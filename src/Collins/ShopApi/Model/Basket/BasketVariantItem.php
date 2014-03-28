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

    /** @var integer */
    private $variantId;

    /**
     * Constructor.
     *
     * @param integer $variantId
     * @param array $additionalData
     */
    public function __construct($variantId, $additionalData = null)
    {
        $this->variantId = $variantId;
        $this->additionalData = $additionalData;
    }

    /**
     * @param object $jsonObject The basket data.
     * @param Product[] $products
     *
     * @return BasketVariantItem
     */
    public static function createFromJson($jsonObject, array $products)
    {
        $item = new self($jsonObject->variant_id, isset($jsonObject->additional_data) ? $jsonObject->additional_data : null);
        $item->parseErrorResult($jsonObject);

        $item->jsonObject = $jsonObject;

        if ($products[$jsonObject->product_id]) {
            $item->setProduct($products[$jsonObject->product_id]);
        }
        unset($jsonObject->variant_id, $jsonObject->additional_data, $jsonObject->product_id);

        return $item;
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
                $this->getProduct()->getVariantById($this->variantId) :
                null
            ;
        }

        return $this->variant;
    }

    /**
     * @return integer
     */
    public function getVariantId()
    {
        return $this->variantId;
    }
}