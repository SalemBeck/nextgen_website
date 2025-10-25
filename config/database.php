<?php


if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    $host = "localhost";
    $username = "root";
    $password = "";
} else {
    $host = "your_remote_host";
    $username = "your_remote_username";
    $password = "your_remote_password";
}

$dbname = "nextgen_website";


$conn = new mysqli($host, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$conn->set_charset("utf8");
