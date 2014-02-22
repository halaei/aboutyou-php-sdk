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

    /** @var Category */
    protected $category;

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jobj)
    {
        // these are required fields
        if (!isset($jobj->id) || !isset($jobj->name)) {
            throw new MalformedJsonException();
        }
        $this->id   = $jobj->id;
        $this->name = $jobj->name;

        $this->isSale            = isset($jobj->sale) ? $jobj->sale : false;
        $this->descriptionShort  = isset($jobj->description_short) ? $jobj->description_short : '';
        $this->descriptionLong   = isset($jobj->description_long) ? $jobj->description_long : '';
        $this->isActive          = isset($jobj->active) ? $jobj->active : true;
        $this->brandId           = isset($jobj->brand_id) ? $jobj->brand_id : null;

        $this->minPrice         = isset($jobj->min_price) ? $jobj->min_price : null;
        $this->maxPrice         = isset($jobj->max_price) ? $jobj->max_price : null;

        $this->defaultImage   = isset($jobj->default_image) ? new Image($jobj->default_image) : null;
        $this->defaultVariant = isset($jobj->default_variant) ? new Variant($jobj->default_variant) : null;

        $this->variants     = self::parseVariants($jobj);
        $this->styles       = self::parseStyles($jobj);
        $this->categoryIds  = self::parseCategoryIds($jobj);
        $this->facetIds     = self::parseFacetIds($jobj);
    }

    protected static function parseVariants($jobj)
    {
        $variants = [];
        if (!empty($jobj->variants)) {
            foreach ($jobj->variants as $variant) {
                $variants[$variant->id] = new Variant($variant);
            }
        }

        return $variants;
    }

    protected static function parseStyles($jobj)
    {
        $styles = [];
        if (!empty($jobj->styles)) {
            foreach ($jobj->styles as $style) {
                $styles[] = new Product($style);
            }
        }

        return $styles;
    }

    protected static function parseCategoryIds($jobj)
    {
        $cIds = [];
        foreach (get_object_vars($jobj) as $name => $subIds) {
            if (strpos($name, 'categories') !== 0) {
                continue;
            }
            // flatten array
            $cIds = call_user_func_array('array_merge', $subIds);
        }

        return $cIds;
    }

    protected static function parseFacetIds($jobj)
    {
        $ids = [];
        if (!empty($jobj->attributes_merged)) {
            foreach ($jobj->attributes_merged as $group => $aIds) {
                $gid = substr($group, 11); // rm prefix "attributs_"
                $ids[$gid] = $aIds;
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
     * @return integer[]
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
     * @deprecated
     */
    public function getCategory()
    {
        return $this->getMainCategory();
    }

    /**
     * Get product category.
     *
     * @return Category
     */
    public function getMainCategory()
    {
        $ids = $this->getCategoryIds();
        if (empty($ids)) {
            return null;
        }
        
        if ($this->category) {
            return $this->category;
        }

        $api = $this->getShopApi();
        $categories = $api->fetchCategoriesByIds($ids)->getCategories();
        foreach ($categories as $category) {
            if ($category->isActive()) {
                $this->category = $category;
                break;
            }
        }

        return $this->category;
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
     * @return integer|null
     */
    public function getBrandId()
    {
        return $this->brandId;
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
     * Select a variant.
     *
     * @param integer $variantId The variant id.
     *
     * @return void
     */
    public function selectVariant($variantId)
    {
        $this->selectedVariant = $this->getVariantById($variantId);
    }

    /**
     * Get the selected or default variant.
     *
     * @return Variant
     */
    public function getSelectedVariant()
    {
        if( $this->selectedVariant ) {
            return $this->selectedVariant;
        }

        return $this->defaultVariant;
    }

    /**
     * This returns the first variant, which matches the given facet group set
     *
     * @param FacetGroupSet $facets
     *
     * @return Variant|null
     */
    public function getVariantByFacets(FacetGroupSet $facets)
    {
        $key = $facets->getUniqueKey();
        foreach ($this->variants as $variant) {
            if ($variant->getFacetGroupSet()->getUniqueKey() === $key) {
                return $variant;
            }
        }

        return null;
    }
}
