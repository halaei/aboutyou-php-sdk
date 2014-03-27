<?php
namespace Collins\ShopApi\Model\Basket;

use Collins\ShopApi\Model\Variant;
use Collins\ShopApi\Model\Product;

/**
 *
 */
class BasketVariantItem extends AbstractBasketItem
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * @var Product
     */
    private $product = null;

    /**
     * @var Variant
     */
    private $variant = null;

    /**
     * Constructor.
     *
     * @param object $jsonObject The basket data.
     * @param Product[] $products
     */
    public function __construct($jsonObject, array $products)
    {
        if (isset($jsonObject->additional_data)) {
            $this->additionalData = $jsonObject->additional_data;
        }

        $this->parseErrorResult($jsonObject);

        $this->jsonObject = $jsonObject;

        if ($products[$jsonObject->product_id]) {
            $this->setProduct($products[$jsonObject->product_id]);
        }
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->errorCode > 0;
    }

    /**
     * Get the total price.
     *
     * @return integer
     */
    public function getPrice()
    {
        return $this->jsonObject->total_price;
    }

    /**
     * Get the tax.
     *
     * @return integer
     */
    public function getTax()
    {
        return $this->jsonObject->tax;
    }

    /**
     * Get the tax.
     *
     * @return integer
     */
    public function getVat()
    {
        return $this->jsonObject->total_vat;
    }

    /**
     * @return integer
     */
    public function getNet()
    {
        return $this->jsonObject->total_net;
    }


    /**
     * Get the variant old price in euro cents.
     *
     * @return integer
     */
    public function getOldPrice()
    {
        return $this->getVariant()->getOldPrice();
    }

    /**
     * Get the product.
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
    }

    /**
     * Get the product variant.
     *
     * @return Variant
     */
    public function getVariant()
    {
        if (!$this->variant) {
            $this->variant = $this->getProduct() ?
                $this->getProduct()->getVariantById($this->jsonObject->variant_id) :
                null
            ;
        }

        return $this->variant;
    }
}