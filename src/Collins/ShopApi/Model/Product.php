<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Exception\MalformedJsonException;
use Collins\ShopApi\Factory\ModelFactoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class Product extends AbstractModel
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $name;

    /** @var mixed */
    protected $isSale;

    /** @var boolean */
    protected $isActive;

    /** @var string */
    protected $descriptionShort;

    /** @var string */
    protected $descriptionLong;

    /** @var integer */
    protected $minPrice;

    /** @var integer */
    protected $maxPrice;

    /** @var integer */
    protected $brandId;


    /** @var array */
    protected $categoryIdPaths;

    /** @var integer[] */
    protected $facetIds;

    /** @var Image */
    protected $defaultImage;

    /** @var Variant */
    protected $defaultVariant;

    /** @var Variant */
    protected $selectedVariant;

    /** @var Variant[] */
    protected $variants;

    /** @var Product[] */
    protected $styles;

    /** @var FacetGroupSet */
    protected $facetGroups;

    /** @var Category[] */
    protected $categories;
    
    /** @var Category[] */
    protected $activeCategories;

    protected function __construct()
    {
    }

    /**
     * @param $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return static
     *
     * @throws \Collins\ShopApi\Exception\MalformedJsonException
     */
    public static function createFromJson($jsonObject, ModelFactoryInterface $factory)
    {
        $product = new static($jsonObject, $factory);

        // these are required fields
        if (!isset($jsonObject->id) || !isset($jsonObject->name)) {
            throw new MalformedJsonException();
        }

        $product->id   = $jsonObject->id;
        $product->name = $jsonObject->name;

        $product->isSale            = isset($jsonObject->sale) ? $jsonObject->sale : false;
        $product->descriptionShort  = isset($jsonObject->description_short) ? $jsonObject->description_short : '';
        $product->descriptionLong   = isset($jsonObject->description_long) ? $jsonObject->description_long : '';
        $product->isActive          = isset($jsonObject->active) ? $jsonObject->active : true;
        $product->brandId           = isset($jsonObject->brand_id) ? $jsonObject->brand_id : null;

        $product->minPrice         = isset($jsonObject->min_price) ? $jsonObject->min_price : null;
        $product->maxPrice         = isset($jsonObject->max_price) ? $jsonObject->max_price : null;

        $product->defaultImage     = isset($jsonObject->default_image) ? $factory->createImage($jsonObject->default_image) : null;
        $product->defaultVariant   = isset($jsonObject->default_variant) ? $factory->createVariant($jsonObject->default_variant) : null;
        $product->variants         = self::parseVariants($jsonObject, $factory);
        $product->styles           = self::parseStyles($jsonObject, $factory);

        $product->categoryIdPaths  = self::parseCategoryIdPaths($jsonObject);

        $product->facetIds     = self::parseFacetIds($jsonObject);

        return $product;
    }

    protected static function parseVariants($jsonObject, ShopApi\Factory\ModelFactoryInterface $factory)
    {
        $variants = array();
        if (!empty($jsonObject->variants)) {
            foreach ($jsonObject->variants as $variant) {
                $variants[$variant->id] = $factory->createVariant($variant);
            }
        }

        return $variants;
    }

    protected static function parseStyles($jsonObject, ShopApi\Factory\ModelFactoryInterface $factory)
    {
        $styles = array();
        if (!empty($jsonObject->styles)) {
            foreach ($jsonObject->styles as $style) {
                $styles[] = $factory->createProduct($style);
            }
        }

        return $styles;
    }

    protected static function parseCategoryIdPaths($jsonObject)
    {
        $paths = array();

        foreach (get_object_vars($jsonObject) as $name => $categoryPaths) {
            if (strpos($name, 'categories') === 0) {
                $paths = $categoryPaths;
                break;
            }
        }

        return $paths;
    }

    public static function parseFacetIds($jsonObject)
    {
        $ids = self::parseFacetIdsInAttributesMerged($jsonObject);
        if ($ids === null) {
            $ids = self::parseFacetIdsInVariants($jsonObject);
        }
        if ($ids === null) {
            $ids = self::parseFacetIdsInBrand($jsonObject);
        }

        return ($ids !== null) ? $ids : array();
    }

    public static function parseFacetIdsInAttributesMerged($jsonObject)
    {
        if (empty($jsonObject->attributes_merged)) {
            return null;
        }

        return self::parseAttributesJson($jsonObject->attributes_merged);
    }

    public static function parseAttributesJson($AttributesJsonObject)
    {
        $ids = array();

        foreach ($AttributesJsonObject as $group => $facetIds) {
            $gid = substr($group, 11); // rm prefix "attributes"

            // TODO: Remove Workaround for Ticket ???
            settype($facetIds, 'array');
            $ids[$gid] = $facetIds;
        }

        return $ids;
    }

    public static function parseFacetIdsInVariants($jsonObject)
    {
        if (isset($jsonObject->variants)) {
            $ids = array();
            foreach ($jsonObject->variants as $variant) {
                $ids[] = self::parseAttributesJson($variant->attributes);
            }
            $ids = FacetGroupSet::mergeFacetIds($ids);

            return $ids;
        } else if (isset($jsonObject->default_variant)) {
            $ids = self::parseAttributesJson($jsonObject->default_variant->attributes);

            return $ids;
        }

        return null;
    }

    public static function parseFacetIdsInBrand($jsonObject)
    {
        if (!isset($jsonObject->brand_id)) {
            return null;
        }

        return array('0' => array($jsonObject->brand_id));
    }

    /**
     * @return string|null
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * @return string|null
     */
    public function getDescriptionShort()
    {
        return $this->descriptionShort;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function isSale()
    {
        return $this->isSale;
    }

    /**
     * return integer|null in euro cent
     */
    public function getMinPrice()
    {
        return $this->minPrice;
    }

    protected function generateFacetGroupSet()
    {
        if (empty($this->facetIds)) {
            throw new ShopApi\Exception\RuntimeException('To use this method, you must add the field ProductFields::ATTRIBUTES_MERGED to the "product search" or "products by ids"');
        }

        $this->facetGroups = new FacetGroupSet($this->facetIds);
    }

    /**
     * @return FacetGroupSet|null
     */
    public function getFacetGroupSet()
    {
        if (!$this->facetGroups) {
            $this->generateFacetGroupSet();
        }

        return $this->facetGroups;
    }

    public function getCategoryIdHierachies()
    {
        return $this->categoryIdPaths;
    }

    /**
     * This is a low level method.
     *
     * Returns an array of arrays:
     * [
     *  "<facet group id>" => [<facet id>, <facet id>, ...],
     *  "<facet group id>" => [<facet id>, ...],
     *  ...
     * ]
     * "<facet group id>" are strings with digits
     * <facet id> are integers
     *
     * for example:
     * [
     *  "0"   => [264],
     *  "1"   => [1234],
     *  "206" => [123,234,345]
     * ]
     *
     * @return array
     */
    public function getFacetIds()
    {
        return $this->facetIds;
    }

    /**
     * Returns the first active category and, if non active, then it return the first category
     
     * @param bool $activeOnly return only categories that are active
     * @return Category|null
     */
    public function getCategory($active = true)
    {
        $categories = $this->getLeafCategories($active);

        if (count($categories)) {
            return reset($categories);
        }

        return null;
    }

    /**
     * Returns array of categories without subcategories. E.g. of the product is in the category
     * Damen > Schuhe > Absatzschuhe and Damen > Schuhe > Stiefelleten then
     * [Absatzschuhe, Stiefelleten] will be returned
     *
     * @param bool $activeOnly return only categories that are active
     * @return Category[]
     */
    public function getLeafCategories($activeOnly = true)
    {
        // TODO: refactor me
        $categories = $this->getCategories($activeOnly);
        
        $leafCategories = array();

        while (count($categories)) {
            $category = array_shift($categories);
            
            if($category->isActive() || ! $activeOnly) {
                $subCategories = $category->getSubCategories();

                if (!count($subCategories) && !isset($leafCategories[$category->getId()])) {
                    $leafCategories[$category->getId()] = $category;
                } else {
                    $categories = array_merge($categories, $subCategories);
                }
            }
        }

        return array_values($leafCategories);
    }

    /**
     * @param bool $activeOnly  return only active categories
     * @return Category[]
     */
    public function getCategories($activeOnly = true)
    {
        // TODO: refactor me
        if ($this->categories === null) {
            if (empty($this->categoryIdPaths)) {     
                
                $this->categories = array();
                $this->activeCategories = array();
                
            } else {                  
                // put all category ids in an array to fetch by ids
                $flattened = array();
                foreach($this->categoryIdPaths as $path) {
                    foreach($path as $categoryId) {
                        $flattened[] = $categoryId;
                    }
                }

                // fetch all necessary categories from API
                $flattenCategories = $this->getShopApi()->fetchCategoriesByIds($flattened)->getCategories();
                $flattenActiveCategories = array();

                foreach($flattenCategories as $category) {
                    if($category->isActive()) {
                        $flattenActiveCategories[] = clone $category;
                    }
                }

                $this->categories = Category::buildTree($flattenCategories);
                $this->activeCategories = Category::buildTree($flattenActiveCategories);                
            }
        }
        
        if($activeOnly) {
            return $this->activeCategories;
        }

        return $this->categories;
    }

    /**
     * Get facets of given group id.
     *
     * @param integer $groupId The group id.
     *
     * @return \Collins\ShopApi\Model\Facet[]
     */
    public function getGroupFacets($groupId)
    {
        $group = $this->getFacetGroupSet()->getGroup($groupId);
        if ($group) {
            return $group->getFacets();
        }
        return array();
    }

    /**
     * Returns all unique FacetGroups from all Variants
     *
     * @param integer $groupId
     *
     * @return FacetGroup[]
     */
    public function getFacetGroups($groupId)
    {
        $allGroups = array();
        foreach ($this->getVariants() as $variant) {
            $groups = $variant->getFacetGroupSet()->getGroups();
            foreach ($groups as $group) {
                if ($group->getId() === $groupId) {
                    $allGroups[$group->getUniqueKey()] = $group;
                }
            }
        }

        return $allGroups;
    }

    /**
     * Returns all FacetGroups, which matches the current facet group set
     * for example:
     * [['color'] => 'rot'] =>
     *
     * @param FacetGroupSet $selectedFacetGroupSet
     *
     * @return FacetGroup[][]
     *
     * @throws \Collins\ShopApi\Exception\RuntimeException
     */
    public function getSelectableFacetGroups(FacetGroupSet $selectedFacetGroupSet)
    {
        /** @var FacetGroup[] $allGroups */
        $allGroups = array();
        $selectedGroupIds = $selectedFacetGroupSet->getGroupIds();

        foreach ($this->getVariants() as $variant) {
            $facetGroupSet = $variant->getFacetGroupSet();
            $ids = $facetGroupSet->getGroupIds();

            if ($facetGroupSet->contains($selectedFacetGroupSet)) {
                foreach ($ids as $groupId) {
                    if (in_array($groupId, $selectedGroupIds)) continue;

                    $group = $facetGroupSet->getGroup($groupId);
                    if ($group === null) {
                        throw new ShopApi\Exception\RuntimeException('group for id ' . $groupId . ' not found');
                    }
                    $allGroups[$groupId][$group->getUniqueKey()] = clone $group;
                }
            }
        }

        foreach ($selectedGroupIds as $groupId) {
            $ids = $selectedFacetGroupSet->getIds();
            unset($ids[$groupId]);
            $myFacetGroupSet = new FacetGroupSet($ids);
            foreach ($this->getVariants() as $variant) {
                $facetGroupSet = $variant->getFacetGroupSet();
                if ($facetGroupSet->contains($myFacetGroupSet)) {
                    $group = $facetGroupSet->getGroup($groupId);
                    $allGroups[$groupId][$group->getUniqueKey()] = clone $group;
                }
            }
        }

        return $allGroups;
    }

    /**
     * Returns all FacetGroups, which matches the current facet group set
     * for example:
     * [['color'] => 'rot'] =>
     *
     * @param FacetGroupSet $selectedFacetGroupSet
     *
     * @return FacetGroup[]
     */
    public function getExcludedFacetGroups(FacetGroupSet $selectedFacetGroupSet)
    {
        /** @var FacetGroup[] $allGroups */
        $allGroups = array();
        $selectedGroupIds = $selectedFacetGroupSet->getGroupIds();

        foreach ($this->getVariants() as $variant) {
            $facetGroupSet = $variant->getFacetGroupSet();
            if (!$facetGroupSet->contains($selectedFacetGroupSet)) {
                continue;
            }

            $ids = $facetGroupSet->getGroupIds();

            foreach ($ids as $groupId) {
                if (in_array($groupId, $selectedGroupIds)) continue;

                $group  = $facetGroupSet->getGroup($groupId);
                if ($group === null) {
                    throw new ShopApi\Exception\RuntimeException('group for id ' . $groupId . ' not found');
                }
                $facets = $group->getFacets();
                if (empty($facets)) continue;

                if (!isset($allGroups[$groupId])) {
                    $allGroups[$groupId] = new FacetGroup($group->getId(), $group->getName());
                }
                $allGroups[$groupId]->addFacets($facets);
            }
        }

        return array_values($allGroups);
    }


    /**
     * @return Image|null
     */
    public function getDefaultImage()
    {
        return $this->defaultImage;
    }

    /**
     * @return \Collins\ShopApi\Model\Facet
     */
    public function getBrand()
    {
        return $this->getFacetGroupSet()->getFacet(ShopApi\Constants::FACET_BRAND, $this->brandId);
    }

    /**
     * @return Variant|null
     */
    public function getDefaultVariant()
    {
        return $this->defaultVariant;
    }

    /**
     * @return Variant[]
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @return Product[]
     */
    public function getStyles()
    {
        return $this->styles;
    }

    /**
     * @return integer|null
     */
    public function getMaxPrice()
    {
        return $this->maxPrice;
    }

    /**
     * Get variant by id.
     *
     * @param integer $variantId The variant id.
     *
     * @return Variant
     */
    public function getVariantById($variantId)
    {
        if (isset($this->variants[$variantId])) {
            return $this->variants[$variantId];
        }

        return null;
    }

    /**
     * @param string $ean
     *
     * @return Variant[]
     */
    public function getVariantsByEan($ean)
    {
        $variants = array();
        foreach ($this->variants as $variant) {
            if ($variant->getEan() === $ean) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    /**
     * This returns the first variant, which matches exactly the given facet group set
     *
     * @param FacetGroupSet $facetGroupSet
     *
     * @return Variant|null
     */
    public function getVariantByFacets(FacetGroupSet $facetGroupSet)
    {
        $key = $facetGroupSet->getUniqueKey();
        foreach ($this->variants as $variant) {
            if ($variant->getFacetGroupSet()->getUniqueKey() === $key) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * This returns the all variants, which matches some of the given facet
     *
     * @param $facetId
     * @param $groupId
     *
     * @return Variant[]
     */
    public function getVariantsByFacetId($facetId, $groupId)
    {
        $variants = array();
        $facet = new Facet($facetId, '', '', $groupId, '');
        foreach ($this->variants as $variant) {
            if ($variant->getFacetGroupSet()->contains($facet)) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }
}
