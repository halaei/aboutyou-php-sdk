<?php
/**
 * @author nils.droege@project-collins.com
 * (c) Collins GmbH & Co KG
 */

namespace Collins\ShopApi\Model\ProductSearchResult;


class PriceRange
{
    /** @var object */
    protected $jsonObject;

    /**
     * Expected json format
     * {
     * "count": 25138,
     * "from": 0,
     * "min": 399,
     * "max": 19999,
     * "to": 20000,
     * "total_count": 25138,
     * "total": 133930606,
     * "mean": 5327.8147028403
     * }
     *
     * @param object $jsonObject
     */
    public function __construct($jsonObject)
    {
        $this->jsonObject = $jsonObject;
    }

    /**
     * @return integer
     */
    public function getProductCount()
    {
        return $this->jsonObject->count;
    }

    /**
     * in euro cent
     * @return integer
     */
    public function getFrom()
    {
        return $this->jsonObject->from;
    }

    /**
     * in euro cent
     * @return integer
     */
    public function getTo()
    {
        return isset($this->jsonObject->to) ? $this->jsonObject->to : null;
    }

    /**
     * in euro cent
     * @return integer
     */
    public function getMin()
    {
        return isset($this->jsonObject->min) ? $this->jsonObject->min : null;
    }

    /**
     * in euro cent
     * @return integer
     */
    public function getMax()
    {
        return isset($this->jsonObject->max) ? $this->jsonObject->max : null;
    }

    /**
     * in euro cent
     * @return integer
     */
    public function getMean()
    {
        return (int)round($this->jsonObject->mean);
    }

    /**
     * sum over all product min prices in this range
     * @return integer
     */
    public function getSum()
    {
        return $this->jsonObject->total;
    }
}