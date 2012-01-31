<?php
/**
 * This code is written with one single purpose in mind: provide a winning solution for the Ibuildings TEP contest. 
 * It does NOT adhere to proper Design Patterns, Coding Standards, or anything else that makes code maintainable. 
 * DO NOT COPY/PASTE ANY OF THIS INTO YOUR OWN CODE AND ASSUME IT MUST BE THE BEST WAY BECAUSE I WROTE IT, OR BECAUSE THE CODE WAS FAST
 * This code is released as an education work under a [CC-BY-NC-ND](http://creativecommons.org/licenses/by-nc-nd/3.0/) license.
 * Please please please read the attached readme.md. <hypnotic voice>Do iiiiit!</hypnotic voice>
 */

/* Read file, parse for places with co-ords */
$file = file($argv[1]);
array_shift($file);
define('A',65);
$iCap = A; //Capital A to start with. This saves a lot of memory, and makes calculating later on a *lot* easier
$fullPlaces = $places = $dists = $starts = $ends = array();
foreach($file as $line) {
	$place = str_getcsv($line);
	$chr = chr($iCap);
	$fullPlaces[$chr] = $place;
	$places[$chr] = $chr;
	$iCap++;
}
/* now calculate for distances */
function getdistance($lat1, $lon1, $lat2, $lon2) {
	$dLat = deg2rad($lat2-$lat1);
	$dLon = deg2rad($lon2-$lon1);
	$a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
	$c = 2* atan2(sqrt($a), sqrt(1-$a));
	return 6371 * $c; //6371 = earth mean radius in KM
}
define('LIMIT',$iCap - 65);
for($i=0; $i<LIMIT; $i++){
	$_i=chr($i+A);
	for($j=0; $j<LIMIT; $j++) {
		$_j=chr($j+A);
		$key = $_i .$_j;
		if ($_i == $_j) {
			$dists[$key] = 0;
		} elseif (isset($dists[$_j.$_i])) {
			$dists[$key] = $dists[$_j.$_i];
		} else {
			$lat1 = $fullPlaces[$_i][1];
			$lat2 = $fullPlaces[$_j][1];
			$lon1 = $fullPlaces[$_i][2];
			$lon2 = $fullPlaces[$_j][2];
			$dists[$key] = getdistance($lat1, $lon1, $lat2, $lon2);
		}
	}
	$starts[$_i] = getdistance(51.58701, -0.23029, $fullPlaces[$_i][1], $fullPlaces[$_i][2]);
	$ends[$_i] = getdistance(52.34139, 4.88833, $fullPlaces[$_i][1], $fullPlaces[$_i][2]);
}//dists is now a big matrix with all the distances between points
/* Make a recursive iteration to get the shortest path. Basically bruteforce, with a lot of exceptions*/
function make_path_with_limiter($now, $options, $count = 0) {
	global $shortest,$shortestPath,$dists,$ends;
	foreach($options as $option) {
		$newstring = $now .$option;
		$newstrlen = strlen($newstring);
		$addCount = $dists[$now[$newstrlen -2] . $option];
		if ($count + $addCount + $ends[$option]> $shortest) continue;
		if ($newstrlen == LIMIT) {
			$sum = $count + $ends[$option];
			if ($sum < $shortest) {
				$shortest = $sum;
				$shortestPath = $newstring;
			}
		} else {
			$realoptions = $options;
			unset($realoptions[$option]);
			make_path_with_limiter($newstring, $realoptions, ($count + $addCount));
		}
	}
}
/* Return the key of the lowest value */
function lowest($struct, $exclude='') {
	$return = '';
	if ($exclude!='')$struct = array_diff_key($struct, array_flip(str_split($exclude)));
	$lowest = reset($struct); //we don't need to reset the array, but this is a cheap way to get the first value of the struct, without unshifting it
	foreach($struct as $k=>$v) {
		if ($v <= $lowest) {
			$lowest = $v;
			$return = $k;
		}
	}
	return $return;
}
/* create the smallest possible route, using an improved bi-directional NNA approach*/
function ibnna() {
	global $dists,$starts,$ends,$places,$shortPaths;
	$quickStart = lowest($starts);
	$quickEnd = lowest($ends);
	if ($quickStart == $quickEnd) {
		if ($starts[$quickStart] < $ends[$quickEnd]) {
			$quickEnd = lowest($ends, $quickStart);
		} else {
			$quickStart = lowest($starts, $quickEnd);
		}
	}
	$pathlength = $starts[$quickStart] + $ends[$quickEnd];
	$length = 2; // we already have the start and the end. They come from different arrays, so we had to calculate those first
	$tmpPlaces = $places;
	unset($tmpPlaces[$quickStart],$tmpPlaces[$quickEnd]);
	while($length < LIMIT) {
		$values = array();
		$strStart = substr($quickStart, -1,1);
		$strEnd = $quickEnd[0];
		foreach($tmpPlaces as $char) {
			$values[$strStart . $char] = $dists[$strStart . $char];
			$values[$strEnd . $char] = $dists[$strEnd . $char]; 
		}
		$lowestStart = lowest($values, $quickStart . $quickEnd);
		
		$next = $lowestStart[1];
		if ($dists[$strStart . $next] < $dists[$strEnd . $next]) { //point is closest to the current start string, add it there
			$quickStart = $quickStart . $next;
		} else {
			$quickEnd = $next . $quickEnd;
		}
		$length++;
		unset($tmpPlaces[$next]);
	}
	$path = $quickStart . $quickEnd;
	$prev = $path[0];
	$pieces=str_split($path);
	foreach($pieces as $piece) {
		$pathlength += $dists[$prev.$piece];
		$prev = $piece;
	}
	$shortpieces = array_reverse($pieces);
	return array($pathlength, $path);
}
/* made all our code, now go for our commands */
list($shortest, $shortestPath) = ibnna();
foreach($places as $place) {
		$realplaces = $places;
		unset($realplaces[$place]);
		make_path_with_limiter($place, $realplaces, $starts[$place]);
}
foreach(str_split($shortestPath) as $point) {
	echo $fullPlaces[$point][0] . "\n";
}
