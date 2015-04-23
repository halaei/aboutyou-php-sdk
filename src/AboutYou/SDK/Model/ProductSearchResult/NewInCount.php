<?php
namespace AboutYou\SDK\Model\ProductSearchResult;

use DateTime;

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

    public function __construct($productCount, $timestamp, DateTime $date)
    {
        $this->productCount = (int)$productCount;
        $this->timestamp = (int)$timestamp;
        $this->date = $date;
    }

    /**
     * @return int
     */
    public function getProductCount()
    {
        return $this->productCount;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @return DateTime|null
     */
    public function getDate()
    {
        return $this->date;
    }
}
