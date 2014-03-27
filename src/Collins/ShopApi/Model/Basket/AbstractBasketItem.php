<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model\Basket;

use Collins\ShopApi\Model\ResultError;

class AbstractBasketItem extends ResultError
{
    /** @var array */
    protected $additionalData;

    /** @var boolean */
    protected $isChanged = false;

    public function isChanged()
    {
        return $this->isChanged;
    }

    /**
     * @return string|null
     */
    public function getDescription()
    {
        return isset($this->additionalData) ?
            $this->additionalData->description :
            null
        ;
    }

    /**
     * @return array|null
     */
    public function getCustomData()
    {
        return isset($this->additionalData) && isset($this->additionalData->internal_infos) ?
            $this->additionalData->internal_infos :
            null
            ;
    }

    /**
     * @return array|null
     */
    public function getAdditionalData()
    {
        return isset($this->additionalData) ?
            $this->additionalData :
            null
            ;
    }

    /**
     * @param string $description
     * @param array $customData
     */
    public function setAdditionData($description, array $customData = null)
    {
        $this->isChanged = true;

        $data = array(
            'description' => $description
        );
        if (!empty($customData)) {
            $data['internal_infos'] = array_values($customData);
        }

        $this->additionalData = $data;
    }
} 