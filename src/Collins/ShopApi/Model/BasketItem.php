<?php
namespace Collins\ShopApi\Model;

class BasketItem extends AbstractModel
{
    protected $variantId;
    protected $additionalData;
    protected $id;
    
    public function __construct($variantId, array $additionalData = array(), $id = null)
    {
        $this->variantId = intval($variantId);
        $this->additionalData = $this->setAdditionalData($additionalData);
        $this->id = $id ? $id : uniqid();
    }
    
    public function getVariantId()
    {
        return $this->variantId;
    }
    
    public function setVariantId($variantId)
    {
        $this->variantId = intval($variantId);
    }
    
    public function getAdditionalData()
    {
        return $this->additionalData;
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
}