<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "photo_app";

// Create connection
// error control operator @ to suppress error message
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
  die('Something went wrong');
}
