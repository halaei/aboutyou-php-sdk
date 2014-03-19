<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Exception\MalformedJsonException;

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

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jsonObject)
    {
        // these are required fields
        if (!isset($jsonObject->id) || !isset($jsonObject->name)) {
            throw new MalformedJsonException();
        }
        $this->id   = $jsonObject->id;
        $this->name = $jsonObject->name;

        $this->isSale            = isset($jsonObject->sale) ? $jsonObject->sale : false;
        $this->descriptionShort  = isset($jsonObject->description_short) ? $jsonObject->description_short : '';
        $this->descriptionLong   = isset($jsonObject->description_long) ? $jsonObject->description_long : '';
        $this->isActive          = isset($jsonObject->active) ? $jsonObject->active : true;
        $this->brandId           = isset($jsonObject->brand_id) ? $jsonObject->brand_id : null;

        $this->minPrice         = isset($jsonObject->min_price) ? $jsonObject->min_price : null;
        $this->maxPrice         = isset($jsonObject->max_price) ? $jsonObject->max_price : null;

        if (isset($jsonObject->default_image) || isset($jsonObject->default_variant) || isset($jsonObject->variants) || !empty($jsonObject->styles)) {
            $factory = $this->getModelFactory();

            $this->defaultImage     = isset($jsonObject->default_image) ? $factory->createImage($jsonObject->default_image) : null;
            $this->defaultVariant   = isset($jsonObject->default_variant) ? $factory->createVariant($jsonObject->default_variant) : null;

            $this->variants         = self::parseVariants($jsonObject, $factory);
            $this->styles           = self::parseStyles($jsonObject, $factory);
        }
        $this->categoryIdPaths  = self::parseCategoryIdPaths($jsonObject);

        $this->facetIds     = self::parseFacetIds($jsonObject);
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

    protected static function parseFacetIds($jsonObject)
    {
        $ids = array();
        if (!empty($jsonObject->attributes_merged)) {
            foreach ($jsonObject->attributes_merged as $group => $facetIds) {
                $gid = substr($group, 11); // rm prefix "attributes"

                // TODO: Remove Workaround for Ticket ???
                settype($facetIds, 'array');
                $ids[$gid] = $facetIds;
            }
        }

        return $ids;
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

        if(count($categories)) {
            return array_values($categories)[0];

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
    public function getLeafCategories($activeOnly = true) {
        $categories = $this->getCategories($activeOnly);
        
        $leafCategories = [];

        $c = 0;
        while(count($categories) && $c<100) {
            $c++;
            $category = array_shift($categories);
            
            if($category->isActive() || ! $activeOnly) {
                $subCategories = $category->getSubCategories();

                if(!count($subCategories) && !isset($leafCategories[$category->getId()])) {
                    $leafCategories[$category->getId()] = $category;
                }
                else {
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
        if (!$this->categories) {
            // put all category ids in an array to fetch by ids
            $flattened = array();
            foreach($this->categoryIdPaths as $path) {
                foreach($path as $categoryId) {
                    $flattened[] = $categoryId;
                }
            }

            // fetch all necessary categories from API
            $flattenCategories = $this->getShopApi()->fetchCategoriesByIds($flattened)->getCategories();
            $flattenActiveCategories = [];
            
            foreach($flattenCategories as $category) {
                if($category->isActive()) {
                    $flattenActiveCategories[] = clone $category;
                }
            }

            $this->categories = Category::buildTree($flattenCategories);
            $this->activeCategories = Category::buildTree($flattenActiveCategories);
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
     * Returns all FacetGroups from all Variants
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
                    $allGroups[] = $group;
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
    public function getSelectableFacetGroups(FacetGroupSet $selectedFacetGroupSet)
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
        $key = Facet::uniqueKey(ShopApi\Constants::FACET_BRAND, $this->brandId);

        return $this->getFacetGroupSet()->getFacetByKey($key);
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
