<?php

function error($msg = "") {
	echo "An error occured" .(!empty($msg) ? ": ". $msg : '');
}

