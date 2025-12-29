<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = isset($_POST['step']) ? (int)$_POST['step'] : 1;
$vendor_exists = is_dir('vendor');

if ($step === 2) {
    // Create directories if they don't exist
    if (!is_dir('config')) {
        mkdir('config', 0755, true);
    }
    if (!is_dir('uploads')) {
        mkdir('uploads', 0755, true);
    }

    $db_host = $_POST['db_host'];
    $db_name = $_POST['db_name'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $admin_user = 'admin';
    $admin_pass = 'admin123';
    $youtube_api_key = $_POST['youtube_api_key'];

    // Generate a random JWT secret
    $jwt_secret = bin2hex(random_bytes(32));

    // 1. Write config/config.php
    $config_content = "<?php\n";
    $config_content .= "define('DB_HOST', '$db_host');\n";
    $config_content .= "define('DB_NAME', '$db_name');\n";
    $config_content .= "define('DB_USER', '$db_user');\n";
    $config_content .= "define('DB_PASS', '$db_pass');\n";
    $config_content .= "define('JWT_SECRET', '$jwt_secret');\n";
    $config_content .= "define('YOUTUBE_API_KEY', '$youtube_api_key');\n";
    file_put_contents('config/config.php', $config_content);

    // 2. Connect and create database
    $conn = new mysqli($db_host, $db_user, $db_pass);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    $conn->query("CREATE DATABASE IF NOT EXISTS " . $conn->real_escape_string($db_name));
    $conn->select_db($db_name);

    // 3. Import schema.sql
    $schema = file_get_contents('schema.sql');
    if ($conn->multi_query($schema)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
    }

    // 4. Create default admin
    $hashed_password = password_hash($admin_pass, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $admin_user, $hashed_password);
    $stmt->execute();
    $stmt->close();

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>
    <style>
        body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 5px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input { width: 100%; padding: 8px; box-sizing: border-box; }
        button { padding: 10px 15px; background-color: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer; }
        .error { color: red; }
        .success { color: green; }
        .warning { color: orange; }
    </style>
</head>
<body>
    <h1>Karaoke Web App Installer</h1>

    <?php if ($step === 1): ?>
        <?php if (!$vendor_exists): ?>
            <p class="warning"><b>Warning:</b> The `vendor` directory is missing. Please run `composer install` in the root directory before proceeding.</p>
        <?php endif; ?>

        <form method="POST" action="install.php">
            <input type="hidden" name="step" value="2">
            <h2>Database Settings</h2>
            <div class="form-group">
                <label for="db_host">Host</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            <div class="form-group">
                <label for="db_name">Database Name</label>
                <input type="text" id="db_name" name="db_name" required>
            </div>
            <div class="form-group">
                <label for="db_user">Username</label>
                <input type="text" id="db_user" name="db_user" required>
            </div>
            <div class="form-group">
                <label for="db_pass">Password</label>
                <input type="password" id="db_pass" name="db_pass">
            </div>


            <h2>API Keys</h2>
             <div class="form-group">
                <label for="youtube_api_key">YouTube API Key</label>
                <input type="text" id="youtube_api_key" name="youtube_api_key" required>
            </div>

            <button type="submit" <?php if (!$vendor_exists) echo 'disabled'; ?>>Install</button>
        </form>
    <?php elseif ($step === 2): ?>
        <p class="success"><b>Installation Complete!</b></p>
        <p>The application has been installed successfully.</p>
        <p>You can now log in with the default credentials:</p>
        <p><b>Username:</b> admin</p>
        <p><b>Password:</b> admin123</p>
        <p class="error"><b>IMPORTANT:</b> For security reasons, please delete this `install.php` file now.</p>
    <?php endif; ?>
</body>
</html>
