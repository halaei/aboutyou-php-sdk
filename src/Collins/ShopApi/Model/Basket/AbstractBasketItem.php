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

    protected function checkAdditionData(array $additionalData = null, $imageUrlRequired = false)
    {
        if ($additionalData) {
            if (!isset($additionalData['description'])) {
                throw new InvalidParameterException('description is required in additional data');
            }
            if ($imageUrlRequired && !isset($additionalData['image_url'])) {
                throw new InvalidParameterException('image_url is required in additional data');
            }
        }

        if (isset($additionalData['internal_infos']) && !is_array($additionalData['internal_infos'])) {
            throw new InvalidParameterException('internal_infos must be an array');
        }
    }
}