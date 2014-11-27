<?php
namespace AboutYou\SDK\Model;

use AboutYou\SDK\Factory\ModelFactoryInterface;
use AboutYou\SDK\Model\Basket\BasketSet;
use AboutYou\SDK\Model\Basket\BasketItem;

/**
 *
 */
class Basket
{
    /** @var AbstractBasketItem[] */
    private $items = array();

    private $errors = array();

    /** @var integer */
    protected $uniqueVariantCount;

    /** @var Product[] */
    protected $products;

    /** @var integer */
    protected $totalPrice;

    /** @var integer */
    protected $totalNet;

    /** @var integer */
    protected $totalVat;
    
    /** @var boolean */
    protected $clear = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param object $jsonObject
     * @param ModelFactoryInterface $factory
     *
     * @return Basket
     */
    public static function createFromJson($jsonObject, ModelFactoryInterface $factory)
    {
        $basket = new static();
        $basket->totalPrice = $jsonObject->total_price;
        $basket->totalNet   = $jsonObject->total_net;
        $basket->totalVat   = $jsonObject->total_vat;

        $basket->parseItems($jsonObject, $factory);

        return $basket;
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

    /**
     * Get the total amount of all items.
     *
     * @return integer
     */
    public function getTotalAmount()
    {
        return count($this->items);
    }

    /**
     * Get the number of variants.
     *
     * @return integer
     */
    public function getTotalVariants()
    {
        return $this->uniqueVariantCount;
    }

    /**
     * @return boolean
     */
    public function hasErrors()
    {
        return count($this->errors) > 0;
    }

    /**
     * Returns all items with errors
     *
     * @return BasketItem[]|BasketSet[]
     */
    public function getErrors()
    {
        return $this->errors;
    }


    /**
     * Get all basket items.
     *
     * @return BasketItem[]|BasketSet[]
     */
    public function getItems()
    {
        return array_values($this->items);
    }

    /**
     * @param $itemId
     *
     * @return BasketItem|BasketSet|null
     */
    public function getItem($itemId)
    {
        return isset($this->items[$itemId]) ?
            $this->items[$itemId] :
            null
        ;
    }

    /**
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }
    
    public function getCollectedItems()
    {
        $items = $this->getItems();
        $itemsMerged = array();
        foreach ($items as $item) {
            $key = $item->getUniqueKey();
            if (isset($itemsMerged[$key])) {
                $amount = $itemsMerged[$key]['amount'] + 1;
                $itemsMerged[$key] = array(
                    'item' => $item,
                    'price' => $item->getTotalPrice() * $amount,
                    'amount' => $amount
                );
            } else {
                $itemsMerged[$key] = array(
                    'item' => $item,
                    'price' => $item->getTotalPrice(),
                    'amount' => 1
                );
            }
        }

        return array_values($itemsMerged);
    }

    /**
     * build order line for update query
     * @return array
     */
    public function getOrderLinesArray()
    {
        $orderLines = array();
            
        foreach (array_unique($this->deletedItems) as $itemId) {
            $orderLines[] = array('delete' => $itemId);
        }

        foreach ($this->updatedItems as $item) {
            $orderLines[] = $item;
        }
        
        return $orderLines;
    }

    protected function parseItems($jsonObject, ModelFactoryInterface $factory)
    {
        $products = array();
        if (!empty($jsonObject->products)) {
            foreach ($jsonObject->products as $productId => $jsonProduct) {
                $products[$productId] = $factory->createProduct($jsonProduct);
            }
        }
        $this->products = $products;

        $vids = array();
        if (!empty($jsonObject->order_lines)) {
            foreach ($jsonObject->order_lines as $index => $jsonItem) {
                if (isset($jsonItem->set_items)) {
                    $item = $factory->createBasketSet($jsonItem, $products);
                } else {
                    $vids[] = $jsonItem->variant_id;
                    $item = $factory->createBasketItem($jsonItem, $products);
                }

                if ($item->hasErrors()) {
                    $this->errors[$index] = $item;
                } else {
                    $this->items[$item->getId()] = $item;
                }
            }
        }

        $vids = array_values(array_unique($vids));
        $this->uniqueVariantCount = count($vids);
    }

    /*
     * Methods to manipulate basket
     *
     * this api is unstable method names and signatures may be changed in the future
     */

    /** @var array */
    protected $deletedItems = array();
    /** @var array */
    protected $updatedItems = array();

    /**
     * @param string $itemId
     *
     * @return $this
     */
    public function deleteItem($itemId)
    {
        $this->deletedItems[$itemId] = $itemId;

        return $this;
    }

    /**
     * @param string[] $itemIds
     *
     * @return $this
     */
    public function deleteItems(array $itemIds)
    {
        $this->deletedItems = array_merge($this->deletedItems, $itemIds);

        return $this;
    }
    
    /**
     * @return $this
     */
    public function deleteAllItems($delete = true)
    {
        $this->clear = $delete !== false || $delete !== 0;

        return $this;
    }
    
    /**
     * @return boolean
     */
    public function isBasketClearedOnUpdate()
    {
        return $this->clear;
    }

    /**
     * @param BasketItem $basketItem
     *
     * @return $this
     */
    public function updateItem(BasketItem $basketItem)
    {
        $itemId = $basketItem->getId();
        $item = array(
            'variant_id' => $basketItem->getVariantId(),
            'app_id' => $basketItem->getAppId()
        );
        if ($itemId) {
            $item['id'] = $itemId;
        }
        
        $additionalData = $basketItem->getAdditionalData();
        if (!empty($additionalData)) {
            $this->checkAdditionData($additionalData);
            $item['additional_data'] = (array)$additionalData;
        }

        if ($itemId) {
            $this->updatedItems[$basketItem->getId()] = $item;
        } else {
            $this->updatedItems[] = $item;
        }

        return $this;
    }

    /**
     * @param BasketSet $basketSet
     *
     * @return $this
     */
    public function updateItemSet(BasketSet $basketSet)
    {
        $items = $basketSet->getItems();
        
        if (empty($items)) {
            throw new \InvalidArgumentException('BasketSet needs at least one item');            
        }

        $itemSet = array();
        foreach ($items as $subItem) {
            $item = array(
                'variant_id' => $subItem->getVariantId(),
                'app_id' => $subItem->getAppId()
            );
            $additionalData = $subItem->getAdditionalData();
            if (!empty($additionalData)) {
                $this->checkAdditionData($additionalData);
                $item['additional_data'] = (array)$additionalData;
            }
            $itemSet[] = $item;
        }

        $this->updatedItems[$basketSet->getId()] = array(
            'id' => $basketSet->getId(),
            'additional_data' => (array)$basketSet->getAdditionalData(),
            'set_items' => $itemSet,
        );

        return $this;

    }

    protected function checkAdditionData(array $additionalData = null, $imageUrlRequired = false)
    {
        if ($additionalData && !isset($additionalData['description'])) {
            throw new \InvalidArgumentException('description is required in additional data');
        }

        if (isset($additionalData['internal_infos']) && !is_array($additionalData['internal_infos'])) {
            throw new \InvalidArgumentException('internal_infos must be an array');
        }
    }
}
