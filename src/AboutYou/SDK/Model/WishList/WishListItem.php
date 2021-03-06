<?php
/**
 * @auther nils.droege@aboutyou.de
 * @author Christian Kilb <christian.kilb@project-collins.com>
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\WishList;

/**
 * WishListItem is a class used for adding a variant item into the WishList
 *
 * If you want to add a variant into a WishList, you need to create an instance
 * of a WishListItem. The WishListItem represents a variant by it's variantId.
 * It can contain $additionalData that will be transmitted to the merchant untouched.
 *
 * Example usage:
 * $variantId = $variant->getId(); // $variant is instance of \AboutYou\Model\Variant
 * $WishListItem = new WishListItem('my-personal-identifier', $variantId);
 * $WishListItem->setAdditionalData('jeans with engraving "for you"', ['engraving_text' => 'for you']);
 * $ay->addItemToWishList(session_id(), $WishListItem);
 *
 * @see \AboutYou\Model\Variant
 * @see \AboutYou\Model\WishList
 * @see \AY
 */
class WishListItem extends WishListVariantItem implements WishListItemInterface
{
    /**
     * The ID of this WishList item. You can choose this ID by yourself to identify
     * your item later.
     *
     * @var string $id ID of this WishList item
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string  $id if null => id will be generated by the api
     * @param integer $variantId
     * @param array   $additionalData
     * @param null    $addedOn
     * @param integer $appId
     * @param null    $deliveryCarrier
     * @param null    $deliveryEstimation
     * @param null    $packageId
     */
    public function __construct(
        $id,
        $variantId,
        array $additionalData = null,
        $addedOn = null,
        $appId = null,
        $deliveryCarrier = null,
        $deliveryEstimation = null,
        $packageId = null
    ) {
        $this->checkId($id);
        $this->id = $id;
        parent::__construct($variantId, $additionalData, $addedOn, $appId, $deliveryCarrier, $deliveryEstimation, $packageId);
    }

    /**
     * @param object $jsonObject The WishList data.
     * @param Product[] $products
     *
     * @return WishListItem
     *
     * @throws \AboutYou\SDK\Exception\UnexpectedResultException
     */
    public static function createFromJson($jsonObject, array $products)
    {
        $item = new static(
            $jsonObject->id,
            $jsonObject->variant_id,
            isset($jsonObject->additional_data) ? (array)$jsonObject->additional_data : null,
            isset($jsonObject->added_on) ? $jsonObject->added_on : null,
            isset($jsonObject->app_id) ? $jsonObject->app_id : null,
            isset($jsonObject->delivery_carrier) ? $jsonObject->delivery_carrier : null,
            isset($jsonObject->delivery_estimation)
                ? DeliveryEstimation::createFromJSON($jsonObject->delivery_estimation)
                : null,
            isset($jsonObject->package_id) ? intval($jsonObject->package_id) : null
        );

        $item->parseErrorResult($jsonObject);

        $item->jsonObject = $jsonObject;

        if (!empty($jsonObject->product_id)) {
            if (isset($products[$jsonObject->product_id])) {
                $item->setProduct($products[$jsonObject->product_id]);
            } else if (!isset($jsonObject->error_code) || $jsonObject->error_code !== 410) {
                throw new \AboutYou\SDK\Exception\UnexpectedResultException(
                    'Product with ID ' . $jsonObject->product_id . ' expected but was not received with the WishList'
                );
            }
        }
        unset($jsonObject->id, $jsonObject->variant_id, $jsonObject->additional_data, $jsonObject->product_id);

        return $item;
    }

    public function getId()
    {
        return $this->id;
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
