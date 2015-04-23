<?php
namespace AboutYou\SDK\Model\ProductSearchResult;

use DateTime;
use stdClass;

/**
 * NewInCount
 *
 * @author Holger Reinhardt <holger.reinhardt@aboutyou.de>
 */
class NewInCount
{
    /**
     * @var int
     */
    protected $productCount = 0;

    /**
     * @var int
     */
    protected $timestamp = 0;

    /**
     * @var DateTime|null
     */
    protected $date;

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @param int $productCount
     */
    public function setProductCount($productCount)
    {
        $this->productCount = (int)$productCount;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = (int)$timestamp;
    }

    /**
     * @return DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param DateTime $date
     */
    public function setDate(DateTime $date)
    {
        $this->date = $date;
    }
}
