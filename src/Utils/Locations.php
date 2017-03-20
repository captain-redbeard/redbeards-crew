<?php
/**
 * @author captain-redbeard
 * @since 20/03/17
 */
namespace Redbeard\Crew\Utils;

class Locations
{
    /**
    * Get the distance between the two locations.
    *
    * @param $latitude_from     - latitude starting
    * @param $longitude_from    - longitude starting
    * @param $latitude_to       - latitude to
    * @param $longitude_to      - longitude to
    * @param $earth_radius      - earth radius, default 6371.009
    *
    * @returns distance
    */
    public static function getDistance($latitude_from, $longitude_from, $latitude_to, $longitude_to, $earth_radius = 6371.009) {
        $latitude_from = deg2rad($latitude_from);
        $longitude_from = deg2rad($longitude_from);
        $latitude_to = deg2rad($latitude_to);
        $longitude_to = deg2rad($longitude_to);
        
        $lat_delta = $latitude_to - $latitude_from;
        $lon_delta = $longitude_to - $longitude_from;
        
        $angle = 2 * asin(
            sqrt(
                pow(sin($lat_delta / 2), 2) +
                cos($latitude_from) *
                cos($latitude_to) *
                pow(sin($lon_delta / 2), 2)
            )
        );
        
        //Return
        return $angle * $earth_radius;
    }
}
