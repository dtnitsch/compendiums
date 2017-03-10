#!/usr/bin/php
<?php

date_default_timezone_set("America/New_York");

######################################
# Project Settings
######################################

# NOTE:
# This project requires setting up an ssh key to the backup destination

$dump_folder = "/tmp/databases/";

$sunday_backups = true;
$first_day_of_month_backups = true;

$email_subject = "Clever Crazes Backup";
$email_list = array("fanlfdn@fdnalnlfa.com");

ob_start();

######################################
# Main Code
######################################

echo "//-------------------------------------------------";
echo "\n\tStarting Database Backup";
echo "\n//-------------------------------------------------\n";

$time_start = microtime(true);
$current_time = microtime(true);
$time = number_format($current_time - $time_start, 2);
$day_of_week = strtolower(date("l"));
$table_start_time = microtime(true);
$db = "clevercrazes";

echo "\n"." Starting Dump (".$time." seconds)";

$command = "pg_dump -Fc clevercrazes > ".$dump_folder.$db."-".$day_of_week.".sql.gz";
// TO RESTORE RUN
// pg_restore -c -d dbname /path/to/backup.sql.gz

exec($command);

$command = "cp  ".$dump_folder.$db."-".$day_of_week.".sql.gz ".$dump_folder." clevercrazes-sync.sql.gz";

exec($command);

$table_time = number_format(microtime(true) - $table_start_time, 2);
echo "\nDumping table '".$db."' - ".$table_time." seconds";

if ($sunday_backups && date("w") == 0) {
	echo "\n  - Sunday Backup Complete";
	$command = "cp ".$dump_folder.$db."-".$day_of_week.".sql.gz ".$dump_folder.$db."-".date("Ymd").".sql.gz";
	exec($command);
}

if ($first_day_of_month_backups && date('j') == 1) {
	echo "\n  - First of Month Backup Complete";
	$command = 'cp '. $dump_folder . $db .'-'. $day_of_week .'.sql.gz '. $dump_folder . $db .'-'. date('Y_F') .'.sql.gz';
	exec($command);
}

$current_time = microtime(true);
$time = number_format($current_time - $time_start,2);

echo "\n\n//-------------------------------------------------";
echo "\n\tDatabase Backup Complete (". $time ." seconds)";
echo "\n//-------------------------------------------------\n";

$output = ob_get_clean();

foreach ($email_list as $email) {
	mail($email, $email_subject, $output);
}
