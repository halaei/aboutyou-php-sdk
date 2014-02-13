<?php
namespace Collins\ShopApi\Model;

/**
 *
 */
class BasketItem
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
     * @var ProductVariant
     */
    private $productVariant = null;

    /**
     * Constructor.
     *
     * @param object $jsonObject The basket data.
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }

    /**
     * Create product model from json object.
     *
     * @param object $jsonProduct The product data.
     *
     * @return Product
     */
    protected function createProduct($jsonProduct)
    {
        return new Product($jsonProduct);
    }

    /**
     * Get the total price.
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->jsonObject->total_price;
    }

    /**
     * Get the unit price.
     *
     * @return integer
     */
    public function getUnitPrice()
    {
        return $this->jsonObject->unit_price;
    }

    /**
     * Get the amount of items.
     *
     * @return integer
     */
    public function getAmount()
    {
        return $this->jsonObject->amount;
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
     * Get the product.
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            $this->product = $this->createProduct($this->jsonObject->product);
        }
        return $this->product;
    }

    /**
     * Get the product variant.
     *
     * @return ProductVariant
     */
    public function getProductVariant()
    {
        if (!$this->productVariant) {
            $this->productVariant = $this->getProduct()->getVariantById($this->jsonObject->id);
        }
        return $this->productVariant;
    }
}