<?php
$db = mysqli_connect("localhost", "root", "", "medical");

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
?>