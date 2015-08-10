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

    /**
     * @var boolean
     */
    protected $holiday;

    /**
     * @var boolean
     */
    protected $weekend;

    /**
     * @var boolean
     */
    protected $crossdocked;

    public static function createFromJSON($jsonObject)
    {
        return new DeliveryEstimation(
            isset($jsonObject->min) ? intval($jsonObject->min) : null,
            isset($jsonObject->max) ? intval($jsonObject->max) : null,
            isset($jsonObject->min_date) ? new \DateTime($jsonObject->min_date) : null,
            isset($jsonObject->max_date) ? new \DateTime($jsonObject->max_date) : null,
            isset($jsonObject->crossdocked) ? boolval($jsonObject->crossdocked) : null,
            isset($jsonObject->holiday) ? boolval($jsonObject->holiday) : null,
            isset($jsonObject->weekend) ? boolval($jsonObject->weekend) : null

        );
    }

    public function __construct($minDays, $maxDays, $minDate, $maxDate, $crossdocked, $holiday, $weekend)
    {
        $this->minDays = $minDays;
        $this->maxDays = $maxDays;
        $this->minDate = $minDate;
        $this->maxDate = $maxDate;
        $this->crossdocked = $crossdocked;
        $this->holiday = $holiday;
        $this->weekend = $weekend;
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

    /**
     * @return boolean
     */
    public function getHoliday()
    {
        return $this->holiday;
    }

    /**
     * @return boolean
     */
    public function getWeekend()
    {
        return $this->weekend;
    }

    /**
     * @return boolean
     */
    public function getCrossdocked()
    {
        return $this->crossdocked;
    }
}
