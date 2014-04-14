<?php
/**
 * @auther nils.droege@project-collins.com
 * @author Christian Kilb <christian.kilb@project-collins.com>
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;

use Collins\ShopApi\Factory\ModelFactoryInterface;

/**
 * BasketSet is a class used for adding a set of variant items into the basket
 *
 * If you want to add a set of variant items into a basket, you need to create an instance
 * of a BasketSet. The BasketSet contains BasketSetItems.
 *
 * A set can be useful if you want to sell several variants as a single product.
 * For example, if you offer a pair of shoes and additionally different styles of shoelaces
 * the customer can choose from, you maybe want to put both - shoes and laces - together.
 *
 * Example usage:
 * $lacesVariantId = $lacesVariant->getId(); // $lacesVariant is instance of \Collins\ShopApi\Model\Variant
 * $shoesVariantID = $shoesVariant->getId(); // $lacesVariant is instance of \Collins\ShopApi\Model\Variant
 * $basketItem1 = new BasketItem($lacesVariantId);
 *
 * $basketSet = new BasketItemSet('my-personal-identifier');
 * $basketSet->addItem(new BasketSetItem($lacesVariantId));
 * $basketSet->addItem(new BasketSetItem($shoesVariantId));
 * $basketSet->setAdditionalData(['description' => 'Shoes with laces "yellow star"', 'image_url' = 'http://myapp.com/shoes_yello_star.png']);
 * $basket->updateItemSet($basketSet)
 * $shopApi->updateBasket(session_id(), $basket);
 *
 * You can use the static method create as an alternative to generate a basket set:
 * $basketSet = BasketItemSet::create(
 *     'my-personal-identifier',
 *     [
 *         [$lacesVariant->getId()],
 *         [$shoesVariantID->getId()],
 *     ]
 * );
 * @see create()
 *
 * @see     \Collins\ShopApi\Model\Basket
 * @see     \Collins\ShopApi\Model\Basket\BasketSetItem
 * @see     \Collins\ShopApi\Model\Variant
 * @see     \Collins\ShopApi
 */
class BasketSet extends AbstractBasketItem implements BasketItemInterface
{
    /**
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     *
     * @var string $id ID of this basket item
     */
    protected $id;

    /** @var BasketSetItem[] */
    protected $items;

    /** @var ResultError[] */
    protected $errors;

    /** @var interger */
    protected $totalPrice;

    /** @var interger */
    protected $totalNet;

    /** @var interger */
    protected $totalVat;

    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     *
     * @param string $id ID of the basket item set.
     * @param array $additionalData additional data for this item set
     */
    public function __construct($id, $additionalData = null)
    {
        $this->checkId($id);
        $this->checkAdditionData($additionalData, true);
        $this->id = $id;
        $this->additionalData = $additionalData;
    }

    /**
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     * @param Product[] $products
     *
     * @return BasketSet
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory, $products)
    {
        $set = new static($jsonObject->id, isset($jsonObject->additional_data) ? (array)$jsonObject->additional_data : null);

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
        $set = new static($itemId, $additionalData);
        foreach ($subItems as $itemData) {
            $set->addItem(new BasketSetItem($itemData[0], isset($itemData[1]) ? $itemData[1] : null));
        }

        return $set;
    }

    /**
     * @param BasketSetItem $item
     */
    public function addItem(BasketSetItem $item)
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
     * @return BasketSetItem[]
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

    public function getUniqueKey()
    {
        $key = ':';
        $additionalData = $this->additionalData;
        if (!empty($additionalData)) {
            ksort($additionalData);
            $key .= ':' . json_encode($additionalData);
        }

        $items = array();
        foreach ($this->items as $item) {
            $items[] = $item->getUniqueKey();
        }
        $key .= json_encode($items);

        return $key;
    }
    
    /**
     * @param mixed $id
     * @throws \InvalidArgumentException
     */
    protected function checkId($id) 
    {
        if(!is_string($id) || strlen($id) < 2) {
            throw new \InvalidArgumentException('ID of the BasketSetItem must be a String that must contain minimum two characters');            
        }
    }
    
    /**
     * Additional data are transmitted to the merchant untouched.
     * It must be set, a key "description" and "image_url" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @param array $additionalData additional data for this item set
     * @throws \InvalidArgumentException
     */    
/*    protected function checkAdditionData(array $additionalData = null)
    {
        if(count($additionalData) < 2 && (!isset($additionalData['description']) || !isset($additionalData['image_url']))) {
            throw new \InvalidArgumentException('$additionalData must be set, key "description" and "image_url" must exist.');
        }
        
        if(!is_string($additionalData['image_url'])) {
            throw new \InvalidArgumentException('If $additionalData["image_url"] is set, it must be a string.');
        }
    }    */
}