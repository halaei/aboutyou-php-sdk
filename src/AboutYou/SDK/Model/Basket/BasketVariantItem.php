<?php
namespace AboutYou\SDK\Model\Basket;

use AboutYou\SDK\Model\Variant;
use AboutYou\SDK\Model\Product;

abstract class BasketVariantItem extends AbstractBasketItem
{
    /**
     * @var object
     */
    protected $jsonObject = null;

    /**
     * @var Product
     */
    protected $product = null;

    /**
     * @var Variant
     */
    protected $variant = null;

    /** @var integer */
    protected $variantId;

    /** @var string */
    protected $deliveryCarrier;

    /** DeliveryEstimation */
    protected $deliveryEstimation;

    /** @var int */
    protected $packageId;

    /** @var int */
    protected $appId = null;

    /**
     * Constructor.
     *
     * @param integer $variantId
     * @param array $additionalData
     * @param string $deliveryCarrier
     * @param DeliveryEstimation $deliveryEstimation
     */
    public function __construct(
        $variantId,
        $additionalData = null,
        $appId = null,
        $deliveryCarrier = null,
        DeliveryEstimation $deliveryEstimation = null,
        $packageId = null
    )
    {
        $this->checkVariantId($variantId);
        $this->checkAdditionData($additionalData);
        $this->variantId = $variantId;
        $this->additionalData = $additionalData;
        $this->deliveryCarrier = $deliveryCarrier;
        $this->deliveryEstimation = $deliveryEstimation;
        $this->packageId = $packageId;

        if (isset($appId)) {
            $this->checkAppId($appId);
            $this->appId = $appId;
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
    public function getTotalPrice()
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
     * Get the AppId
     *
     * @return int
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Get the value added tax.
     *
     * @return integer
     */
    public function getTotalVat()
    {
        return $this->jsonObject->total_vat;
    }

    /**
     * @return integer
     */
    public function getTotalNet()
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
                $this->getProduct()->getVariantById($this->variantId, true) :
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

    /**
     * @return string
     */
    public function getDeliveryCarrier()
    {
        return $this->deliveryCarrier;
    }

    /**
     * @return DeliveryEstimation
     */
    public function getDeliveryEstimation()
    {
        return $this->deliveryEstimation;
    }

    /**
     * @return int
     */
    public function getPackageId()
    {
        return $this->packageId;
    }

    /**
     * @return string
     */
    public function getUniqueKey()
    {
        $key = $this->getVariantId();
        $additionalData = $this->additionalData;
        if (!empty($additionalData)) {
            ksort($additionalData);
            $key .= ':' . json_encode($additionalData);
        }

        return $key;
    }

    protected function checkVariantId($variantId)
    {
        if (!is_long($variantId)) {
            throw new \InvalidArgumentException('the variant id must be an integer');
        }
    }

    protected function checkAppId($appId)
    {
        if (!is_long($appId)) {
            throw new \InvalidArgumentException('the app id must be an integer');
        }
    }
}