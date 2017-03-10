<?php

# to:  Joe <joe@example.com>, Bob Jones <bob.jones@example.com>
# options: 	Array (
#				'cc' => 'Marley <marley@example.com>'
#				'bcc' => 'Zoe <zoe@example.com>'
#				'from' => 'No Reply <norepy@example.com>'
#			)
function send_mail($to,$subject,$message,$options=array()) {

	if(empty($to)) { return false; }

	// To send HTML mail, the Content-type header must be set
	$headers  = 'MIME-Version: 1.0' . "\r\n";
	$headers .= 'Content-type: text/html; charset=utf-8' . "\r\n";
	// Additional headers
	$headers .= 'To: '. $to . "\r\n";
	
	if(empty($options['from'])) {
		$headers .= 'From: '. $GLOBALS['project_info']['name'] .' <noreply@'. $GLOBALS['project_info']['dns'] .'>' . "\r\n";
	} else {
		$headers .= 'From: '. $options['from'] ."\r\n";
	}

	if(!empty($options['cc'])) { $headers .= 'Cc: '. $options['cc'] ."\r\n"; }
	if(!empty($options['bcc'])) { $headers .= 'Bcc: '. $options['bcc'] ."\r\n"; }

	// Mail it
	if(mail($to, $subject, $message, $headers)) { return true; }
	return false;
}


?>