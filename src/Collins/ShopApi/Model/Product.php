<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Product
{
    const SALE_INCLUDE = true;
    const SALE_EXCLUDE = false;
    const SALE_IGNORE  = null;
    const SALE_UNKNOWN = -1;

    const UNKNOWN = null;

    /** @var int */
    protected $id;

    /** @var string */
    protected $name;

    /** @var mixed */
    protected $sale;

    /** @var bool */
    protected $isActive;

    /** @var string */
    protected $descritptionShort;

    /** @var string */
    protected $descriptionLong;

    /** @var int */
    protected $brandId;

    /** @var int[] */
    protected $categoryIds;

    /** @var int[] */
    protected $attributeIds;

    /** @var Image */
    protected $defaultImage;

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

        $this->sale              = isset($jobj->sale)              ? $jobj->sale : self::SALE_UNKNOWN;
        $this->descritptionShort = isset($jobj->description_short) ? $jobj->description_short : '';
        $this->descriptionLong   = isset($jobj->description_long)  ? $jobj->description_long : '';
        $this->isActive          = isset($jobj->active)            ? $jobj->active : true;

        $this->brandId           = isset($jobj->brandId)           ? $jobj->brandId : self::UNKNOWN;

        $this->defaultImage      = !empty($jobj->default_image)    ? new Image($jobj->default_image) : self::UNKNOWN;
    }

    /**
     * @return string
     */
    public function getDescriptionLong()
    {
        return $this->descriptionLong;
    }

    /**
     * @return string
     */
    public function getDescritptionShort()
    {
        return $this->descritptionShort;
    }

    /**
     * @return int
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
     * @return mixed
     */
    public function getSale()
    {
        return $this->sale;
    }

    /** bool */
    public function isSaleIncluded()
    {
        return $this->sale === self::SALE_INCLUDE;
    }

    /** bool */
    public function isSaleExcluded()
    {
        return $this->sale === self::SALE_EXCLUDE;
    }

    /** bool */
    public function isSaleIgnored()
    {
        return $this->sale === self::SALE_IGNORE;
    }

    public function getAttributes()
    {

    }

    public function getCategoryIds()
    {

    }

    public function fetchCategories()
    {

    }

    public function getDefaultImage()
    {

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