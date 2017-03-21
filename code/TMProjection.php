<?php

/**
 * TMProjection
 */

require_once "CoOrdinates.php";

class TMProjection
{

    const PI = 3.1415926535898;
    const TWOPI = (2.0 * self::PI);
    const RAD2DEG = (180 / self::PI);

    const NZTM_A = 6378137;
    const NZTM_RF = 298.257222101;
    const NZTM_CM = 173.0;
    const NZTM_OLAT = 0.0;
    const NZTM_SF = 0.9996;
    const NZTM_FE = 1600000.0;
    const NZTM_FN = 10000000.0;

    /* Central meridian */
    public $meridian;

    /* Scale factor */
    public $scalef;

    /* Origin latitude */
    public $orglat;

    /* False easting */
    public $falsee;

    /* False northing */
    public $falsen;

    /* Unit to metre conversion */
    public $utom;

    /* Ellipsoid parameters */
    public $a;
    public $rf;
    public $f;
    public $e2;
    public $ep2;

    /* Intermediate calculation */
    public $om;


    /**
     * TMProjection constructor.
     *      Initiallize the TM structure
     *
     * @param $a
     * @param $rf
     * @param $cm
     * @param $sf
     * @param $lto
     * @param $fe
     * @param $fn
     * @param $utom
     */
    public function TMProjection($a, $rf, $cm, $sf, $lto, $fe, $fn, $utom)
    {
        $this->meridian = $cm;
        $this->scalef = $sf;
        $this->orglat = $lto;
        $this->falsee = $fe;
        $this->falsen = $fn;
        $this->utom = $utom;

        if ($rf != 0.0) {
            $f = 1.0 / $rf;
        } else {
            $f = 0.0;
        }

        $this->a = $a;
        $this->rf = $rf;
        $this->f = $f;
        $this->e2 = 2.0 * $f - $f * $f;
        $this->ep2 = $this->e2 / (1.0 - $this->e2);

        $this->om = self::meridian_arc($this, $this->orglat);

        return $this;
    }


    /**
     * @return TMProjection
     */
    public static function CreateDefaultTMProjection()
    {
        return new TMProjection(
            TMProjection::NZTM_A,
            TMProjection::NZTM_RF,
            TMProjection::NZTM_CM / TMProjection::RAD2DEG,
            TMProjection::NZTM_SF,
            TMProjection::NZTM_OLAT / TMProjection::RAD2DEG,
            TMProjection::NZTM_FE,
            TMProjection::NZTM_FN,
            1.0
        );
    }


    /**
     * meridian_arc
     *
     *  Returns the length of meridional arc (Helmert formula)
     *  Method based on Redfearn's formulation as expressed in GDA technical
     *  manual at http://www.anzlic.org.au/icsm/gdatm/index.html
     *
     *  Parameters are
     *    projection
     *    latitude (radians)
     *
     *  Return value is the arc length in metres
     *
     *
     * @param TMProjection $tm
     * @param $lt
     * @return mixed
     */
    public static function meridian_arc(TMProjection $tm, $lt)
    {
        $e2 = $tm->e2;
        $a = $tm->a;

        $e4 = $e2 * $e2;
        $e6 = $e4 * $e2;

        $A0 = 1 - ($e2 / 4.0) - (3.0 * $e4 / 64.0) - (5.0 * $e6 / 256.0);
        $A2 = (3.0 / 8.0) * ($e2 + $e4 / 4.0 + 15.0 * $e6 / 128.0);
        $A4 = (15.0 / 256.0) * ($e4 + 3.0 * $e6 / 4.0);
        $A6 = 35.0 * $e6 / 3072.0;

        return $a * ($A0 * $lt - $A2 * sin(2 * $lt) + $A4 * sin(4 * $lt) - $A6 * sin(6 * $lt));
    }


    /**
     *   foot_point_lat
     *
     *   Calculates the foot point latitude from the meridional arc
     *   Method based on Redfearn's formulation as expressed in GDA technical
     *   manual at http://www.anzlic.org.au/icsm/gdatm/index.html
     *
     *   Takes parameters
     *      tm definition (for scale factor)
     *      meridional arc (metres)
     *
     *   Returns the foot point latitude (radians)                           */ /*
     *      * @param TMProjection $
     * @param $m
     * @return float|int
     */
    public static function foot_point_lat(TMProjection $tm, $m)
    {
        $f = $tm->f;
        $a = $tm->a;

        $n = $f / (2.0 - $f);
        $n2 = $n * $n;
        $n3 = $n2 * $n;
        $n4 = $n2 * $n2;

        $g = $a * (1.0 - $n) * (1.0 - $n2) * (1 + 9.0 * $n2 / 4.0 + 225.0 * $n4 / 64.0);
        $sig = $m / $g;

        $phio = $sig + (3.0 * $n / 2.0 - 27.0 * $n3 / 32.0) * sin(2.0 * $sig)
            + (21.0 * $n2 / 16.0 - 55.0 * $n4 / 32.0) * sin(4.0 * $sig)
            + (151.0 * $n3 / 96.0) * sin(6.0 * $sig)
            + (1097.0 * $n4 / 512.0) * sin(8.0 * $sig);

        return $phio;
    }

    /**
     * tmgeod
     *
     *   Routine to convert from Tranverse Mercator to latitude and longitude.
     *   Method based on Redfearn's formulation as expressed in GDA technical
     *   manual at http://www.anzlic.org.au/icsm/gdatm/index.html
     *
     *   Takes parameters
     *      input easting (metres)
     *      input northing (metres)
     *      output latitude (radians)
     *      output longitude (radians)
     */
    public static function tm_eod(TMProjection $tm, $ce, $cn)
    {

        $coOrinate = new CoOrdinates();

        $fn = $tm->falsen;
        $fe = $tm->falsee;
        $sf = $tm->scalef;
        $e2 = $tm->e2;
        $a = $tm->a;
        $cm = $tm->meridian;
        $om = $tm->om;
        $utom = $tm->utom;

        $cn1 = ($cn - $fn) * $utom / $sf + $om;
        $fphi = self::foot_point_lat($tm, $cn1);
        $slt = sin($fphi);
        $clt = cos($fphi);

        $eslt = (1.0 - $e2 * $slt * $slt);
        $eta = $a / sqrt($eslt);
        $rho = $eta * (1.0 - $e2) / $eslt;
        $psi = $eta / $rho;

        $E = ($ce - $fe) * $utom;
        $x = $E / ($eta * $sf);
        $x2 = $x * $x;


        $t = $slt / $clt;
        $t2 = $t * $t;
        $t4 = $t2 * $t2;

        $trm1 = 1.0 / 2.0;

        $trm2 = ((-4.0 * $psi + 9.0 * (1 - $t2)) * $psi + 12.0 * $t2) / 24.0;

        $trm3 = ((((8.0 * (11.0 - 24.0 * $t2) * $psi - 12.0 * (21.0 - 71.0 * $t2)) * $psi + 15.0 * ((15.0 * $t2 - 98.0) * $t2 + 15)) * $psi + 180.0 * ((-3.0 * $t2 + 5.0) * $t2)) * $psi + 360.0 * $t4) / 720.0;

        $trm4 = (((1575.0 * $t2 + 4095.0) * $t2 + 3633.0) * $t2 + 1385.0) / 40320.0;

        $coOrinate->latitude = $fphi + ($t * $x * $E / ($sf * $rho)) * ((($trm4 * $x2 - $trm3) * $x2 + $trm2) * $x2 - $trm1);

        $trm1 = 1.0;

        $trm2 = ($psi + 2.0 * $t2) / 6.0;

        $trm3 = (((-4.0 * (1.0 - 6.0 * $t2) * $psi + (9.0 - 68.0 * $t2)) * $psi + 72.0 * $t2) * $psi + 24.0 * $t4) / 120.0;

        $trm4 = (((720.0 * $t2 + 1320.0) * $t2 + 662.0) * $t2 + 61.0) / 5040.0;

        $coOrinate->longitude = $cm - ($x / $clt) * ((($trm4 * $x2 - $trm3) * $x2 + $trm2) * $x2 - $trm1);

        return $coOrinate;
    }

}

