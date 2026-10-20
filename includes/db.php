<?php

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0"); 

$host = "localhost";
$user = "root"; 
$pass = ""; 
$db = "ceiling_decor_db";
$charset = "utf8mb4";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    
    die("Database connection failed. Please ensure the database '$db' exists and credentials are correct.");
}

$conn->set_charset($charset);
?>
