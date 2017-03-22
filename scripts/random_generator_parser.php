<?php
set_time_limit(0);
####################################################################################################
#
# PDF Parser
#
####################################################################################################


####################################################################################################
#
# Initial Functions
#
####################################################################################################
#$db_connection = db_connection();

# Run the main script
// importer($db_connection,'Ultimate_Toolbox.txt');
importer("",'tool_box_clean.txt.txt');
#build_helping_tables();

####################################################################################################
#
# Main Function
#
####################################################################################################
function importer($db_connection,$file) {

	#echo "<pre>";
	$lines = array();
	$titles = array();

	$i = 0;
	$new_category = 0;
	$last_title = '';
	$handle = @fopen($file, "r");
	$cnt = $moo = 0;
	if ($handle) {
		while (!feof($handle)) {
			#if($cnt++ >= 3000) { break; }
			$buffer = trim(fgets($handle, 4096));
			if($buffer == '') { continue; }
			else if(preg_match('/CHAPTER/',$buffer)) { continue; }
			else if(preg_match('/\?\d\?/',$buffer)) { continue; }
			else if(preg_match('/^[T|t]able/',$buffer)) {
				$clean_title = preg_replace('/[^A-Za-z0-9\(\)\:\- ]/',' ',$buffer);
				$clean_title = preg_replace('/(\d+)\s+(\d+)/','$1-$2',$clean_title);
				$clean_title = trim(preg_replace('/[t|T]?able \d+\-\d+\:/','',$clean_title));
				$clean_title = ucwords(trim(preg_replace('/ \d+$/','',$clean_title)));
				if($clean_title == $last_title) { continue; }
				$last_title = $clean_title;
				$new_category++;
				$i = 0;
				$lines[$new_category]['title'] = $clean_title;
			}  else if(!is_numeric($buffer)) {
				
				if(preg_match('/ \d+$/',$buffer)) {
					$lines[$new_category][$i] = trim(preg_replace('/ \d+$/','',$buffer));
					$i++;
				} else {
					if(strpos($buffer,"*") == 0) { continue; }
					if(preg_match('/\?\d+\?/',$buffer)) { 
						$buffer = preg_replace('/(\d+ )?\?\d+\?/','|||',$buffer);
						list($buffer,$other) = explode("|||",$buffer);
					}
					if(empty($lines[$new_category][$i])) {
						$lines[$new_category][$i] = "";
					}
					$lines[$new_category][$i] .= " ". trim($buffer);
				}
				
			}
		}
		fclose($handle);
	}

	#sort($lines);
	#print_r($lines);

	// foreach($lines as $v) {
	// 	$len = count($v);
	// 	print_r($v);
	// 	if(strlen($v[($len-3)]) > 50 || $v[($len-2)] > 50) { 
	// 		echo $v['title']."\n1) ". $v[($len-3)] ."\n2) ". $v[($len-2)] ."\n\n";
	// 	}
	// }

/*	
	$len = count($lines);
	$max_chapter = 0;
	$i = 0;
	while($i < $len) {
		if(!isset($lines[$i])) { $i++; continue; }
		if(count($lines[$i]) < 10) { 
			unset($lines[$i]);
			$i++;
			continue;
		}
		#$lines[$i] = array_slice($lines[$i],0,21);
		#if(count($lines[$i]) > 21) { print_r($lines[$i]); }

		
		$chapters[$i] = $lines[$i]['title'];
		$max_chapter = $lines[$i]['title'];
		#print_r($matches);
		#Array
		#(
		# [0] => 1-1
		# [1] => 1
		# [2] => 1
		#)

		$i++;
	}
*/
	// $chapters = array_unique($chapters);
	echo "<pre>";
	// ksort($lines);
	// print_r($lines);
	
	// sort($lines);
	print_r($lines);
	die();
	
	/*
	$len = count($lines);
	$i = 0;
	while($i++ < $len) {
		$q = 'insert into categories (category,description,parent_category_id,parent_category,date_created) values ("'. $lines[$i]['title'] .'","'. $lines[$i]['title'] .'",2,"Random Generators",now())';
		$res = mysqli_query($db_connection,$q);
		$category_id = mysqli_insert_id($db_connection);
		
		$j = 0;
		$inner_len = count($lines[$i]) - 1;
		while($j < $inner_len) {
			$q = 'insert into random_content (title,content,category_id,parent_category_id,date_created) values ("'. $lines[$i][$j] .'","'. $lines[$i][$j] .'","'. $category_id .'",2,now())';
			mysqli_query($db_connection,$q);
			$j++;
		}
		
		echo "<br /><br /><br />";
	}
	*/
	

	#echo "</pre>";

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
// function db_connection() {

// $host ='127.0.0.1';
// $user = 'root';
// $pass = '';
// $db = 'delphi_dev';

// $db_connection = mysqli_connect($host, $user, $pass, $db);
// return $db_connection;
// }


?>
