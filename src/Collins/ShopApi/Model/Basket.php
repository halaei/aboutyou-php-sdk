<?php
namespace Collins\ShopApi\Model;

use Collins\ShopApi\Factory\ModelFactoryInterface;
use Collins\ShopApi\Model\Basket\BasketVariantItem;
use Collins\ShopApi\Model\Basket\BasketSet;
use Collins\ShopApi\Model\Basket\BasketItem;

/**
 *
 */
class Basket
{
    /** @var object */
    protected $jsonObject = null;

    /** @var ModelFactoryInterface */
    protected $factory;

    /** @var AbstractBasketItem[] */
    private $items = [];

    private $errors = [];

    /** @var integer */
    protected $uniqueVariantCount;

    /**
     * Constructor.
     *
     * @param object $jsonObject The basket data.
     */
    public function __construct($jsonObject, ModelFactoryInterface $factory)
    {
        $this->jsonObject = $jsonObject;
        $this->factory    = $factory;
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
     * Get the total net.
     *
     * @return integer
     */
    public function getTotalNet()
    {
        return $this->jsonObject->total_net;
    }

    /**
     * Get the total vat.
     *
     * @return integer
     */
    public function getTotalVat()
    {
        return $this->jsonObject->total_vat;
    }

    /**
     * Get the total amount of all items.
     *
     * @return integer
     */
    public function getTotalAmount()
    {
        if (!$this->items) {
            $this->parseItems();
        }

        return count($this->items);
    }

    /**
     * Get the number of variants.
     *
     * @return integer
     */
    public function getTotalVariants()
    {
        if (!$this->items) {
            $this->parseItems();
        }

        return $this->uniqueVariantCount;
    }

    public function hasErrors()
    {
        if (!$this->items) {
            $this->parseItems();
        }

        return count($this->errors) > 0;
    }

    /**
     * Get all basket items.
     *
     * @return BasketItem[]|BasketSet[]
     */
    public function getItems()
    {
        if (!$this->items) {
            $this->parseItems();
        }

        return $this->items;
    }

    /**
     * @return array
     */
    public function getOrderLinesArray()
    {
        $orderLines = [
        ];

        return $orderLines;
    }

    protected function parseItems()
    {
        $factory = $this->factory;

        $products = [];
        foreach ($this->jsonObject->products as $productId => $jsonProduct) {
            $products[$productId] = $factory->createProduct($jsonProduct);
        }
        unset($this->jsonObject->products);

        $vids = [];
        foreach ($this->jsonObject->order_lines as $index => $jsonItem) {
            if (isset($jsonItem->set_items)) {
                $item = $factory->createBasketSet($jsonItem, $products);
            } else {
                $item = $factory->createBasketItem($jsonItem, $products);
                $vids[] = $jsonItem->variant_id;
            }

            if ($item->hasErrors()) {
                $this->errors[$index] = $item;
            } else {
                $this->items[$index] = $item;
            }
        }
        unset($this->jsonObject->order_lines);

        array_unique($vids);
        $this->uniqueVariantCount = count($vids);
    }
}
