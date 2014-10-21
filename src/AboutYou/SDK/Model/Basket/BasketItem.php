<?php
/**
 * @auther nils.droege@aboutyou.de
 * @author Christian Kilb <christian.kilb@project-collins.com>
 * (c) ABOUT YOU GmbH
 */

namespace AboutYou\SDK\Model\Basket;

/**
 * BasketItem is a class used for adding a variant item into the basket
 *
 * If you want to add a variant into a basket, you need to create an instance
 * of a BasketItem. The BasketItem represents a variant by it's variantId.
 * It can contain $additionalData that will be transmitted to the merchant untouched.
 *
 * Example usage:
 * $variantId = $variant->getId(); // $variant is instance of \AboutYou\Model\Variant
 * $basketItem = new BasketItem('my-personal-identifier', $variantId);
 * $basketItem->setAdditionalData('jeans with engraving "for you"', ['engraving_text' => 'for you']);
 * $shopApi->addItemToBasket(session_id(), $basketItem);
 *
 * @see \AboutYou\Model\Variant
 * @see \AboutYou\Model\Basket
 * @see \AY
 */
class BasketItem extends BasketVariantItem implements BasketItemInterface
{
    /**
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     *
     * @var string $id ID of this basket item
     */
    protected $id;

    /**
     * Constructor.
     *
     * @param string $id
     * @param integer $variantId
     * @param array $additionalData
     */
    public function __construct($id, $variantId, array $additionalData = null, $appId = null)
    {
        $this->checkId($id);
        $this->id = $id;
        parent::__construct($variantId, $additionalData, $appId);
    }

    /**
     * @param object $jsonObject The basket data.
     * @param Product[] $products
     *
     * @return BasketItem
     *
     * @throws \AboutYou\SDK\Exception\UnexpectedResultException
     */
    public static function createFromJson($jsonObject, array $products)
    {
        $item = new static(
            $jsonObject->id, 
            $jsonObject->variant_id, 
            isset($jsonObject->additional_data) ? (array)$jsonObject->additional_data : null,
            isset($jsonObject->app_id) ? $jsonObject->app_id : null
        );
        
        $item->parseErrorResult($jsonObject);

        $item->jsonObject = $jsonObject;
                
        if (!empty($jsonObject->product_id)) {
            if (isset($products[$jsonObject->product_id])) {
                $item->setProduct($products[$jsonObject->product_id]);
            } else {
                throw new \AboutYou\SDK\Exception\UnexpectedResultException('Product with ID '.$jsonObject->product_id.' expected but wasnt received with the basket');
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
        if (!is_string($id) || strlen($id) < 2) {
            throw new \InvalidArgumentException('ID of the BasketSetItem must be a String that must contain minimum two characters');            
        }
    }    
}