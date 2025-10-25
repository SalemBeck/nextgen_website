<?php

if (in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1'])) {
    // Local XAMPP setup
    $host = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nextgen_website";
} else {
    // Remote FreeSQLDatabase connection
    $host = "sql8.freesqldatabase.com";
    $username = "sql8804504";
    $password = "PtpePIsLqq";
    $dbname = "sql8804504";
}

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>
