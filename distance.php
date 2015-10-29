#!/usr/bin/php
<?php

# http://stackoverflow.com/questions/10053358/measuring-the-distance-between-two-coordinates-in-php
function vincentyGreatCircleDistance( $latitudeFrom, $longitudeFrom, $latitudeTo, $longitudeTo, $earthRadius = 6371000) {
  // convert from degrees to radians
  $latFrom = deg2rad($latitudeFrom);
  $lonFrom = deg2rad($longitudeFrom);
  $latTo = deg2rad($latitudeTo);
  $lonTo = deg2rad($longitudeTo);

  $lonDelta = $lonTo - $lonFrom;
  $a = pow(cos($latTo) * sin($lonDelta), 2) +
    pow(cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta), 2);
  $b = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);

  $angle = atan2(sqrt($a), $b);
  return $angle * $earthRadius;
}

# URL for dump1090
$json = file_get_contents('http://192.168.0.102:8080/dump1090/data.json');
$obj = json_decode($json, true);

# Base station location
$baseLat = 51.1541;
$baseLon = -0.2255;

# Datafile to log json data
$dataFile = '/var/www/html/lunam/distance.json';

# Earth Radius: 6371000 meters, 3959 miles or 3440.227 nautical miles
$earthRad = 3440.227;

$furthest = 0;
$planeData = json_decode(file_get_contents($dataFile), true);
if ( isset($planeData['distance']) ) {
  $furthest = $planeData['distance'];
}
$update = false;

foreach ($obj as $plane) {
  if ( $plane['validposition'] == '1' && $plane['validtrack'] == '1' ) {
    $planeDist = vincentyGreatCircleDistance($baseLat, $baseLon, $plane['lat'], $plane['lon'], $earthRad);
    if ( $planeDist > $furthest ) {
      $furthest = $planeDist;
      $planeData = $plane;
      $planeData['distance'] = $planeDist;
      $update = true;
    }
  }
}

if ( $update ) {
  file_put_contents($dataFile,json_encode($planeData));
}

?>
