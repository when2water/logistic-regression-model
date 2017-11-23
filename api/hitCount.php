<?php

function pageCount() {
	$counter_name = dirname(__FILE__) . "/APIcalls.txt";
	
	// Check if a text file exists. If not create one and initialize it to zero.
	if (!file_exists($counter_name)) {
	  $f = fopen($counter_name, "w");
	  fwrite($f,"0");
	  fclose($f);
	}
	
	// Read the current value of our counter file
	$f = fopen($counter_name,"r");
	$counterVal = intval(fread($f, filesize($counter_name)));
	fclose($f);
	
	// Increase counter value by one
	$counterVal++;
	$f = fopen($counter_name, "w");
	fwrite($f, $counterVal);
	fclose($f);
}
pageCount();
