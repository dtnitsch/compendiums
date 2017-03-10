<?php
########################################################################
#
#	Error Handling
#
########################################################################

function error_handler_setup($debug,$log_level='E_ALL') {

	$error_handler['E_USER_ALL']	= E_USER_NOTICE | E_USER_WARNING | E_USER_ERROR;
	$error_handler['E_NOTICE_ALL'] = E_NOTICE | E_USER_NOTICE;
	$error_handler['E_WARNING_ALL'] = E_WARNING | E_USER_WARNING | E_CORE_WARNING | E_COMPILE_WARNING;
	$error_handler['E_ERROR_ALL'] = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR | E_USER_ERROR;
	$error_handler['E_NOTICE_NONE'] = E_ALL & ~$error_handler['E_NOTICE_ALL'];
	$error_handler['E_DEBUG'] = 0x10000000;

	$error_handler['E_ALL'] = $error_handler['E_ERROR_ALL'] | $error_handler['E_WARNING_ALL'] | $error_handler['E_NOTICE_ALL'] | $error_handler['E_DEBUG'] | E_STRICT;

	$debug_options =& $GLOBALS["debug_options"];
	$debug_info =& $GLOBALS["debug_information"];

	$debug_info['total_page_time'] = microtime(true);
	$debug_info['total_query_time'] = 0;
	$debug_info['total_queries'] = 0;
	$debug_info['memory_usage'] = memory_get_usage();
	$debug_info['memory_usage_peak'] = memory_get_peak_usage();

	# If they want an error email
	if(!empty($debug_options['send_debug_mail'])) {
		$email = '';
		if(!empty($debug_options['debug_email'])) {
			$email = $debug_options['debug_email'];
		} else if(!empty($GLOBALS['project_info']['default_email'])) {
			$email = $GLOBALS['project_info']['default_email'];
		}
		$debug_options['debug_email'] = $email;
		$debug_options['log_level'] = $error_handler['E_ERROR_ALL'];
		$debug_options['mail_level'] = $error_handler['E_ERROR_ALL'];
	}

	# If the above is disabled, then don't set the rest of the values
	if(!empty($debug_options['enabled'])) {
		$debug_options['log_level'] = $error_handler[$log_level];
	}

	if(!empty($debug_options['console_level'])) {
		error_reporting($debug_options['console_level']);
	}
	set_error_handler('error_handler');
	register_shutdown_function('fatal_error_shutdown');

	$GLOBALS['error_handling']['all_errors'] = array();
	$GLOBALS['error_handling']['error_counter'] = array(
		'E'=>0
		,'W'=>0
		,'N'=>0
		,'D'=>0
		,'U'=>0
		,'S'=>0
	);
}

// fatal errors
function fatal_error_shutdown() {

	$show_debug = false;

	$last_error = error_get_last();
	if ($last_error['type'] === E_ERROR) {
		error_handler(E_ERROR, $last_error['message'], $last_error['file'], $last_error['line']);
		if(is_file($GLOBALS["root_path"] ."templates/error.template.php")) {
			include($GLOBALS["root_path"] ."templates/error.template.php");
		}
		$show_debug = true;
	}


	foreach($GLOBALS['error_handling']['all_errors'] as $row) {
		if($row['Level'] == "ERROR") {
			$show_debug = true;
			break;
		}
	}

	if($show_debug && empty($GLOBALS["debug_information"]['has_shown'])) {
		// show_debug();
	}

}

function error_handler($errno, $errstr='', $errfile='', $errline='') {

	$debug_options =& $GLOBALS["debug_options"];
	if($debug_options['enabled'] == 0 && $debug_options['send_debug_mail'] == 0) { return; }

	# Create a reference to all errors for a faster lookup and add.
	$all_errors =& $GLOBALS['error_handling']['all_errors'];

	# If error has been supressed with an @
    if(error_reporting() == 0) {
		$GLOBALS['error_handling']['error_counter']['S']++;
        return;	}

	// This is required to be on to correctly find where files were called from
	$backtrace = get_backtrace();
	# Create a link to PHP.net for more informaiton
	if(preg_match('/[\w]*\(\)/',$errstr,$matches)) {
		preg_match('/[\w]*/',$matches[0],$stripped_match);
		$message = str_replace($matches[0], "<a target='_blank' href='http://php.net/".$stripped_match[0]."'>".$stripped_match[0]."()</a>", $errstr);
		if(!empty($backtrace)) {
			foreach($backtrace as $v) {
				if(strpos($v['file'],'trunk/modules/') !== false) { 
					$errfile = $v['file'];
					$errline = $v['line'];
					break;
				}
			}
		}
	} else {
		$message = $errstr;
	}

	$new_error = array(
		'Number' => $errno,
		'Message' => $message,
		'File' => $errfile,
		'Line' => $errline,
		'Level' => error_type($errno)
	);

	$match = false;	
	# Check this error against the most recent error
	$total_errors = count($all_errors);
	if($total_errors > 0) {
		$i = 0;
		while($i < $total_errors) {

			if( $new_error['Number']  == $all_errors[$i]['Number']  && 
				$new_error['Message'] == $all_errors[$i]['Message'] && 
				$new_error['File']    == $all_errors[$i]['File']    && 
				$new_error['Line']    == $all_errors[$i]['Line']
			) {
				$match = true;
				break;
			}
			$i++;
		}
	}

	# If this error is the same as the previous, increment the previous
	if($match) {
		if(isset($all_errors[$i]['count'])) {
			$all_errors[$i]['count']++;
		} else {
			$all_errors[$i]['count'] = 2;
		}
		return;
	}

	if(!empty($debug_options['enabled_backtrace'])) { $new_error['Backtrace'] = display_backtrace($backtrace); }
	$all_errors[] = $new_error;
}


function error_type($errno) {

	# Create a reference to all error counts for a faster lookup and add.
	$error_counter =& $GLOBALS['error_handling']['error_counter'];
	
	if($errno == 'E_DEBUG') { 
		$error_counter['D']++;
		return "DEBUG";
	}

	$error_types = array (
		E_ERROR            		=> 'ERROR',
		E_WARNING        		=> 'WARNING',
		E_PARSE          		=> 'PARSING ERROR',
		E_NOTICE         		=> 'NOTICE',
		E_CORE_ERROR     		=> 'CORE ERROR',
		E_CORE_WARNING   		=> 'CORE WARNING',
		E_COMPILE_ERROR  		=> 'COMPILE ERROR',
		E_COMPILE_WARNING 		=> 'COMPILE WARNING',
		E_USER_ERROR     		=> 'USER ERROR',
		E_USER_WARNING   		=> 'USER WARNING',
		E_USER_NOTICE    		=> 'USER NOTICE',
		E_STRICT         		=> 'STRICT NOTICE'
		/*,E_RECOVERABLE_ERROR  	=> 'RECOVERABLE ERROR'*/
	);

    # Create error message
    if (array_key_exists($errno, $error_types)) {
		if(strstr($error_types[$errno],'ERROR')) {
			$error_counter['E']++;
		} else if(strstr($error_types[$errno],'WARNING')) {
			$error_counter['W']++;
		} else {
			$error_counter['N']++;
		}
        return $error_types[$errno];
    } else {
		$error_counter['U']++;
        return 'CAUGHT EXCEPTION';
    }
}

function _error_debug($errstr, $var='', $errline='', $errfile='', $errno="E_DEBUG") {

	$debug_options =& $GLOBALS["debug_options"];
	if($debug_options['enabled'] == 0 && $debug_options['send_debug_mail'] == 0) { return; }
	
	# Create a reference to all errors for a faster lookup and add.
	$all_errors =& $GLOBALS['error_handling']['all_errors'];

	$errcontext = array($errstr => $var);

	$backtrace = get_backtrace();	

	$error_count = 0;
	// if(strpos($backtrace[0]['file'],'LazyDB.php') !== false) { array_shift($backtrace); }

	if(strpos($backtrace[$error_count]["file"],"library/databases") !== false) {
		foreach($backtrace as $row) {
			if(strpos($row["file"],"library/databases") === false) {
				break;
			}
			$error_count++;
		}

	} else if(strpos($backtrace[0]["file"],"library/post_functions") !== false) {
		$error_count = 1;

	} else if(strpos($backtrace[0]["file"],"library/library.php") !== false) {
		foreach($backtrace as $row) {
			if(strpos($row["file"],"library/library.php") === false) {
				break;
			}
			$error_count++;
		}

	// } else if(strpos($backtrace[0]["file"],"library/error_handler.php") !== false) {
	// 	$error_count = 1;
	}
	// else if(strpos($backtrace[0]['file'],'trunk/library/error_handler') !== false) { $error_count = 0; }
	// else if(strpos($backtrace[0]['file'],'trunk/library/') !== false) { $error_count = 0; }
	// else if(strpos($backtrace[0]['file'],'trunk/public/index.php') !== false) { $error_count = 0; }
	// else if(strpos($backtrace[0]['file'],'ajax') !== false) { $error_count = 0; }
	// else { $error_count = count($backtrace) - 2; }
	

	// if(strpos($errstr,'Invalid argument supplied') !== false) {
	// 	echo "errstr: ". $errstr ." ... errline: ". $errline ." ... errfile: ". $errfile;
	// 	echo "Error Count: ". $error_count ."<br>";
	// 	die();
	// }

	if($errline == '') { $errline = $backtrace[$error_count]['line']; }
	if($errfile == '') { $errfile = $backtrace[$error_count]['file']; }

	// if(empty($backtrace[$error_count])) {
	// 	echo "<pre>";
	// 	print_r($backtrace);
	// 	echo "</pre>";
	// 	echo "ERRLINE: ". $errline ." -- ERRFILE: ". $errfile ." -- ERR_COUNT: ". $error_count ."<br>";
	// }

	$new_error = array(
		'Number' => $errno,
		'Message' => $errstr,
		'File' => $errfile,
		'Line' => $errline,
		'ContextCode' => $var,
		'Level' => error_type($errno)
	);
	if(!empty($debug_options['enabled_backtrace'])) { $new_error['Backtrace'] = display_backtrace($backtrace); }

	$all_errors[] = $new_error;
}

function convert_bytes($memory_usage) {
    if ($memory_usage < 1024) {
        # Do nothing, we are good
    } elseif ($memory_usage < 1048576) {
        $memory_usage = number_format($memory_usage/1024,2) .'KB'; 
    } else {
        $memory_usage = number_format($memory_usage/1048576,2) .'MB';
    }
    return $memory_usage;
}

function ajax_debug() {
	$debug_options =& $GLOBALS["debug_options"];
	$debug_info =& $GLOBALS["debug_information"];

	$page_load_time = microtime(true) - $debug_info['total_page_time'];
	$memory_usage = convert_bytes(memory_get_peak_usage() - $debug_info['memory_usage']);

	if(!empty($_SESSION)) { _error_debug('Sessions', $_SESSION, __LINE__, __FILE__); }
	if(!empty($_POST)) { _error_debug('POST Variables', $_POST, __LINE__, __FILE__); }
	if(!empty($_GET)) { _error_debug('GET Variables', $_GET, __LINE__, __FILE__); }
	if(!empty($_SERVER)) { _error_debug('Server', $_SERVER, __LINE__, __FILE__); }
	if(!empty($_COOKIE)) { _error_debug('Cookies', $_COOKIE, __LINE__, __FILE__); }

	$error_counter =& $GLOBALS['error_handling']['error_counter'];
	$short_errors = "";
	if(!empty($error_counter)) {
		foreach($error_counter as $k => $v) {
			if($v > 0) {
				$short_errors .= $k. ":". $v .", ";
			}
		}
		$short_errors = substr($short_errors,0,-2);
	}

	$output = array(
		'page_load_time' => $page_load_time
		,'memory_usage' => $memory_usage
		,'error_counter' => $short_errors
		,'total_queries' => (!empty($debug_info['total_queries']) ? $debug_info['total_queries'] : 0)
		,'total_query_time' => (!empty($debug_info['total_query_time']) ? $debug_info['total_query_time'] : 0)
		,'error_css' => array("ERROR"=>"error_debug_red","WARNING"=>"error_debug_orange","NOTICE"=>"error_debug_yellow","DEBUG"=>"error_debug_gray")
		,'all_errors' => $GLOBALS['error_handling']['all_errors']
		,'unique' => md5(microtime()+mt_rand())
	);

	return $output;
}

function show_debug() {

	$debug_options =& $GLOBALS["debug_options"];
	$debug_info =& $GLOBALS["debug_information"];
	$GLOBALS["debug_information"]['has_shown'] = 1;

	if($debug_options['enabled'] == 0 && $debug_options['send_debug_mail'] == 0) { return; }

	$page_load_time = microtime(true) - $debug_info['total_page_time'];
	$memory_usage = convert_bytes(memory_get_peak_usage() - $debug_info['memory_usage']);

	# Create a reference to all errors for a faster lookup and add.
	$all_errors =& $GLOBALS['error_handling']['all_errors'];
	# Create a reference to all error counts for a faster lookup and add.
	$error_counter =& $GLOBALS['error_handling']['error_counter'];

	if(!empty($_SESSION)) { _error_debug('Sessions', $_SESSION, __LINE__, __FILE__); }
	if(!empty($_POST)) { _error_debug('POST Variables', $_POST, __LINE__, __FILE__); }
	if(!empty($_GET)) { _error_debug('GET Variables', $_GET, __LINE__, __FILE__); }
	if(!empty($_SERVER)) { _error_debug('Server', $_SERVER, __LINE__, __FILE__); }
	if(!empty($_COOKIE)) { _error_debug('Cookies', $_COOKIE, __LINE__, __FILE__); }

	$err_count = count($all_errors);
	if($err_count) {
		$short_errors = "";
		$send_email = 0;
		foreach($error_counter as $k => $v) {
			if($v > 0) {
				$short_errors .= $k. ":". $v .", ";
				if($k == 'E') { $send_email = 1; }
			}
		}

	# Set up compression to load this information nice and fast
	ob_start();
?>
	<div class='error_debug_box'>
	<div class='error_debug_title' onclick='_showhide_debug();'>
		<strong>Debug Information</strong>: (<?php echo substr($short_errors,0,-2); ?>)
		-- <strong>Page Load Time</strong>: <?php printf("%.4f",$page_load_time); ?>
		<?php if($debug_options['enabled'] == 1) { echo " -- <strong>Query Load Times</strong> (".  $debug_info['total_queries'] ." queries) "; } ?> <?php printf("%.4f",$debug_info['total_query_time']); ?>
		<?php echo " -- <strong>Total Memory Usage</strong>: ". $memory_usage; ?></div>
	<table id='debuginfo' class='error_debug_table'>
<?php
		$i = 0;
		$err_colors = array("ERROR"=>"error_debug_red","WARNING"=>"error_debug_orange","NOTICE"=>"error_debug_yellow","DEBUG"=>"error_debug_gray");
		$enable_backtrace = (!empty($debug_options['enabled_backtrace']) ? 1 : 0);
		while($i < $err_count) {
			if(is_array($all_errors[$i]['ContextCode'])) {
				$context = "";
				$debug_counter = 1;
				foreach($all_errors[$i]['ContextCode'] as $k => $v) {
					$context .= "<div>". $debug_counter++ .": <strong>$k</strong>: ". (is_array($v) || strstr($k,'Query') ? DUMP($v,1) : $v) ."</div>";
				}
			} else {
				$context = $all_errors[$i]['ContextCode'];
			}
	$output = "
	<tr>
    	<td class='error_type ". $err_colors[$all_errors[$i]['Level']] ."'>".$all_errors[$i]['Level']."</td>
		";
		if($enable_backtrace) {
			$output .= "
		<td class='show_more' id='backtrace_$i'><a href='javascript:_showhide_backtrace($i)'>Backtrace</a></td>";
		}
		$output .= "
    	<td class='show_more' id='show_more_$i'>". ($context != '' ? "<a href='javascript:_showhide_context($i)'>Show More</a>" : '') ."</td>
   	 	<td class='error_details'><span class='undln'>". $all_errors[$i]['Message'] . ($all_errors[$i]['count'] > 0 ? ' <span class="error_debug_redtext">('.$all_errors[$i]['count'].'x times)</span>' : '') ."</span><span class='page_line'><strong>File</strong>: ".$all_errors[$i]['File']." - <strong>Line</strong>: ". $all_errors[$i]['Line'] ."</span></td>
	</tr>
	";
		$colspan = ($enable_backtrace ? 4 : 3);
		if(!empty($context)) { 
			$output .= "
	<tr>
		<td id='display_context_$i' class='hide_context' colspan='". $colspan ."'>". $context ."</td>
	</tr>
	";
		}
		if($enable_backtrace) { 
			$output .= "
	<tr>
		<td colspan='". $colspan ."' class='hide_backtrace' id='display_backtrace_$i'>". $all_errors[$i]['Backtrace'] ."</td>
    </tr>";
		}
			echo $output;
			$i++;
		}
	}
?>
	</table>
	</div>
<?php
	$output = ob_get_clean();
	if($debug_options['send_debug_mail'] == 1 && $send_email == 1) { error_handler_mailer($output); }
	if($debug_options['enabled'] == 1) { echo $output; }
}

function get_backtrace() {
	//if(empty($debug_options['enabled_backtrace'])) { return false; }
	$backtrace = debug_backtrace();

	array_shift($backtrace);

	$next_context = '';
	$next_e = null;

	$temp = array();
	foreach($backtrace as $k => $bt) {

		$new_e = $next_e;

		if($new_e != null) {
			$args = array();
			$new_e['function'] = (isset($bt['class']) ? $bt['class'] : '<em>N/A</em>') .
				(isset($bt['type']) ? $bt['type'] : '<em>N/A</em>') .
				$bt['function']=='unknown' ? '<em>N/A</em>' : ($bt['function'] . '(' . implode(', ', $args) . ')');

			$temp[] = $new_e;
		}

		$next_e = array(
			'file' => (!empty($bt['file']) ? $bt['file'] : ''),
			'line' => (!empty($bt['line']) ? $bt['line'] : ''),
		);
	}

	$next_e['function'] = '<em>N/A</em>';
	$temp[] = $next_e;

	return($temp);
}

function display_backtrace($backtrace) {
	$output = '<p>';
	$cnt = 1;
	foreach($backtrace as $v) {
		$output .= "<div class='mt'><strong>Step ". $cnt++ ."</strong>";
		if(isset($v['function']) && $v['function'] != '<em>N/A</em>') { $output .= "<br />Function: ". $v['function']; }
		if(!empty($v['line'])) { $output .= " -- Line: ". $v['line']; }
		if(!empty($v['file'])) { $output .= "<br />File: ". $v['file']; }
		$output .= "</div>";
	}
	return $output . "</p>";
}

function error_handler_mailer($output) {

	$output = str_replace('hide_context','context',$output);
	$output = str_replace('hide_backtrace','backtrace',$output);
	$output = str_replace("class='error_debug_box'","style='margin: 30px 10px 10px; background: #ECEFD1; border: 1px solid gray; padding: 5px;'",$output);
	$output = str_replace("class='error_debug_title'","style=' cursor: pointer; font-size: 93%; padding: 1px 10px; border: 1px solid gray; background: #D1DCEF; color: #333333;'",$output);
	$output = str_replace("class='show_more'","style='background: white; border-bottom: 1px solid gray; white-space: nowrap; padding: 0 5px;'",$output);
	$output = str_replace("class='context'","style='background: #FEFFBF; border-right: 1px solid gray; border-bottom: 1px solid gray; padding: 5px 10px; font-size: 93%;'",$output);
	$output = str_replace("class='backtrace'","style='background: #D5FECD; border-right: 1px solid gray; border-bottom: 1px solid gray; padding: 5px 10px; font-size: 93%;'",$output);
	$output = str_replace("class='error_details'","style='width: 100%; padding-left: 5px; border-bottom: 1px solid gray; background: white; font-size: 93%;'",$output);
	$output = str_replace("class='undln'","style='text-decoration: underline;'",$output);

	$output = str_replace("class='error_type error_debug_red'","style='background: #FF6F6F; border-right: 1px solid gray; border-bottom: 1px solid gray; text-align: center; font-weight: bold; padding: 2px 5px;'",$output);
	$output = str_replace("class='error_type error_debug_orange'","style='background: #FFBF4F; border-right: 1px solid gray; border-bottom: 1px solid gray; text-align: center; font-weight: bold; padding: 2px 5px;'",$output);
	$output = str_replace("class='error_type error_debug_yellow'","style='background: #F5FF9F; border-right: 1px solid gray; border-bottom: 1px solid gray; text-align: center; font-weight: bold; padding: 2px 5px;'",$output);
	$output = str_replace("class='error_type error_debug_gray'","style='background: #CFCFCF; border-right: 1px solid gray; border-bottom: 1px solid gray; text-align: center; font-weight: bold; padding: 2px 5px;'",$output);
	$output = str_replace("class='error_type error_debug_redtext'","style='color: #FF0000; border-right: 1px solid gray; border-bottom: 1px solid gray; text-align: center; font-weight: bold; padding: 2px 5px;'",$output);



	// message
	$message = "
	<html>
	<head>
	  <title>Debug Messages</title>
	</head>
	<body>
	<style type='text/css'>

	</style>
		<table style='margin-bottom: 10px; border: 1px solid #666666;'>
			<tr style='border-bottom: 1px solid #666666;'>
				<td style='padding: 2px 4px; background: #dddddd;'><strong>Script URI</strong></td><td>". $_SERVER['SCRIPT_URI'] ."</td></tr>
			<tr style='border-bottom: 1px solid #666666;'>
				<td style='padding: 2px 4px; background: #dddddd;'><strong>HTTP User Agent</strong></td><td>". $_SERVER['HTTP_USER_AGENT'] ."</td></tr>
			<tr style='border-bottom: 1px solid #666666;'>
				<td style='padding: 2px 4px; background: #dddddd;'><strong>Script Filename</strong></td><td>". $_SERVER['SCRIPT_FILENAME'] ."</td></tr>
			<tr style='border-bottom: 1px solid #666666;'>
				<td style='padding: 2px 4px; background: #dddddd;'><strong>Date and Time</strong></td><td>". date('Y-m-d H:i:s') ."</td></tr>
		</table>

		". $output ."
	</body>
	</html>
";

	$debug_options =& $GLOBALS["debug_options"];

	// $to = str_replace('@','+debug_message@',$GLOBALS['project_info']['default_email']);
	$to = $debug_options['debug_email'];

	// subject
	$subject = "Debug Messages - ". $GLOBALS['project_info']['alias'];

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

	// Additional headers
	$headers .= 'To: '. $to ."\r\n";
	$headers .= 'From: System <noreply@'. $GLOBALS['project_info']['company_name'] . ">\r\n";
	#$headers .= 'Cc: shdowhawk2002@yahoo.com' . "\r\n";

	// Mail it
	mail($to, $subject, $message, $headers) or die("email didn't send");
}
