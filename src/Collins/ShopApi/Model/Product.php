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
    protected $categoryIds;

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

        $factory = $this->getModelFactory();

        $this->isSale            = isset($jsonObject->sale) ? $jsonObject->sale : false;
        $this->descriptionShort  = isset($jsonObject->description_short) ? $jsonObject->description_short : '';
        $this->descriptionLong   = isset($jsonObject->description_long) ? $jsonObject->description_long : '';
        $this->isActive          = isset($jsonObject->active) ? $jsonObject->active : true;
        $this->brandId           = isset($jsonObject->brand_id) ? $jsonObject->brand_id : null;

        $this->minPrice         = isset($jsonObject->min_price) ? $jsonObject->min_price : null;
        $this->maxPrice         = isset($jsonObject->max_price) ? $jsonObject->max_price : null;

        $this->defaultImage   = isset($jsonObject->default_image) ? $factory->createImage($jsonObject->default_image) : null;
        $this->defaultVariant = isset($jsonObject->default_variant) ? $factory->createVariant($jsonObject->default_variant) : null;

        $this->variants     = self::parseVariants($jsonObject, $factory);
        $this->styles       = self::parseStyles($jsonObject, $factory);
        $this->categoryIds  = self::parseCategoryIds($jsonObject);
        $this->facetIds     = self::parseFacetIds($jsonObject);
    }

    protected static function parseVariants($jsonObject, ShopApi\Factory\ModelFactoryInterface $factory)
    {
        $variants = [];
        if (!empty($jsonObject->variants)) {
            foreach ($jsonObject->variants as $variant) {
                $variants[$variant->id] = $factory->createVariant($variant);
            }
        }

        return $variants;
    }

    protected static function parseStyles($jsonObject, ShopApi\Factory\ModelFactoryInterface $factory)
    {
        $styles = [];
        if (!empty($jsonObject->styles)) {
            foreach ($jsonObject->styles as $style) {
                $styles[] = $factory->createProduct($style);
            }
        }

        return $styles;
    }

    protected static function parseCategoryIds($jsonObject)
    {
        $cIds = [];
        foreach (get_object_vars($jsonObject) as $name => $subIds) {
            if (strpos($name, 'categories') !== 0) {
                continue;
            }
            // flatten array
            $cIds = call_user_func_array('array_merge', $subIds);
        }

        return $cIds;
    }

    protected static function parseFacetIds($jsonObject)
    {
        $ids = [];
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
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * Returns the first active category and, if non active, then it return the first category
     *
     * @return Category|null
     */
    public function getMainCategory()
    {
        $category = $this->getFirstActiveCategory();
        if ($category === null) {
            $category = $this->getFirstCategory();
        }

        return $category;
    }

    /**
     * @return Category|null
     */
    public function getFirstActiveCategory()
    {
        $categories = $this->getCategories();
        foreach ($categories as $category) {
            if ($category->isActive()) {
                return $category;
            }
        }

        return null;
    }

    /**
     * @return Category|null
     */
    public function getFirstCategory()
    {
        $categories = $this->getCategories();

        return count($categories) ? reset($categories) : null;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        if ($this->categories) {
            return $this->categories;
        }

        $ids = $this->getCategoryIds();
        if (empty($ids)) {
            $this->categories = [];
        } else {
            $api = $this->getShopApi();
            $this->categories = $api->fetchCategoriesByIds($ids)->getCategories();
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
        return [];
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
        $allGroups = [];
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
     *
     * TODO: implement me
     *
     * @param integer $groupId
     *
     * @param FacetGroupSet $facetGroupSet
     */
    public function getSelectableFacetGroups($groupId, FacetGroupSet $facetGroupSet)
    {
        $this->getFacetGroups($groupId);
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
}
