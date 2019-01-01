<?php

ini_set("safe_mode", 0);
ini_set("precision", 15);

if (! isset($zip)) {
	$zip = "10007"; // New York
}

// Get predicted data from getPred.php for the current zip code
ob_start(); // stop any output from that file
$predictionData = include_once "getPred.php";
ob_end_clean();
$fData = $predictionData[1];
$fTimes = $predictionData[0];
// var_dump($predictionData);

// Read any stored historical data into correct format
$fName = "/data" . "/$zip.txt";
if (!file_exists($fName)) {
	$zipReg = fopen("/data" . "/zips.txt", "a+");
	fwrite($zipReg, "$zip\n");
	fclose($zipReg);
}
$f = fopen($fName, "r");
rewind($f);
$raw = fread($f, filesize($fName));
fclose($f);
$raw = explode("\n", $raw);
$pData = Array();
foreach ($raw as $line) { // Formating
	if ($line) {
		$line = explode(",", $line);
		$line[0] = new DateTime($line[0]);
		$line[1] = (float) $line[1];
		$line[2] = (float) $line[2];
		$line[3] = (float) $line[3];
		$line[4] = (float) $line[4];
		$pData[] = $line;
	}
}

// Check for today's historical data
$tDataGot = False;
$today = new DateTime(date('Y-m-d'));
if ($pData[count($pData) - 1][0] == $today) { // Only the latest data
	$pData = array_slice($pData, -5, 4);
	$tDataGot = True;
} else {
	$pData = array_slice($pData, -4);
}
// var_dump($tDataGot);
// var_dump($pData);

// Make sure the data isn't missing anything
$missingData = False;
for ($i = 3; $i >= 0; $i--) {
	if (! isset($pData[$i][0])) {
		$missingData = True;
		break;
	}
	$add = "+" . (string) (4 - $i) . " day";
	$dD = $pData[$i][0];
	$dD->modify($add);
	if ($dD->format("Y-m-d") !== $today->format("Y-m-d")) {
		$missingData = True;
		break;
	}
}

// Write new historical data to file
if (!$tDataGot) {
	$f = fopen($fName, "a+");
	$tData = Array();
	$tData[] = (string) date('Y-m-d');
	$tData[] = (string) $fData[0][0];
	$tData[] = (string) $fData[1][0];
	$tData[] = (string) $fData[2][0];
	$tData[] = (string) $fData[3][0];
	$tData = (string) implode(",", $tData);
	fwrite($f, $tData);
	fwrite($f, "\n");
	fclose($f);
}

if ($missingData === False) {
	$highTemp = (float) $fData[0][0];
	
	$high5day = $pData[0][1] + $pData[1][1] + $pData[2][1] + $pData[3][1] + $fData[0][0];
	$high5day = (float) ($high5day / 5);
	
	$high3pred = array_slice($fData[0], 1, 3);
	$high3pred = (float) array_avg($high3pred);
	
	$lowTemp = (float) $fData[1][0];
	
	$low5day = $pData[0][2] + $pData[1][2] + $pData[2][2] + $pData[3][2] + $fData[1][0];
	$low5day = (float) ($low5day / 5);
	
	$low3pred = array_slice($fData[1], 1, 3);
	$low3pred = (float) array_avg($low3pred);
	
	$precip = (float) $fData[3][0];
	
	$precip3past = (float) ($pData[2][4] + $pData[3][4] + $fData[3][0]);
	
	$precip3pred = array_slice($fData[3], 0, 3);
	$precip3pred = (float) array_sum($precip3pred);
	
	$cloudC = $fData[2][0];
	$cloudC = (float) ($cloudC / 10);
	
	// old theta
	/*$sign = -  44.340875136336000
			-   1.829931009086090 * $highTemp
			+   2.002613900756610 * $high5day
			-   0.643542476739116 * $high3pred
			+   1.521056540776330 * $lowTemp
			-   1.090647707884480 * $low5day
			+   1.106389037294810 * $low3pred
			- 398.636217073200000 * $precip
			+  11.052971259671500 * $precip3past
			+   4.995838538586970 * $precip3pred
			-   1.870814228990030 * $cloudC
			;*/
	
	// new theta
	$sign = -  26.50580666926859000
	        +   0.55592999302254630 * $highTemp
	        -   0.61615654695684840 * $high5day
	        +   0.09718923661623589 * $high3pred
	        -   0.34864564869201280 * $lowTemp
	        +   0.69499568863150950 * $low5day
	        +   0.25597594423003430 * $low3pred
	        -  10.79337772406538000 * $precip
	        - 174.29129749909470000 * $precip3past
	        - 122.81421631645490000 * $precip3pred
	        -   1.35363188452810300 * $cloudC
	        ;
	
	
	// Some print statements for testing
	/*print_r($fTimes);
	print_r($fData);
	print_r($pData);
	echo "\n";
	echo "\n";
	var_dump($highTemp);
	echo "\n";
	var_dump($high5day);
	echo "\n";
	var_dump($high3pred);
	echo "\n";
	var_dump($lowTemp);
	echo "\n";
	var_dump($low5day);
	echo "\n";
	var_dump($low3pred);
	echo "\n";
	var_dump($precip);
	echo "\n";
	var_dump($precip3past);
	echo "\n";
	var_dump($precip3pred);
	echo "\n";
	var_dump($cloudC);
	echo "\n";
	var_dump($sign);
	echo "\n";*/
}
else {
	// Manual algorithm for first few days without all historical data
	define('ALGO_MANUAL_MAX_PRECIC', 0.05);
	define('ALGO_MANUAL_MIN_AVG_TEMP', 60);
	
	// $fData[3][0] = today's precip
	// $fData[0,1][0] = today's high/low temp
	
	print_r($fData);
	$sign = -1;
	if ($fData[3][0] + $fData[3][1] <= ALGO_MANUAL_MAX_PRECIC) {
		if ($fData[0][0] + $fData[1][0] >= 2 * ALGO_MANUAL_MIN_AVG_TEMP) {
			$sign = 1;
		}
	}
}

/* Sample cURL system call command for weather data
$token = "MrMdNxjhVdRUqmBReCCYpqzYVBIlYhaV";
$url = "http://www.ncdc.noaa.gov/cdo-web/api/v2/data?datasetid=GHCND&startdate=2014-06-05&enddate=2014-06-15&locationid=ZIP:10007";
// $url = "http://www.ncdc.noaa.gov/cdo-web/api/v2/data?datasetid=GHCND&locationid=FIPS:02&startdate=2010-05-01&enddate=2010-05-31";
$query = "curl -H \"token:$token\" \"$url\"";
echo $query;
echo system($query);
 */

print $sign;
return $sign;

?>
