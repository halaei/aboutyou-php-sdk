<?php
namespace Collins\ShopApi\Model;
class BasketVariantSet extends BasketObject implements BasketObjectInterface
{
    protected $items = null;
    
    public function fromJson($jsonObject)
    {
        $this->id = $jsonObject->id;
        $this->additionalData = isset($jsonObject->additional_data) ? $jsonObject->additional_data : array();
        $this->items = self::parseItems($jsonObject, $this->basket);
    }
    
    protected static function parseItems($jsonObject, $basket)
    {
        $items = array();
        foreach($jsonObject->set_items as $item) {
            $basketVariant = new BasketVariant($item, $basket);
            $items[] = $basketVariant;
        }
        
        return $items;
    }
    
    public function getBasketVariants()
    {
        return $this->items;
    }
    
    public function isVariant()
    {
        return false;
    }
}