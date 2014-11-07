<?php
namespace AboutYou\SDK\Criteria;

abstract class AbstractCriteria implements \AboutYou\SDK\Criteria\CriteriaInterface
{
    /**
     * @return array
     */
    public function toArray()
    {
        return array();
    }
}