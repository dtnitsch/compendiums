<?php
####################################################################################################
# Add Users Script
####################################################################################################
require("../scripts/bootstrap.php");

library("membership.php");

####################################################################################################
# Main Function
$id = create_new_user([
    "firstname" => "Daniel"
    ,"lastname" => "Tisza-Nitsch"
    ,"username" => "shdowhawk"
    ,"email" => "daniel@tiszanitsch.com"
    ,"password" => "password"
    ,"is_superadmin" => "t"
]);

$body = "test";

include($GLOBALS["root_path"] ."templates/default.template.php");

