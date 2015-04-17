<?php
/**
 * @author nils.droege@aboutyou.de
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model;

use AboutYou\SDK\Constants;
use AboutYou\SDK\Exception\MalformedJsonException;
use AboutYou\SDK\Exception\RuntimeException;
use AboutYou\SDK\Factory\ModelFactoryInterface;
use DateTime;

class Product
{
    /**
     * @var \stdClass
     */
    public $rawJson;

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
    protected $maxSavingsPrice;

    /** @var integer */
    protected $maxSavingsPercentage;

    /** @var integer */
    protected $brandId;

    /** @var integer */
    protected $merchantId;

    /** @var DateTime */
    protected $firstPublicationDate;

    /** @var array */
    protected $categoryIdPaths;

    /** @var  Category[] */
    protected $rootCategories;

    /** @var  Category[] */
    protected $activeRootCategories;

    /** @var  Category[] */
    protected $leafCategories;

    /** @var  Category[] */
    protected $activeLeafCategories;

    /** @var integer[] */
    protected $facetIds;

    /** @var string */
    protected $defaultImageHash;

    /** @var Image */
    protected $selectedImage;

    /** @var Image[] */
    protected $images = [];

    /** @var int */
    protected $defaultVariantId;

    /** @var Variant */
    protected $selectedVariant;

    /** @var Variant[] */
    protected $variants = [];

    /** @var Variant[] */
    protected $inactiveVariants;

    /**
     * @var string
     */
    protected $styleKey;

    /** @var Product[] */
    protected $styles;

    /** @var FacetGroupSet */
    protected $facetGroups;

    /** @var string[] */
    protected $bulletPoints;

    /** @var Facet[][] */
    protected $productAttributes;

    /** @var  ModelFactoryInterface */
    private $factory;

    protected function __construct()
    {
    }

    /**
     * @param $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return static
     *
     * @throws \AboutYou\SDK\Exception\MalformedJsonException
     */
    public static function createFromJson($jsonObject, ModelFactoryInterface $factory, $appId)
    {
        $product = new static($jsonObject, $factory);

        // these are required fields
        if (!isset($jsonObject->id) || !isset($jsonObject->name)) {
            throw new MalformedJsonException();
        }

        $product->rawJson = $jsonObject;

        $product->factory = $factory;

        $product->id   = $jsonObject->id;
        $product->name = $jsonObject->name;

        $product->isSale           = isset($jsonObject->sale) ? $jsonObject->sale : false;
        $product->descriptionShort = isset($jsonObject->description_short) ? $jsonObject->description_short : '';
        $product->descriptionLong  = isset($jsonObject->description_long) ? $jsonObject->description_long : '';
        $product->isActive         = isset($jsonObject->active) ? $jsonObject->active : true;
        $product->brandId          = isset($jsonObject->brand_id) ? $jsonObject->brand_id : null;
        $product->merchantId       = isset($jsonObject->merchant_id) ? $jsonObject->merchant_id : null;
        $product->bulletPoints     = isset($jsonObject->bullet_points) ? $jsonObject->bullet_points : null;

        $product->minPrice         = isset($jsonObject->min_price) ? $jsonObject->min_price : null;
        $product->maxPrice         = isset($jsonObject->max_price) ? $jsonObject->max_price : null;
        $product->maxSavingsPrice  = isset($jsonObject->max_savings) ? $jsonObject->max_savings : null;
        $product->maxSavingsPercentage = isset($jsonObject->max_savings_percentage) ? $jsonObject->max_savings_percentage : null;

        $product->firstPublicationDate = isset($jsonObject->new_in_since_date) && is_string($jsonObject->new_in_since_date)
            ? new \DateTime($jsonObject->new_in_since_date)
            : null;

        $product->images = self::parseImages($jsonObject, $factory);
        if (isset($jsonObject->default_image)) {
            unset($product->images[$jsonObject->default_image->hash]);

            $defaultImage = $factory->createImage($jsonObject->default_image);
            $product->defaultImageHash = $jsonObject->default_image->hash;
            $product->images = array_merge([$jsonObject->default_image->hash => $defaultImage], $product->images);
        }

        $product->variants = self::parseVariants($jsonObject, $factory, $product);
        if (isset($jsonObject->default_variant)) {
            unset($product->variants[$jsonObject->default_variant->id]);

            $defaultVariant = $factory->createVariant($jsonObject->default_variant, $product);
            $product->defaultVariantId = $jsonObject->default_variant->id;
            $product->variants = array_merge([$jsonObject->default_variant->id => $defaultVariant], $product->variants);
        }

        $product->inactiveVariants = self::parseVariants($jsonObject, $factory, $product, 'inactive_variants');

        $product->styleKey = isset($jsonObject->style_key) ? $jsonObject->style_key : null;
        $product->styles           = self::parseStyles($jsonObject, $factory);

        $key = 'categories.' . $appId;
        $product->categoryIdPaths  = isset($jsonObject->$key) ? $jsonObject->$key : [];

        $product->facetIds     = self::parseFacetIds($jsonObject);
        if (isset($jsonObject->product_attributes)) {
            $product->productAttributes = [];
            foreach ($jsonObject->product_attributes as $groupId => $jsonAttributes) {
                $attributes = [];
                $items = isset($jsonAttributes->items) ? $jsonAttributes->items : $jsonAttributes;
                foreach ($items as $jsonAttribute) {
                    $attribute = $factory->createInlineFacet($jsonAttribute);
                    $attributes[$attribute->getId()] = $attribute;
                }
                $product->productAttributes[$groupId] = $attributes;
            }
        }

        return $product;
    }

    /**
     * @param \stdClass             $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return Image[]
     */
    protected static function parseImages($jsonObject, ModelFactoryInterface $factory)
    {
        $images = [];
        if (isset($jsonObject->images)) {
            foreach ($jsonObject->images as $image) {
                $images[$image->hash] = $factory->createImage($image);
            }
        }

        return array_reverse($images, true); // TODO: KIM remove reverse after SAPI fixes it
    }

    protected static function parseVariants($jsonObject, ModelFactoryInterface $factory, Product $product, $attributeName = 'variants')
    {
        $variants = [];
        if (!empty($jsonObject->$attributeName)) {
            foreach ($jsonObject->$attributeName as $jsonVariant) {
                if (isset($jsonVariant->id)) {
                    $variants[$jsonVariant->id] = $factory->createVariant($jsonVariant, $product);
                }
            }
        }

        return $variants;
    }

    protected static function parseStyles($jsonObject, ModelFactoryInterface $factory)
    {
        $styles = [];
        if (!empty($jsonObject->styles)) {
            foreach ($jsonObject->styles as $style) {
                $styles[] = $factory->createProduct($style);
            }
        }

        return $styles;
    }

    protected static function parseCategoryIdPaths($jsonObject)
    {
        $paths = [];

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

        return ($ids !== null) ? $ids : [];
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
        $ids = [];

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
            $ids = [];
            foreach ($jsonObject->variants as $variant) {
                if (isset($variant->attributes)) {
                    $ids[] = self::parseAttributesJson($variant->attributes);
                }
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

        return ['0' => [$jsonObject->brand_id]];
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
            throw new RuntimeException('To use this method, you must add the field ProductFields::ATTRIBUTES_MERGED to the "product search" or "products by ids"');
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
     * @return Factes[][]
     */
    public function getAttributes()
    {
        return $this->productAttributes;
    }

    /**
     * Returns the first active category and, if non active, then it return the first category

     * @param bool $activeOnly return only categories that are active
     *
     * @return Category|null
     */
    public function getCategory($active = false)
    {
        if (empty($this->categoryIdPaths)) {
            return;
        }

        $categories = $this->getLeafCategories($active);

        return reset($categories);
    }

    /**
     * @return Category|null
     */
    public function getCategoryWithLongestActivePath()
    {
        if (empty($this->categoryIdPaths)) {
            return;
        }

        // get reverse sorted category pathes
        $pathLengths = array_map('count', $this->categoryIdPaths);
        arsort($pathLengths);

        foreach ($pathLengths as $index => $pathLength) {
            $categoryPath = $this->categoryIdPaths[$index];
            $leafId = end($categoryPath);

            $category = $this->getCategoryManager()->getCategory($leafId, true);

            if (!$category) {
                throw new RuntimeException('Missing category with id ' . $leafId);
            }

            if ($category->isPathActive()) {
                return $category;
            }
        }

        return null;
    }

    /**
     * Returns array of categories without subcategories. E.g. of the product is in the category
     * Damen > Schuhe > Absatzschuhe and Damen > Schuhe > Stiefelleten then
     * [Absatzschuhe, Stiefelleten] will be returned
     *
     * @param bool $activeOnly return only categories that are active
     *
     * @return Category[]
     */
    public function getLeafCategories($activeOnly = Category::ACTIVE_ONLY)
    {
        $categoryIds = $this->getLeafCategoryIds();

        $categories = $this->getCategoryManager()->getCategories($categoryIds, $activeOnly);

        return $categories;
    }


    public function getRootCategoryIds() {
        $ids = array_map(function($categoryIdPath) {
            return $categoryIdPath[0];
        }, $this->categoryIdPaths);

        return array_unique($ids);
    }

    public function getLeafCategoryIds() {
        return array_map(function($categoryIdPath) {
            return end($categoryIdPath);
        }, $this->categoryIdPaths);

        return $ids;
    }

    public function getCategories($activeOnly = Category::ACTIVE_ONLY)
    {
        return $this->getRootCategories($activeOnly);
    }

    /**
     * @param bool $activeOnly  return only active categories
     *
     * @return Category[]
     */
    public function getRootCategories($activeOnly = Category::ACTIVE_ONLY)
    {
        $categoryIds = $this->getRootCategoryIds();

        $categories = $this->getCategoryManager()->getCategories($categoryIds, $activeOnly);

        return $categories;
    }

    /**
     * Get facets of given group id.
     *
     * @param integer $groupId The group id.
     *
     * @return \AboutYou\SDK\Model\Facet[]
     */
    public function getGroupFacets($groupId)
    {
        $group = $this->getFacetGroupSet()->getGroup($groupId);
        if ($group) {
            return $group->getFacets();
        }
        return [];
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
        $allGroups = [];
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
     * @throws \AboutYou\SDK\Exception\RuntimeException
     */
    public function getSelectableFacetGroups(FacetGroupSet $selectedFacetGroupSet)
    {
        /** @var FacetGroup[] $allGroups */
        $allGroups = [];
        $selectedGroupIds = $selectedFacetGroupSet->getGroupIds();

        foreach ($this->getVariants() as $variant) {
            $facetGroupSet = $variant->getFacetGroupSet();
            $ids = $facetGroupSet->getGroupIds();

            if ($facetGroupSet->contains($selectedFacetGroupSet)) {
                foreach ($ids as $groupId) {
                    if (in_array($groupId, $selectedGroupIds)) continue;

                    $group = $facetGroupSet->getGroup($groupId);
                    if ($group === null) {
                        throw new RuntimeException('group for id ' . $groupId . ' not found');
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
        $allGroups = [];
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
                    throw new RuntimeException('group for id ' . $groupId . ' not found');
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
        return $this->getImageByHash($this->defaultImageHash);
    }

    /**
     * @return Image[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        $image = $this->selectedImage ?: $this->getDefaultImage() ?: null;

        if (!$image) {
            list($image) = array_values($this->getImages()) + [null];
        }

        return $image;
    }

    /**
     * @param string $hash
     *
     * @return Image|null
     */
    public function getImageByHash($hash)
    {
        if (isset($this->images[$hash])) {
            return $this->images[$hash];
        }

        return null;
    }

    /**
     * @param string $hash
     */
    public function selectImage($hash)
    {
        if ($hash) {
            $this->selectedImage = $this->getImageByHash($hash);
        } else {
            $this->selectedImage = null;
        }
    }

    /**
     * @return \AboutYou\SDK\Model\Facet
     */
    public function getBrand()
    {
        return $this->getFacetGroupSet()->getFacet(Constants::FACET_BRAND, $this->brandId);
    }

    /**
     * @return string[]|null
     */
    public function getBulletPoints()
    {
        return $this->bulletPoints;
    }

    /**
     * @return Variant|null
     */
    public function getDefaultVariant()
    {
        return $this->getVariantById($this->defaultVariantId);
    }

    /**
     * @return Variant[]
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * @return Variant[]
     */
    public function getInactiveVariants()
    {
        return $this->inactiveVariants;
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
     * @return integer|null
     */
    public function getMaxSavingsPrice()
    {
        return $this->maxSavingsPrice;
    }

    /**
     * @return integer|null
     */
    public function getMaxSavingsPercentage()
    {
        return $this->maxSavingsPercentage;
    }

    public function getMerchantId()
    {
        return $this->merchantId;
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
        $variants = [];
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
        $variants = [];
        $facet = new Facet($facetId, '', '', $groupId, '');
        foreach ($this->variants as $variant) {
            if ($variant->getFacetGroupSet()->contains($facet)) {
                $variants[] = $variant;
            }
        }

        return $variants;
    }

    /**
     * @return \AboutYou\SDK\Model\CategoryManager\CategoryManagerInterface
     */
    private function getCategoryManager()
    {
        return $this->factory->getCategoryManager();
    }

    /**
     * Returns a DateTime object with the value of new_in_since_date from the API.
     * This date specifies when the product was available for the app the first time.
     *
     * @return DateTime|null
     */
    public function getFirstPublicationDate()
    {
        return $this->firstPublicationDate;
    }

    /**
     * @return Facet|null
     */
    public function getSizeAdvice()
    {
        $attributes = $this->getAttributes();

        $sizeAdvice = null;

        if (is_array($attributes) && isset($attributes[368]) && is_array($attributes[368])) {

            $sizeAdvice = array_shift($attributes[368]);
        }

        return $sizeAdvice;
    }

    /**
     * @return string
     */
    public function getStyleKey()
    {
        return $this->styleKey;
    }
}
