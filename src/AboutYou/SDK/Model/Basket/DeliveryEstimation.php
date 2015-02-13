<?php
namespace AboutYou\SDK\Model\Basket;

class DeliveryEstimation
{
    /**
     * @var int
     */
    protected $minDays;

    /**
     * @var int
     */
    protected $maxDays;

    /**
     * @var \DateTime
     */
    protected $minDate;

    /**
     * @var \DateTime
     */
    protected $maxDate;

    public static function createFromJSON($jsonObject)
    {
        return new DeliveryEstimation(
            isset($jsonObject->min) ? intval($jsonObject->min) : null,
            isset($jsonObject->max) ? intval($jsonObject->max) : null,
            isset($jsonObject->min_date) ? new \DateTime($jsonObject->min_date) : null,
            isset($jsonObject->max_date) ? new \DateTime($jsonObject->max_date) : null
        );
    }

    public function __construct($minDays, $maxDays, $minDate, $maxDate)
    {
        $this->minDays = $minDays;
        $this->maxDays = $maxDays;
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
    }

    /**
     * @return int
     */
    public function getMinDays()
    {
        return $this->minDays;
    }

    /**
     * @return int
     */
    public function getMaxDays()
    {
        return $this->maxDays;
    }

    /**
     * @return \DateTime
     */
    public function getMinDate()
    {
        return $this->minDate;
    }

    /**
     * @return \DateTime
     */
    public function getMaxDate()
    {
        return $this->maxDate;
    }
}