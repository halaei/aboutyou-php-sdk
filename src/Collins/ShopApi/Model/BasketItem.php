<?php
namespace Collins\ShopApi\Model;

/**
 * BasketItem is a class used for adding a variant item into the basket
 *
 * If you want to add a variant into a basket, you need to create an instance
 * of a BasketItem. The BasketItem represents a variant by it's variantId.
 * It can contain $additionalData that will be transmitted to the merchant untouched.
 * 
 * Example usage:
 * $variantId = $variant->getId(); // $variant is instance of \Collins\ShopApi\Model\Variant
 * $basketItem = new BasketItem($variantId);
 * $basketItem->setId('my-personal-identifier');
 * $basketItem->setAdditionalData(['description' => 'jeans with engraving "for you"', 'engraving_text' => 'for you']);
 * $shopApi->addItemToBasket(session_id(), $basketItem);
 *
 * @author  Christian Kilb <christian.kilb@antevorte.org>
 * @see     \Collins\ShopApi\Model\Variant
*/
class BasketItem extends AbstractModel
{
    /**
     * @var int $variantId ID of the Variant
     */
    protected $variantId;
    
    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass a different image URL,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @var array $additionalData additional data for this variant
     */
    protected $additionalData;
    
    /**
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     * 
     * @var string $id ID of this basket item
     */
    protected $id;
    
    /**
     * 
     * @param int $variantId ID of the variant
     * @param array $additionalData additional data for this variant
     * 
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass a different image URL,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @param string $id ID of the basket item.
     * 
     * You can choose this ID by yourself to identify
     * your item later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     */
    public function __construct($variantId, array $additionalData = array(), $id = null)
    {
        $this->variantId = intval($variantId);
        $this->additionalData = $this->setAdditionalData($additionalData);
        $this->id = $this->setId($id);
    }
    
    /**
     * Returns the variant ID of this basket item.
     * 
     * @return int
     */
    public function getVariantId()
    {
        return $this->variantId;
    }
    
    /**
     * Sets the variant ID of this basket item.
     * 
     * @return int $variant ID of the variant
     */
    public function setVariantId($variantId)
    {
        $this->variantId = intval($variantId);
    }
    
    /**
     * Sets the variant ID of this basket item.
     * 
     * @return int $variant ID of the variant
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
    
    
    /** @param array $additionalData additional data for this variant
     * 
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass a different image URL,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @param array $additionalData
     * @throws \Collins\ShopApi\Exception\InvalidParameterException
     */
    public function setAdditionalData(array $additionalData)
    {
        if(count($additionalData) && !isset($additionalData['description'])) {
            throw new \Collins\ShopApi\Exception\InvalidParameterException('If $additionalData is not empty, key "description" must exist.');
        }
        elseif(isset($additionalData['image_url']) && !is_string($additionalData['imageUrl'])) {
            throw new \Collins\ShopApi\Exception\InvalidParameterException('If $additionalData["image_url"] is set, it must be a string.');
        }
        $this->additionalData = $additionalData;
    }
    
    /**
     * Returns the ID of this basket item.
     * 
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * Sets the ID of this basket item.
     * 
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     * 
     * @param string $id ID of this basket item
     */
    public function setId($id)
    {
        $this->id = $id ? $id : uniqid();
    }
}