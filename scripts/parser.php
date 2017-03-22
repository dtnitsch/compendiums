<?php
set_time_limit(0);
####################################################################################################
#
# PDF Parser
#
####################################################################################################


####################################################################################################
# Initial Functions
####################################################################################################
importer('tool_box_clean.txt.txt');

####################################################################################################
# Main Function
####################################################################################################
function importer($file) {

	$lines = array();

	$handle = fopen($file, "r");
	$clean_title = "";
	if ($handle) {
	    while (($line = fgets($handle)) !== false) {
    		$line = trim($line);
    		// $line = to_iso($line);
    		// $line = iconv('UTF-8', 'ASCII//IGNORE//TRANSLIT', $line);
    		// echo "<br>". $line;
    		// $line = htmlentities($line, ENT_SUBSTITUTE, 'UTF-8');
    		if($line == '') { continue; }
			else if(strstr($line,'CHAPTER')) { continue; }
			else if(preg_match('/^\s*[T|t]able \d+.\d+/',$line)) {
				$pieces = explode(":",$line);
				if(empty($pieces[1])) {
					$pieces[1] = preg_replace('/[t|T]?able \d+.\d+/','',$line);
				}
				array_shift($pieces);
				$clean_title = ucwords(trim(implode(": ",$pieces)));

			}  else {
				$id = (int)$line;
				if($id) {
					$lines[$clean_title][$id] = trim(substr(trim($line),2));
				}

				
			}
		}
		fclose($handle);
	}

	echo "<pre>";

	echo count($lines);
	print_r($lines);
	die();

}

####################################################################################################
#
# Helping Functions
#
####################################################################################################


####################################################################################################
#
# Database Connection Functions
#
####################################################################################################



?>
