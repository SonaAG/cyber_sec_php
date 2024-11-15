<?php
// Load environment variables (for DATABASE_URL) if using dotenv or set on Render.
$databaseUrl = getenv('DATABASE_URL');
if (!$databaseUrl) {
    die("Database configuration not found.");
}

// Parse the DATABASE_URL environment variable
$dbopts = parse_url($databaseUrl);
$host = $dbopts["host"];
$port = isset($dbopts["port"]) ? $dbopts["port"] : 5432;
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

// Connect to PostgreSQL using PDO
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $conn = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    die("Service unavailable. Please try again later.");
}

// Start session for managing login
session_start();

// Handle login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        session_regenerate_id(true);
        $_SESSION['user'] = $username;
        echo "Login successful. Welcome, " . htmlspecialchars($username, ENT_QUOTES, 'UTF-8') . "!";
    } else {
        echo "Invalid credentials.";
    }
}

// Handle file upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['upload_file'])) {
    $upload_dir = "uploads/";
    $file_name = basename($_FILES['upload_file']['name']);
    $file_tmp_name = $_FILES['upload_file']['tmp_name'];
    $file_type = $_FILES['upload_file']['type'];
    
    // Allowed file types for security
    $allowed_types = ['image/jpeg', 'image/png'];
    if (in_array($file_type, $allowed_types)) {
        $safe_name = uniqid() . "_" . $file_name;
        move_uploaded_file($file_tmp_name, $upload_dir . $safe_name);
        echo "File uploaded successfully: " . htmlspecialchars($safe_name, ENT_QUOTES, 'UTF-8');
    } else {
        echo "Invalid file type.";
    }
}

// Display user profile if logged in
if (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($profile) {
        echo "<h2>User Profile</h2>";
        echo "Username: " . htmlspecialchars($profile['username'], ENT_QUOTES, 'UTF-8') . "<br>";
        echo "Email: " . htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8') . "<br>";
    } else {
        echo "Please login to see your profile.";
    }
}

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("UPDATE users SET password = :password WHERE username = :username");
    if ($stmt->execute(['password' => $hashed_password, 'username' => $_SESSION['user']])) {
        echo "Password updated successfully.";
    } else {
        echo "Error updating password.";
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    echo "You have been logged out.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Advanced Secure PHP Application</title>
</head>
<body>
<h2>Login</h2>
<form method="POST" action="">
    Username: <input type="text" name="username" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" name="login" value="Login">
</form>

<h2>Upload File</h2>
<form method="POST" enctype="multipart/form-data" action="">
    Choose file: <input type="file" name="upload_file" required><br>
    <input type="submit" value="Upload File">
</form>

<h2>Change Password</h2>
<form method="POST" action="">
    New Password: <input type="password" name="new_password" required><br>
    <input type="submit" name="change_password" value="Change Password">
</form>

<a href="?logout=true">Logout</a>
</body>
</html>
