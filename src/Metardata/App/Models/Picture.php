<?php

namespace Metardata\App\Models;

class Picture {

    protected $id;

    protected $path;

    protected $metadata;

    function __construct($picturePath) {
        $this->path = $picturePath;
        $pathParts = explode('picture', $this->path);
        $this->id = explode('.', $pathParts[1])[0];
    }

    /**
    * Guetters
    */
    public function getMeta() {
        return $this->metadata;
    }

    public function getPath() {
        return $this->path;
    }

    public function getId() {
        return $this->id;
    }

    /**
    * Setters
    */
    public function setMeta($meta) {
        $this->metadata = $meta;
    }

    public function getLatitude() {
        $lat = null;
        if(key_exists('GPS', $this->metadata)) {
            $lat = $this->metadata["GPS"]["GPSLatitude"];
            $ref = $this->metadata["GPS"]["GPSLatitudeRef"];
            // decimal conversion
            $lat = $this->DMS2Decimal($lat, $ref);
        }
        return $lat;
    }

    public function getLongitude() {
        $long = null;
        if(key_exists('GPS', $this->metadata)) {
            $long = $this->metadata["GPS"]["GPSLongitude"];
            $ref = $this->metadata["GPS"]["GPSLongitudeRef"];
            // decimal conversion
            $long = $this->DMS2Decimal($long, $ref);
        }
        return $long;
    }

    public function split($gpsStr) {
        $parts = explode("deg", $gpsStr);
        $deg = $parts[0];
        $minute = explode("'", $parts[1])[0];
        $sec = explode("'", $parts[1])[1];

        $sec = str_replace('"', "", $sec);
        $deg = trim($deg);
        $minute = trim($minute);
        $sec = trim($sec);

        return [$deg, $minute, $sec];
    }

    public function DMS2Decimal($gpsStr, $ref) {
        $decimals = $this->split($gpsStr);
        $degrees = $decimals[0];
        $minutes = $decimals[1];
        $seconds = $decimals[2];
        $direction = $ref;

         //converts DMS coordinates to decimal
         //returns false on bad inputs, decimal on success

         //direction must be n, s, e or w, case-insensitive
         $d = strtolower($direction);
         $ok = array('north', 'south', 'east', 'west');

         //degrees must be integer between 0 and 180
         if(!is_numeric($degrees) || $degrees < 0 || $degrees > 180) {
            $decimal = false;
         }
         //minutes must be integer or float between 0 and 59
         elseif(!is_numeric($minutes) || $minutes < 0 || $minutes >= 60) {
            $decimal = false;
         }
         //seconds must be integer or float between 0 and 59
         elseif(!is_numeric($seconds) || $seconds < 0 || $seconds >= 60) {
            $decimal = false;
         }
         elseif(!in_array($d, $ok)) {
            $decimal = false;
         }
         else {
            //inputs clean, calculate
            $decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

            //reverse for south or west coordinates; north is assumed
            if($d == 'south' || $d == 'west') {
               $decimal *= -1;
            }
         }
         return $decimal;
      }

    public function setId( $id ){
        $this->id = $id;
    }
}

 ?>
