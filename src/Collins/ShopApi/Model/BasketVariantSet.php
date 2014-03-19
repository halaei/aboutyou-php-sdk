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
    
    public function getTotalPrice()
    {
        $items = $this->getBasketVariants();
        $price = 0;
        foreach($items as $item) {
            $price = $price+$item->getTotalPrice();
        }
        
        return $price;
    }
    
    /**
     * Returns true if all the properties but the ID match.
     * 
     * @param \Collins\ShopApi\Model\BasketVariant $basketVariant BasketVariant to compare with
     */
    public function isEqual(BasketObject $basketVariant)
    {
        return $this->getUniqueKey() == $basketVariant->getUniqueKey();
    }
    
    public function getUniqueKey()
    {
        $properties = array(
            'additionalData'
        );
        
        $key = '';
        foreach($properties as $property) {
            $key .= json_encode($this->$property);
        }
        
        $basketVariantKeys = array();
        foreach($this->getBasketVariants() as $basketVariant) {
            $basketVariantKeys[] = $basketVariant->getUniqueKey();
        }
        
        sort($basketVariantKeys); // sort keys of variants, so the order of them doesn't matter
        
        $key .= implode('-',$basketVariantKeys);

        return md5($key);
    }
}