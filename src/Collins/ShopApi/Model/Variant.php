<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;


class Variant
{
    protected $jsonObject;

    protected $images = null;

    public function __construct($jsonObject)
    {
        $this->fromJson($jsonObject);
    }

    public function fromJson($jobj)
    {
        $this->jsonObject = $jobj;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->jsonObject->id;
    }

    /**
     * @return array
     */
    public function getImages()
    {
        // parse lazy
        if ($this->images === null) {
            $this->images = [];
            if (!empty($this->jsonObject->images)) {
                foreach ($this->jsonObject->images as $image) {
                    $this->images[] = new Image($image);
                }
            }
            unset($this->jsonObject->images); // free memory
        }

        return $this->images;
    }

    /**
     * Get default image.
     *
     * @return Image
     */
    public function getImage()
    {
        $images = $this->getImages();
        if (isset($images[0])) {
            return $images[0];
        }
        return null;
    }

    /**
     * @return string
     */
    public function getEan()
    {
        return $this->jsonObject->ean;
    }

    /**
     * @return boolean
     */
    public function isDefault()
    {
        return $this->jsonObject->default;
    }

    /**
     * Returns the price in euro cent
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->jsonObject->price;
    }

    /**
     * return integer in euro cent
     */
    public function getOldPrice()
    {
        return $this->jsonObject->old_price;
    }

    /**
     * return integer in euro cent
     */
    public function getRetailPrice()
    {
        return $this->jsonObject->retail_price;
    }

    /**
     * @return interger
     */
    public function getMaxQuantity()
    {
        return $this->jsonObject->quantity;
    }
}