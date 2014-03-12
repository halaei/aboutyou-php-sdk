<?php
namespace Collins\ShopApi\Model;


/**
 * BasketItemSet is a class used for adding a set of variant items into the basket
 *
 * If you want to add a set of variant items into a basket, you need to create an instance
 * of a BasketItemSet. The BasketItemSet contains BasketItem.
 * 
 * A set can be useful if you want to sell several variants as a single product.
 * For example, if you offer a pair of shoes and additionally different styles of shoelaces
 * the customer can choose from, you maybe want to put both - shoes and laces - together.

 * 
 * Example usage:
 * $lacesVariantId = $lacesVariant->getId(); // $lacesVariant is instance of \Collins\ShopApi\Model\Variant
 * $shoesVariantID = $shoesVariant->getId(); // $lacesVariant is instance of \Collins\ShopApi\Model\Variant
 * $basketItem1 = new BasketItem($lacesVariantId);
 * $basketItem2 = new BasketItem($shoesVariantId);
 * 
 * $basketItemSet = new BasketItemSet([$basketItem1, $basketItem2]);
 * $basketItemSet->setAddition
 * $basketItemSet->setAdditionalData(['description' => 'Shoes with laces "yellow star"', 'image_url' = 'http://myapp.com/shoes_yello_star.png']);
 * $shopApi->addItemSetToBasket(session_id(), $basketItemSet);
 *
 * @author  Christian Kilb <christian.kilb@antevorte.org>
 * @see     \Collins\ShopApi\Model\BasketItem
 * @see     \Collins\ShopApi\Model\Variant
*/
class BasketItemSet
{
    /**
     * The ID of this basket item. You can choose this ID by yourself to identify
     * your item later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     * 
     * @var string $id ID of this basket item
     */
    protected $id;
    
    /**
     * @var BasketItem[] array of BasketItems
     */
    protected $basketItems;
    
    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @var array $additionalData additional data for this item set
     */
    protected $additionalData;
    
    /**
     * 
     * @param int $variantId ID of the variant
     * @param array $additionalData additional data for this item set
     * 
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @param string $id ID of the basket item set.
     * 
     * You can choose this ID by yourself to identify
     * your item set later.
     * If you don't pass any ID ($id = null), uniquid() will be used.
     */
    public function __construct(array $basketItems, array $additionalData = array(), $id = null)
    {
        
        $this->setId($id);
        $this->setAdditionalData($additionalData);
        
        foreach($basketItems as $basketItem) {
            $this->addBasketItem($basketItem);
        }
        
    }
    
    /**
     * Adds a basket item to this set.
     * 
     * @param \Collins\ShopApi\Model\BasketItem $basketItem
     */
    public function addBasketItem(BasketItem $basketItem) {
        $prefix = $basketItem->getId();
        $id = $prefix;
        $i = 2;
        while(isset($this->basketItems[$id])) {
            $id = $prefix.'_'.$i;
        }
        $this->basketItems[$id] = $basketItem;
    }
    
    /**
     * Removes a basket item from this set by it's ID.
     * 
     * @param type $id ID of the basket item
     * @return boolean true if item found and removed
     */
    public function removeBasketItem($id)
    {
        if(isset($this->basketItems[$id])) {
            unset($this->basketItems[$id]);
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns all the basket items of this set
     * 
     * @return BasketItem[]
     */
    public function getItems()
    {
        return $this->basketItems;
    }
    
    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @param array $additionalData additional data for this item set
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
     * Returns the ID of this basket item set.
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
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id ? $id : uniqid();
    }
    
    /**
     * Additional data are transmitted to the merchant untouched.
     * If set (array not empty), a key "description" must exist. This description
     * must be a string that describes the variant. If you want to pass an image URL that
     * represents this item set,
     * you can add a key "image_url" to the $additionalData that contains the URL to the image.
     * 
     * @return array additional data
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}