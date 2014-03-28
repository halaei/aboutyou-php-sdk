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

    public function __construct($id, $additionalData = null)
    {
        $this->id = $id;
        $this->additionalData = $additionalData;
    }

    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory, $products)
    {
        $set = new self($jsonObject->id, isset($jsonObject->additional_data) ? $jsonObject->additional_data : null);

        $set->parseErrorResult($jsonObject);

        foreach ($jsonObject->set_items as $index => $jsonItem) {
            $item = $factory->createBasketSetItem($jsonItem, $products);
            if ($item->hasErrors()) {
                $set->errors[$index] = $item;
            } else {
                $set->items[$index] = $item;
            }
        }

        $set->totalPrice = isset($jsonObject->total_price) ? $jsonObject->total_price : null;
        $set->totalNet   = isset($jsonObject->total_net)   ? $jsonObject->total_net   : null;
        $set->totalVat   = isset($jsonObject->total_vat)   ? $jsonObject->total_vat   : null;

        return $set;
    }

    /**
     * Create an basket item set from an array, for example:
     *  BasketSet::create(
     *      'identifier4',
     *      [
     *          [12312121],
     *          [7777777, ['description' => 'engravingssens', 'internal_infos' => ['stuff']]]
     *      ],
     *      ['description' => 'Wunderschön und so']
     *  );
     *
     * @param $itemId
     * @param $subItems
     * @param array $additionalData
     *
     * @return BasketSet
     */
    public static function create($itemId, $subItems, array $additionalData = null)
    {
        $set = new self($itemId, $additionalData);
        foreach ($subItems as $itemData) {
            $set->addItem(new BasketVariantItem($itemData[0], isset($itemData[1]) ? $itemData[1] : null));
        }

        return $set;
    }

    /**
     * @param BasketVariantItem $item
     */
    public function addItem(BasketVariantItem $item)
    {
        $this->items[] = $item;
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