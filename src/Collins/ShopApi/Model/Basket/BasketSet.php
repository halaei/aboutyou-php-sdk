<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;

use Collins\ShopApi\Factory\ModelFactoryInterface;

class BasketSet extends AbstractBasketItem implements BasketItemInterface
{
    /** @var string */
    protected $id;

    /** @var BasketVariantItem[] */
    protected $items;

    /** @var ResultError[] */
    protected $errors;

    /** @var interger */
    protected $totalPrice;

    /** @var interger */
    protected $totalNet;

    /** @var interger */
    protected $totalVat;

    public function __construct(\stdClass $jsonObject, ModelFactoryInterface $factory, $products)
    {
        $this->id = $jsonObject->id;
        if (isset($jsonObject->additional_data)) {
            $this->additionalData = $jsonObject->additional_data;
        }

        $this->parseErrorResult($jsonObject);

        foreach ($jsonObject->set_items as $index => $jsonItem) {
            $item = $factory->createBasketSetItem($jsonItem, $products);
            if ($item->hasErrors()) {
                $this->errors[$index] = $item;
            } else {
                $this->items[$index] = $item;
            }
        }

        $this->totalPrice = isset($jsonObject->total_price) ? $jsonObject->total_price : null;
        $this->totalNet   = isset($jsonObject->total_net)   ? $jsonObject->total_net   : null;
        $this->totalVat   = isset($jsonObject->total_vat)   ? $jsonObject->total_vat   : null;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return BasketVariantItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return $this->errorCode || count($this->errors) > 0;
    }

    /**
     * Get the total price.
     *
     * @return integer
     */
    public function getTotalPrice()
    {
        return $this->totalPrice;
    }

    /**
     * Get the total net.
     *
     * @return integer
     */
    public function getTotalNet()
    {
        return $this->totalNet;
    }

    /**
     * Get the total vat.
     *
     * @return integer
     */
    public function getTotalVat()
    {
        return $this->totalVat;
    }
}