<?php
function call_api_function($function,$params) {
    $file = "../api/functions/". $function .".php";
    if(!is_file($file)) {
        error_message("API Call '". $function ."' does not exist");
        return;
    }

    include($file);
    ob_start();
    $function($params);
    $output = ob_get_clean();
    return $output;
}