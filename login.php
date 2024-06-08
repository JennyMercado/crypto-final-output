<?php
// Establish database connection
$db = mysqli_connect("localhost", "root", "", "medical");

if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($db, $_POST['username']);
    $password = mysqli_real_escape_string($db, $_POST['password']);

    // Check credentials
    $sql = "SELECT username, password FROM `signup` WHERE username='$username'"; // Replace 'signup' with your actual table name
    $result = mysqli_query($db, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);
        $stored_password = $row['password'];

        // Verify password
        if (password_verify($password, $stored_password)) {
            // Log login attempt
            $d = date("Y-m-d h:i:sa");
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $que = "INSERT INTO `login` (`user`, `password`, `date_time`) VALUES ('$username', '$hashed_password', '$d')";
            mysqli_query($db, $que);

            // Redirect to index.php upon successful login
            header("Location: about.html");
            exit();
        } else {
            echo "<script>alert('Invalid username or password');</script>";
        }
    } else {
        echo "<script>alert('Invalid username or password');</script>";
    }
}
?>


