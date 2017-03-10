<?php
_error_debug("Starting Ajax",'',__LINE__,__FILE__);

$q = "
	select pin
	,username
	from system.users
	where lower(email) = '". db_prep_sql(strtolower($_POST['email'])) ."'
";
$row = db_fetch($q,"Getting Email");

$success = (empty($row) ? false : true);

if($success) {

	$pin_mail_to = $_POST['email'];
	$pin_mail_subject = "Clever Crazes for Kids Account Information";
	$pin_mail_from_address = "kclark@clevercrazes.com";
	$pin_mail_from_display_name = "CleverCrazes.com";
	$pin_mail_text_message = "Your login name is: ".$row["username"]."\n";
	$pin_mail_text_message .= "Your PIN is: ".$row["pin"]."\n";
	$pin_mail_html_message = "Your login name is: ".$row["username"]."<br />\n";
	$pin_mail_html_message .= "Your PIN is: ".$row["pin"]."<br />\n";
	multipart_mail($pin_mail_to, $pin_mail_subject, $pin_mail_html_message, $pin_mail_text_message, $pin_mail_from_address, $pin_mail_from_display_name);
}

_error_debug("Ending Ajax",'',__LINE__,__FILE__);
echo json_encode(array(
	"output" => array("success" => $success)
	,"debug" => ajax_debug()
));

function multipart_mail ($to, $subject, $html_message, $text_message, $from_address, $from_display_name='',$to_display_name='',$bccemail='',$replyto=''){
	$email_from_addr = $from_address; // actual email address of the sender
	$email_from_name = $from_display_name; // display name, if any, that the sender wishes to use
	$email_subject =  $subject; // The Subject of the email
	$email_txt = $text_message; // Message that the email has in it
	$email_htm = $html_message; // Message that the email has in it
	$email_to = $to_display_name == '' ? $to : "$to_display_name <$to>"; // Who the email is to
	$headers = $email_from_name == '' ? "From: ".$email_from_addr."\n" : "From: ".$email_from_name." <".$email_from_addr.">\n";
	if($bccemail) $headers .= "BCC: $bccemail \n";

	$sTimestamp = date("r");
	$headers .= "Date: $sTimestamp\n";

	$headers .= $replyto == '' ? "Reply-To: ".$email_from_addr."\n" : "Reply-To: ".$replyto."\n";

	$headers .= 'X-Mailer: 123YourWeb Mailer 1.0' . "\n";

	$semi_rand = md5(time());
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
	$headers .= "MIME-Version: 1.0\n" .
			"Content-Type: multipart/alternative;\n" .
			" boundary=\"{$mime_boundary}\"";
	$email_message .= "This is a multi-part message in MIME format.\n\n" .
				"--{$mime_boundary}\n" .
				"Content-Type:text/plain; charset=\"iso-8859-1\"\n" .
				"Content-Transfer-Encoding: 7bit\n\n" .
				$email_txt . "\n".
				"--{$mime_boundary}\n" .
				"Content-Type:text/html; charset=\"iso-8859-1\"\n" .
				"Content-Transfer-Encoding: 7bit\n\n" .
				$email_htm ;

	$email_message = wordwrap($email_message);

	$ok = @mail($email_to, $email_subject, $email_message, $headers);
	if(! $ok) {
		//die("Sorry but the email could not be sent. Please go back and try again!");
		return false;
	} else {
		return true;
	}
}