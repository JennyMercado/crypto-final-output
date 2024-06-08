<?php
session_start();
include("db.php");

// Check if form is submitted
if (isset($_POST['send'])) {
    // Retrieve form data
    $pname = isset($_POST['name']) ? $_POST['name'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $pnumber = isset($_POST['number']) ? $_POST['number'] : '';
    $message = isset($_POST['message']) ? $_POST['message'] : '';

    // Sanitize and validate email
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format");
    }

    // Sanitize phone number
    $pnumber = filter_var($pnumber, FILTER_SANITIZE_NUMBER_INT);

    // Insert data into database using prepared statement to prevent SQL injection
    $con = mysqli_connect("localhost", "root", "", "medical");
    if (!$con) {
        die("Connection failed: " . mysqli_connect_error());
    }
    $que = "INSERT INTO `contact` (`id`, `pname`, `email`, `pnumber`, `message`) VALUES (0, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($con, $que);
    mysqli_stmt_bind_param($stmt, "ssss", $pname, $email, $pnumber, $message);
    mysqli_stmt_execute($stmt);

    // Close statement and connection
    mysqli_stmt_close($stmt);
    mysqli_close($con);

    // Redirect to main page after submission
    header('location: about.html');
    exit; // Make sure to exit after redirection
}
?>



