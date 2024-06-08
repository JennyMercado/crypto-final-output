<?php
session_start();
include("db.php"); 

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "medical"); 
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Check if form is submitted
if (isset($_POST['submit'])) {
    // Retrieve form data
    $patientName = isset($_POST['inputPatientName']) ? trim($_POST['inputPatientName']) : '';
    $doctorName = isset($_POST['inputDoctorName']) ? trim($_POST['inputDoctorName']) : '';
    $departmentName = isset($_POST['inputDepartmentName']) ? trim($_POST['inputDepartmentName']) : '';
    $phoneNumber = isset($_POST['inputPhone']) ? trim($_POST['inputPhone']) : '';
    $medicalRecord = isset($_POST['inputMedical']) ? trim($_POST['inputMedical']) : '';
    $dateOfBirth = isset($_POST['inputDateOfBirth']) ? trim($_POST['inputDateOfBirth']) : '';

    // Clean up phone number
    $phoneNumber = preg_replace('/\D/', '', $phoneNumber);

    // Validate form inputs
    if (empty($patientName) || empty($doctorName) || empty($departmentName) || empty($phoneNumber) || empty($medicalRecord) || empty($dateOfBirth)) {
        die("All fields are required.");
    }

    // Validate phone number length
    if (strlen($phoneNumber) != 11) {
        die("Invalid phone number format.");
    }

    // Handle file upload
    $uploadDirectory = "uploads/";
    $uploadedFilePath = ""; // Initialize the variable
    if (isset($_FILES['inputFile']) && $_FILES['inputFile']['error'] == UPLOAD_ERR_OK) {
        $allowedFileTypes = ['application/pdf', 'image/jpeg', 'image/png']; // Allowed file types
        if (in_array($_FILES['inputFile']['type'], $allowedFileTypes)) {
            $uploadedFileName = basename($_FILES['inputFile']['name']);
            $uploadedFilePath = $uploadDirectory . $uploadedFileName; // Correct path assignment
            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0755, true);
            }
            if (move_uploaded_file($_FILES['inputFile']['tmp_name'], $uploadedFilePath)) {
                // File successfully uploaded
                echo "File uploaded successfully.<br>";
            } else {
                die("File upload failed, please try again.");
            }
        } else {
            die("Invalid file type. Only PDF, JPEG, and PNG are allowed.");
        }
    } else {
        die("No file was uploaded.");
    }

    // Prepare file data to be stored in the database
    $fileData = file_get_contents($uploadedFilePath);
    $fileData = mysqli_real_escape_string($con, $fileData); // Escape special characters

    // Generate digital signature
    $dataToSign = $patientName . $doctorName . $departmentName . $phoneNumber . $medicalRecord . $dateOfBirth;
    $signature = hash('sha256', $dataToSign);

    // Insert data into database along with the signature
    $que = "INSERT INTO `medical_record` (`patient_name`, `doctor_name`, `department_name`, `phone_number`, `medical_record`, `date_of_birth`, `signature`, `upload_document`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = mysqli_prepare($con, $que)) {
        mysqli_stmt_bind_param($stmt, "ssssssss", $patientName, $doctorName, $departmentName, $phoneNumber, $medicalRecord, $dateOfBirth, $signature, $fileData);
        
        if (mysqli_stmt_execute($stmt)) {
            echo "Data inserted successfully.<br>";
        } else {
            die("Error executing statement: " . mysqli_stmt_error($stmt) . "<br>");
        }

        // Close statement
        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing statement: " . mysqli_error($con) . "<br>");
    }

    // Close the database connection
    mysqli_close($con);

    // Redirect to main page after submission
    header('Location: about.html');
    exit; 
}
?>




<?php
// Signature verification code
include("db.php"); 

// Establish database connection
$con = mysqli_connect("localhost", "root", "", "medical"); 
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Retrieve data from the database
$appointmentId = isset($_GET['id']) ? $_GET['id'] : null; // Assuming you have appointment ID in the URL
$query = "SELECT * FROM `appointments` WHERE `AppointmentId` = ?";
$stmt = mysqli_prepare($con, $query);
mysqli_stmt_bind_param($stmt, "i", $appointmentId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$appointment = mysqli_fetch_assoc($result);

// Close statement
mysqli_stmt_close($stmt);

// Verify signature
if ($appointment) {
    // Recreate the signature
    $dataToSign = $appointment['PatientName'] . $appointment['DoctorName'] . $appointment['DepartmentName'] . $appointment['PhoneNumber'] . $appointment['Symptoms'] . $appointment['AppointmentDate'];
    $recreatedSignature = hash('sha256', $dataToSign);

    // Compare signatures
    if ($recreatedSignature === $appointment['Signature']) {
        echo "Signature verified. Data integrity maintained.";
    } else {
        echo "Signature mismatch. Data may have been tampered with.";
    }
} else {
    echo "Appointment not found.";
}
?>