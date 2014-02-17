<?php
/**
 * @auther nils.droege@antevorte.org
 * (c) Antevorte GmbH & Co KG
 */

namespace Collins\ShopApi\Model;

use Collins\ShopApi;
use Collins\ShopApi\Model\AttributeGroup;

class ProductAttributes
{
    /** @var array */
    protected $ids;

    /** @var AttributeGroup[] */
    protected $groups;

    /** @var Attribute[] */
    protected $attributes;

    /**
     * @param array $ids two dimensional array of group ids and array ids
     */
    public function __construct(array $ids)
    {
        $this->ids = $ids;
        $this->fetch();
    }

    protected function fetch()
    {
        // TODO: Refactore me
        $shopApi = ShopApi::getCurrentApi();

        $groupIds = array_keys($this->ids);
        $allAttributes = $shopApi->fetchAttributes($groupIds);

        foreach ($this->ids as $groupId => $attributeIds) {
            foreach ($attributeIds as $attributeId) {
                $key = $groupId . '_' . $attributeId;
                if (!isset($allAttributes[$key])) {
                    // TODO: error handling
                    continue;
                }

                $attribute = $allAttributes[$key];

                if (isset($this->groups[$groupId])) {
                    $group = $this->groups[$groupId];
                } else {
                    $group = new AttributeGroup($attribute->getGroupId(), $attribute->getGroupName());
                    $this->groups[$groupId] = $group;
                }

                $group->addAttribute($attribute);
                $this->attributes[$attribute->getUniqueKey()] = $attribute;
            }

        }
    }

    /**
     * @return AttributeGroup[]
     */
    public function getGroups()
    {
        if ($this->groups === null) {
            $this->fetch();
        }

        return $this->groups;
    }

    /**
     * @return AttributeGroup
     */
    public function getGroup($groupId)
    {
        $groups = $this->getGroups();
        if( isset($groups[$groupId]) ) {
            return $groups[$groupId];
        }
        return null;
    }

    /**
     * @param integer $attributeId
     *
     * @return Attribute|null
     */
    public function getAttributeByKey($key)
    {
        return
            isset($this->attributes[$key]) ?
            $this->attributes[$key] :
            null
        ;
    }
}