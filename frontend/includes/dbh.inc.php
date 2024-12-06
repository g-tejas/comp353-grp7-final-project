<?php

/*
$db_server = "lpc353.encs.concordia.ca";
$db_name = "lpc353_2";       //IMPORTANT TO NAME THE DB comp353
$db_username = "lpc353_2";
$db_password = "CleanlyTartanRerun82";   //CHANGE THIS TO YOUR MYSQL PASSWORD try "" or "root"
*/

$db_server = "localhost";
$db_name = "comp353";       //IMPORTANT TO NAME THE DB comp353
$db_username = "root";
$db_password = "";   //CHANGE THIS TO YOUR MYSQL PASSWORD try "" or "root"


$conn = mysqli_connect($db_server, $db_username, $db_password, $db_name);

if($conn === false){
    die("Connection failed: " . mysqli_connect_error());
}
else{
   // echo "Connected successfully";
}
