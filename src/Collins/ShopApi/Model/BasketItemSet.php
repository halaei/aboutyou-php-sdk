<?php
namespace Collins\ShopApi\Model;

class BasketItemSet
{
    protected $id;
    protected $basketItems;
    protected $additionalData;
    
    public function __construct(array $basketItems, array $additionalData = array(), $id = null)
    {
        $this->id = $id ? $id : uniqid();
        $this->additionalData = $this->setAdditionalData($additionalData);
        
        foreach($basketItems as $basketItem) {
            $this->addBasketItem($basketItem);
        }
        
    }
    
    public function addBasketItem(BasketItem $basketItem) {
        $prefix = $basketItem->getId();
        $id = $prefix;
        $i = 2;
        while(isset($this->basketItems[$id])) {
            $id = $prefix.'_'.$i;
        }
        $this->basketItems[$id] = $basketItem;
    }
    
    public function removeBasketItem($id)
    {
        if(isset($this->basketItems[$id])) {
            unset($this->basketItems[$id]);
            return true;
        }
        
        return false;
    }
    
    public function getItems()
    {
        return $this->basketItems;
    }
    
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
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}