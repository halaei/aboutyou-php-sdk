<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Product
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

    /** @var integer[] */
    protected $categoryIds;

    /** @var integer[] */
    protected $attributeIds;

    /** @var Image */
    protected $defaultImage;

    /** @var Variant */
    protected $defaultVariant;

    /** @var Variant[] */
    protected $variants;

    /** @var Product[] */
    protected $styles;

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jobj)
    {
        // these are required fields
        if (!isset($jobj->id) || !isset($jobj->name)) {
            throw MalformedJsonException();
        }
        $this->id   = $jobj->id;
        $this->name = $jobj->name;

        $this->isSale            = isset($jobj->sale)              ? $jobj->sale : false;
        $this->descritptionShort = isset($jobj->description_short) ? $jobj->description_short : '';
        $this->descriptionLong   = isset($jobj->description_long)  ? $jobj->description_long : '';
        $this->isActive          = isset($jobj->active)            ? $jobj->active : true;


        $this->brandId           = isset($jobj->brandId)           ? $jobj->brandId : null;

        $this->defaultImage      = !empty($jobj->default_image)    ? new Image($jobj->default_image) : null;

        $this->categoryIds       = self::parseCategoryIds($jobj);

        $this->defaultVariant    = isset($jobj->default_variant) ? new Variant($jobj->default_variant) : null;

        $this->variants = [];
        if (!empty($jobj->variants)) {
            foreach ($jobj->variants as $variant) {
                $this->variants[] = new Variant($variant);
            }
        }

        $this->styles = [];
        if (!empty($jobj->styles)) {
            foreach ($jobj->styles as $style) {
                $this->styles[] = new Product($style);
            }
        }
    }

    protected static function parseCategoryIds($jobj)
    {
        $cIds = [];
        foreach (get_object_vars($jobj) as $name => $aa) {
            if (strpos($name, 'categories') !== 0) continue;
            foreach ($aa as $ids) {
                $cIds = array_merge($cIds, $ids);
            }
        }

        return array_unique($cIds);
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
        return $this->descritptionShort;
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

    public function getAttributes()
    {
        // TODO: Implement me
    }

    /**
     * @return integer[]
     */
    public function getAttributeIds()
    {
        return $this->attributeIds;
    }

    /**
     * @return integer[]
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    public function fetchCategories()
    {

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
     * @return ProductVariant
     */
    public function getVariantById($variantId)
    {
        //TODO: get product variant by json data
        return new ProductVariant();
    }
}
