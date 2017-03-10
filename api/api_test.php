<?php


$privateKey = 'Qqlcn5Y66DqMB6k2EYsusCbggq/L7AprQYIi+anxcLI';

$uid = 'ABC123';
$time = time();


list($host,$data) = example_get_questions();
// list($host,$data) = example_2();

$message = buildMessage($time, $uid, $data);
$hash = hash_hmac('sha256', $message, $privateKey);

$headers = ['API_UID: ' . $uid, 'API_TIME: ' . $time, 'API_HASH: ' . $hash];

$ch = curl_init();
curl_setopt($ch, CURLOPT_VERBOSE, TRUE);
curl_setopt($ch, CURLOPT_URL, $host);
// curl_setopt($ch, CURLOPT_POST, TRUE);
// curl_setopt($ch, CURLOPT_HEADER, TRUE);
// curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
#curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
if ($result === FALSE) {
	echo "Curl Error: " . curl_error($ch);
} else {
	// echo PHP_EOL;
	// echo "Request: " . PHP_EOL;
	// echo curl_getinfo($ch, CURLINFO_HEADER_OUT);	
	// echo PHP_EOL;
	// echo "Response:" . PHP_EOL;

	header('Content-type: text/xml');
	echo $result; 
	// echo PHP_EOL;
}
curl_close($ch);










function buildMessage($time, $id, array $data) {
	return $time . $id . implode($data);
}



function example_get_questions() {
	return [
		// "https://api.clevercrazes.local/questions/?world=1&theme=12&genre="
		"http://api.clevercrazes.local/questions/?world=i,ws&theme=bs,da&genre=a,dav&student_id=1342"
		,["http://api.clevercrazes.local/questions/"]
	];
}

function example_put_scores() {
	return [
		// "https://api.clevercrazes.local/questions/?world=1&theme=12&genre="
		// !cck?forkids&
		"http://api.clevercrazes.local/scores/save/?world=18&parent=52612&child=209696&game=0&score=100&question=592250&reps=1&playasaclassroom=0&version=1.12&bundle_id=4"
		// "http://api.clevercrazes.local/scores/save/"
		// "http://api.clevercrazes.local/scores/save/?world=18&parent=52612&child=209696&0&reps=1&playasaclassroom=0&version=1.12"
		,["http://api.clevercrazes.local/scores/save/"]
	];
}
