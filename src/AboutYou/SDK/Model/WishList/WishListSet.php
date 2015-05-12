<?php
/**
 * @auther nils.droege@aboutyou.de
 * @author Christian Kilb <christian.kilb@project-collins.com>
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\WishList;

use AboutYou\SDK\Factory\ModelFactoryInterface;
use AboutYou\SDK\Model\Product;
use AboutYou\SDK\Model\ResultError;

/**
 * WishListSet is a class used for adding a set of variant items into the WishList
 *
 * If you want to add a set of variant items into a WishList, you need to create an instance
 * of a WishListSet. The WishListSet contains WishListSetItems.
 *
 * A set can be useful if you want to sell several variants as a single product.
 * For example, if you offer a pair of shoes and additionally different styles of shoelaces
 * the customer can choose from, you maybe want to put both - shoes and laces - together.
 *
 * Example usage:
 * $lacesVariantId = $lacesVariant->getId(); // $lacesVariant is instance of \AboutYou\Model\Variant
 * $shoesVariantID = $shoesVariant->getId(); // $lacesVariant is instance of \AboutYou\Model\Variant
 * $WishListItem1 = new WishListItem($lacesVariantId);
 *
 * $WishListSet = new WishListItemSet('my-personal-identifier');
 * $WishListSet->addItem(new WishListSetItem($lacesVariantId));
 * $WishListSet->addItem(new WishListSetItem($shoesVariantId));
 * $WishListSet->setAdditionalData(['description' => 'Shoes with laces "yellow star"', 'image_url' = 'http://myapp.com/shoes_yello_star.png']);
 * $WishList->updateItemSet($WishListSet)
 * $ay->updateWishList(session_id(), $WishList);
 *
 * You can use the static method create as an alternative to generate a WishList set:
 * $WishListSet = WishListItemSet::create(
 *     'my-personal-identifier',
 *     [
 *         [$lacesVariant->getId()],
 *         [$shoesVariantID->getId()],
 *     ]
 * );
 * @see create()
 *
 * @see     \AboutYou\Model\WishList
 * @see     \AboutYou\SDK\Model\WishList\WishListSetItem
 * @see     \AboutYou\Model\Variant
 * @see     \AboutYou
 */
class WishListSet extends AbstractWishListItem implements WishListItemInterface
{
    /**
     * The ID of this WishList item. You can choose this ID by yourself to identify
     * your item later.
     *
     * @var string $id ID of this WishList item
     */
    protected $id;

    /** @var WishListSetItem[] */
    protected $items;

    /** @var ResultError[] */
    protected $errors;

    /** @var int */
    protected $totalPrice;

    /** @var int */
    protected $totalNet;

    /** @var int */
    protected $totalVat;

    /** @var int */
    protected $setItemAppId = null;

    const IMAGE_URL_REQUIRED = true;

    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     *
     * @param string        $id             ID of the WishList item set.
     * @param array         $additionalData additional data for this item set
     * @param string|null   $addedOn
     */
    public function __construct($id, $additionalData = null, $addedOn = null)
    {
        $this->checkId($id);
        $this->checkAdditionData($additionalData, true);
        $this->id = $id;
        $this->additionalData = $additionalData;
        $this->addedOn = $addedOn ? new \DateTime($addedOn) : null;
    }

    /**
     * @param \stdClass $jsonObject
     * @param ModelFactoryInterface $factory
     * @param Product[] $products
     *
     * @return WishListSet
     */
    public static function createFromJson(\stdClass $jsonObject, ModelFactoryInterface $factory, $products)
    {
        $set = new static(
            $jsonObject->id,
            isset($jsonObject->additional_data) ? (array)$jsonObject->additional_data : null,
            isset($jsonObject->added_on) ? $jsonObject->added_on : null
        );

        $set->parseErrorResult($jsonObject);

        foreach ($jsonObject->set_items as $index => $jsonItem) {
            $item = $factory->createWishListSetItem($jsonItem, $products);
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
     * Create an WishList item set from an array, for example:
     *  WishListSet::create(
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
     * @return WishListSet
     */
    public static function create($itemId, $subItems, array $additionalData = null)
    {
        $set = new static($itemId, $additionalData);
        foreach ($subItems as $itemData) {
            $set->addItem(new WishListSetItem($itemData[0], isset($itemData[1]) ? $itemData[1] : null));
        }

        return $set;
    }

    /**
     * @param WishListSetItem $item
     */
    public function addItem(WishListSetItem $item)
    {
        if (count($this->items) === 0) {
            $this->setItemAppId = $item->getAppId();
        } elseif ($this->setItemAppId !== $item->getAppId()) {
            throw new \InvalidArgumentException('you can not set different app ids for items in an item-set.');
        }

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
     * @return WishListSetItem[]
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

        $items = [];
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
        if ($id !== null && (!is_string($id) || strlen($id) < 2)) {
            throw new \InvalidArgumentException('ID of the WishListSetItem must be a String that must contain minimum two characters');
        }
    }

}
