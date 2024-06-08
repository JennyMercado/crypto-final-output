<?php
session_start();
include("db.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (!empty($username) && !empty($email) && !empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Use prepared statements to prevent SQL injection
        $stmt = $db->prepare("INSERT INTO `signup` (`username`, `email`, `password`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $hashed_password);

        if ($stmt->execute()) {
            // Redirect to mainPage.php after successful registration
            header("Location: index.html");
            exit();
        } else {
            echo "<script type='text/javascript'> alert('Error registering user');</script>";
        }
        $stmt->close();
    } else {
        echo "<script type='text/javascript'> alert('Please Enter Valid Information');</script>";
    }
}
?>


