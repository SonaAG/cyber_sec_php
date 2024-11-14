<?php
$servername = "localhost";
$username = "root";
$password = ""; // Secure this with a strong password.
$dbname = "advanced_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Service unavailable. Please try again later.");
}

session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc() && password_verify($password, $row['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $username;
        echo "Login successful. Welcome, " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "!";
    } else {
        echo "Invalid credentials.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['upload_file'])) {
    $upload_dir = "uploads/";
    $file_name = basename($_FILES['upload_file']['name']);
    $file_tmp_name = $_FILES['upload_file']['tmp_name'];
    $file_type = $_FILES['upload_file']['type'];
    
    $allowed_types = ['image/jpeg', 'image/png'];
    if (in_array($file_type, $allowed_types)) {
        $safe_name = uniqid() . "_" . $file_name;
        move_uploaded_file($file_tmp_name, $upload_dir . $safe_name);
        echo "File uploaded successfully: " . htmlspecialchars($safe_name, ENT_QUOTES, 'UTF-8');
    } else {
        echo "Invalid file type.";
    }
}

if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        echo "<h2>User Profile</h2>";
        echo "Username: " . htmlspecialchars($row['username'], ENT_QUOTES, 'UTF-8') . "<br>";
        echo "Email: " . htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') . "<br>";
    } else {
        echo "Please login to see your profile.";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = ?");
    $stmt->bind_param("ss", $hashed_password, $_SESSION['user']);
    if ($stmt->execute()) {
        echo "Password updated successfully.";
    } else {
        echo "Error updating password: " . $conn->error;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    echo "You have been logged out.";
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Advanced Vulnerable PHP Applications</title>
</head>
<body>
<h2>Login</h2>
<form method="POST" action="">
    Username: <input type="text" name="username"><br>
    Password: <input type="password" name="password"><br>
    <input type="submit" name="login" value="Login">
</form>
<h2>Upload File</h2>
<form method="POST" enctype="multipart/form-data" action="">
    Choose file: <input type="file" name="upload_file"><br>
    <input type="submit" value="Upload File">
</form>
<h2>Change Password</h2>
<form method="POST" action="">
    New Password: <input type="password" name="new_password"><br>
    <input type="submit" name="change_password" value="Change Password">
</form>
<a href="?logout=true">Logout</a>
</body>
</html>
