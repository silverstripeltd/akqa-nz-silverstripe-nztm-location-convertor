<?php

/**
 * TMProjectionTest.php.
 */

require_once __DIR__ . "/../code/TMProjection.php";

use PHPUnit\Framework\TestCase;

class TMProjectionTest extends TestCase
{
    protected static $fixture_file = 'station-location-data.yml';

    /**
     *
     */
    public function testNZTMConversion()
    {
        /**
         * Station ID ,Station Name,Ycoord,Xcoord,Latitude,Longitude
         */
        $testData = explode("\n", file_get_contents(__DIR__ . "/fixtures/station-location-data-subset.csv"));

        $tm = TMProjection::CreateDefaultTMProjection();

        $count = 0;
        foreach ($testData as $row) {
            if ($count > 0) {
                $data = explode(',', $row);

                $easting = $data[3];
                $northing = $data[2];
                $coOrdinates = TMProjection::tm_eod($tm, $easting, $northing);

                $latitude = CoOrdinates::truncateNumber($coOrdinates->getFormattedLatitude());
                $referenceLatitude = CoOrdinates::truncateNumber(floatval($data[4]));
                $this->assertEquals($latitude, $referenceLatitude);

                $longitude = CoOrdinates::truncateNumber($coOrdinates->getFormattedLongitude());
                $referenceLongitude = CoOrdinates::truncateNumber(floatval($data[5]));
                $this->assertEquals($longitude, $referenceLongitude);
            }
            $count++;
        }
    }

}
