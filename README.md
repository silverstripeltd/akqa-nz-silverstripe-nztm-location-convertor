# SilverStripe NZ Transverse Mercator Projection Conversion

This module is designed to convert New Zealand Transverse Mercator (NZTM) projections to latitude longitude on the
New Zealand Geodetic Datum 2000.

Northings and eastings are in metres. Latitudes and longitudes are in radians.

Note that Chatham Islands conversion does not return correct result.


## Usage


A conversion return a _CoOrdinates_ object and can be done in the following way:

```
// Ycoord
$northing = 4858564;

// Xcoord
$easting = 1237476;

// The following returns an object using the default constants
$tm = TMProjection::CreateDefaultTMProjection();
$coOrdinates = TMProjection::tm_eod($tm, $easting, $northing);

// Latitude
print $coOrdinates->getFormattedLatitude());

// Longitude
print $coOrdinates->getFormattedLongitude());
```




## Running Tests

Tests can be run:

```
vendor/bin/phpunit tests/TMProjectionTest.php
```
