<?php
/**
 * CoOrdinates
 */

namespace Heyday\NZTMLocationConvertor;

class CoOrdinates
{
    const DECIMAL_PLACES = 4;

    public $latitude;
    public $longitude;


    /**
     * @return mixed
     */
    public function getFormattedLatitude()
    {
        return floatval($this->latitude * TMProjection::RAD2DEG);
    }

    /**
     * @return mixed
     */
    public function getFormattedLongitude()
    {
        return floatval($this->longitude * TMProjection::RAD2DEG);
    }


    /**
     * @param $number
     * @return float
     */
    public static function truncateNumber($number)
    {
        return floatval(bcdiv($number, 1, self::DECIMAL_PLACES));
    }



    /**
     * @return string
     */
    public function __toString()
    {
        $latitude = $this->getFormattedLatitude();
        $longitude = $this->getFormattedLongitude();

        return "{$latitude}, {$longitude}";
    }

}

