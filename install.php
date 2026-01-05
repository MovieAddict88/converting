<?php
/**
 * NeonVox Karaoke Platform - Auto Installer
 * This script handles the complete setup of the application
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

$step = $_GET['step'] ?? '1';
$error = '';
$success = '';

// Initialize session
session_start();

// Step 1: Check requirements
if ($step === '1') {
    $php_version = phpversion();
    $php_ok = version_compare($php_version, '7.4.0', '>=');
    $mysqli_ok = extension_loaded('mysqli');
    $json_ok = extension_loaded('json');
    $mbstring_ok = extension_loaded('mbstring');
    $writable = is_writable(__DIR__);

    $requirements_ok = $php_ok && $mysqli_ok && $json_ok && $mbstring_ok && $writable;
}

// Step 2: Database configuration
if ($step === '2' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $db_host = $_POST['db_host'] ?? 'localhost';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $admin_username = $_POST['admin_username'] ?? 'admin';
    $admin_password = $_POST['admin_password'] ?? '';
    $site_url = $_POST['site_url'] ?? 'http://' . $_SERVER['HTTP_HOST'];

    // Validate inputs
    if (empty($db_user) || empty($db_name) || empty($admin_password)) {
        $error = 'Please fill in all required fields';
    } elseif (strlen($admin_password) < 6) {
        $error = 'Admin password must be at least 6 characters';
    } else {
        try {
            // Test database connection
            $conn = new mysqli($db_host, $db_user, $db_pass);
            if ($conn->connect_error) {
                throw new Exception('Database connection failed: ' . $conn->connect_error);
            }

            // Create database if it doesn't exist
            $conn->query("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $conn->select_db($db_name);

            // Create tables
            $sql = file_get_contents(__DIR__ . '/schema.sql');
            if (!$sql) {
                throw new Exception('Could not read schema.sql file');
            }

            // Split and execute queries
            $queries = array_filter(array_map('trim', explode(';', $sql)));
            foreach ($queries as $query) {
                if (!empty($query)) {
                    if (!$conn->query($query)) {
                        throw new Exception('Database error: ' . $conn->error);
                    }
                }
            }

            // Insert default admin
            $hashed_password = password_hash($admin_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO admins (username, password, created_at) VALUES (?, ?, NOW())");
            $stmt->bind_param('ss', $admin_username, $hashed_password);
            $stmt->execute();
            $stmt->close();

            // Create config file
            $config_content = "<?php
// Database Configuration
define('DB_HOST', '$db_host');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_NAME', '$db_name');

// Site Configuration
define('SITE_URL', '$site_url');
define('SITE_NAME', 'NeonVox');
define('JWT_SECRET', '" . bin2hex(random_bytes(32)) . "');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100MB
";

            if (file_put_contents(__DIR__ . '/config.php', $config_content)) {
                $conn->close();
                header('Location: install.php?step=3');
                exit;
            } else {
                throw new Exception('Could not write config.php file. Please check permissions.');
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

// Step 3: Complete
if ($step === '3') {
    // Create uploads directory if it doesn't exist
    if (!file_exists(__DIR__ . '/uploads')) {
        mkdir(__DIR__ . '/uploads', 0755, true);
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonVox Installation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Outfit:wght@300;400;600&family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background: #030305;
            --foreground: #FFFFFF;
            --card: #0A0A0F;
            --primary: #FF0099;
            --primary-foreground: #FFFFFF;
            --secondary: #00F0FF;
            --secondary-foreground: #000000;
            --muted: #1A1A24;
            --muted-foreground: #A1A1AA;
            --border: #27272A;
            --input: #18181B;
            --ring: #FF0099;
            --success: #00FF94;
            --error: #FF0055;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--background);
            color: var(--foreground);
            min-height: 100vh;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: clamp(1rem, 5vw, 3rem);
        }

        .header {
            text-align: center;
            margin-bottom: clamp(2rem, 8vw, 4rem);
            padding-top: clamp(2rem, 10vw, 4rem);
        }

        .logo {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(2rem, 10vw, 4rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
        }

        .subtitle {
            color: var(--muted-foreground);
            font-size: clamp(1rem, 4vw, 1.25rem);
            margin-top: 0.5rem;
        }

        .card {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 1rem;
            padding: clamp(1.5rem, 5vw, 2.5rem);
            margin-bottom: 2rem;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .step {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Mono', monospace;
            font-weight: bold;
            transition: all 0.3s ease;
        }

        .step.active {
            background: var(--primary);
            box-shadow: 0 0 20px rgba(255, 0, 153, 0.5);
        }

        .step.completed {
            background: var(--success);
        }

        h2 {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.5rem, 6vw, 2rem);
            margin-bottom: 1.5rem;
        }

        .check-list {
            list-style: none;
        }

        .check-list li {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1rem;
            background: var(--muted);
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .check-list li.ok {
            border-left: 3px solid var(--success);
        }

        .check-list li.error {
            border-left: 3px solid var(--error);
        }

        .icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .icon.success {
            background: var(--success);
            color: var(--background);
        }

        .icon.error {
            background: var(--error);
            color: white;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        input[type="text"],
        input[type="password"],
        input[type="url"] {
            width: 100%;
            padding: clamp(0.75rem, 3vw, 1rem);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: clamp(0.9rem, 3vw, 1rem);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 0, 153, 0.2);
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: clamp(0.75rem, 3vw, 1rem) clamp(1.5rem, 6vw, 2rem);
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9999px;
            font-family: 'Outfit', sans-serif;
            font-weight: bold;
            font-size: clamp(0.9rem, 3vw, 1rem);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(255, 0, 153, 0.4);
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .button:hover {
            box-shadow: 0 0 25px rgba(255, 0, 153, 0.6);
            transform: scale(1.05);
        }

        .button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .alert.error {
            background: rgba(255, 0, 85, 0.2);
            border: 1px solid var(--error);
            color: var(--error);
        }

        .alert.success {
            background: rgba(0, 255, 148, 0.2);
            border: 1px solid var(--success);
            color: var(--success);
        }

        .success-content {
            text-align: center;
        }

        .success-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: var(--success);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
        }

        .success-content p {
            color: var(--muted-foreground);
            margin-bottom: 2rem;
            font-size: clamp(1rem, 4vw, 1.1rem);
        }

        .progress-bar {
            height: 4px;
            background: var(--muted);
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            transition: width 0.5s ease;
        }

        @media (max-width: 640px) {
            .step {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }
        }

        @media (min-width: 1920px) {
            .container {
                max-width: 1000px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="logo">NeonVox</h1>
            <p class="subtitle">Karaoke Platform Installation</p>
        </div>

        <?php if ($error): ?>
            <div class="alert error">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="progress-bar">
            <div class="progress-fill" style="width: <?php echo ($step * 33); ?>%"></div>
        </div>

        <div class="step-indicator">
            <div class="step <?php echo $step >= '1' ? 'active' : ''; ?> <?php echo $step > '1' ? 'completed' : ''; ?>">1</div>
            <div class="step <?php echo $step >= '2' ? 'active' : ''; ?> <?php echo $step > '2' ? 'completed' : ''; ?>">2</div>
            <div class="step <?php echo $step >= '3' ? 'active' : ''; ?>">3</div>
        </div>

        <?php if ($step === '1'): ?>
            <div class="card">
                <h2>System Requirements Check</h2>
                <ul class="check-list">
                    <li class="<?php echo $php_ok ? 'ok' : 'error'; ?>">
                        <div class="icon <?php echo $php_ok ? 'success' : 'error'; ?>">
                            <?php echo $php_ok ? '✓' : '✗'; ?>
                        </div>
                        <div>
                            <strong>PHP Version</strong><br>
                            <?php echo $php_version; ?> (requires 7.4+)
                        </div>
                    </li>
                    <li class="<?php echo $mysqli_ok ? 'ok' : 'error'; ?>">
                        <div class="icon <?php echo $mysqli_ok ? 'success' : 'error'; ?>">
                            <?php echo $mysqli_ok ? '✓' : '✗'; ?>
                        </div>
                        <div>
                            <strong>MySQLi Extension</strong><br>
                            <?php echo $mysqli_ok ? 'Installed' : 'Not installed'; ?>
                        </div>
                    </li>
                    <li class="<?php echo $json_ok ? 'ok' : 'error'; ?>">
                        <div class="icon <?php echo $json_ok ? 'success' : 'error'; ?>">
                            <?php echo $json_ok ? '✓' : '✗'; ?>
                        </div>
                        <div>
                            <strong>JSON Extension</strong><br>
                            <?php echo $json_ok ? 'Installed' : 'Not installed'; ?>
                        </div>
                    </li>
                    <li class="<?php echo $mbstring_ok ? 'ok' : 'error'; ?>">
                        <div class="icon <?php echo $mbstring_ok ? 'success' : 'error'; ?>">
                            <?php echo $mbstring_ok ? '✓' : '✗'; ?>
                        </div>
                        <div>
                            <strong>MBString Extension</strong><br>
                            <?php echo $mbstring_ok ? 'Installed' : 'Not installed'; ?>
                        </div>
                    </li>
                    <li class="<?php echo $writable ? 'ok' : 'error'; ?>">
                        <div class="icon <?php echo $writable ? 'success' : 'error'; ?>">
                            <?php echo $writable ? '✓' : '✗'; ?>
                        </div>
                        <div>
                            <strong>Directory Writable</strong><br>
                            <?php echo $writable ? 'Yes' : 'No (chmod 755 required)'; ?>
                        </div>
                    </li>
                </ul>

                <?php if ($requirements_ok): ?>
                    <div class="alert success">All requirements met!</div>
                    <a href="?step=2" class="button">Continue</a>
                <?php else: ?>
                    <div class="alert error">Please fix the issues above before continuing.</div>
                <?php endif; ?>
            </div>

        <?php elseif ($step === '2'): ?>
            <div class="card">
                <h2>Database Configuration</h2>
                <form method="POST">
                    <div class="form-group">
                        <label for="db_host">Database Host</label>
                        <input type="text" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="form-group">
                        <label for="db_user">Database Username</label>
                        <input type="text" id="db_user" name="db_user" required>
                    </div>
                    <div class="form-group">
                        <label for="db_pass">Database Password</label>
                        <input type="password" id="db_pass" name="db_pass">
                    </div>
                    <div class="form-group">
                        <label for="db_name">Database Name</label>
                        <input type="text" id="db_name" name="db_name" placeholder="neonvox_db" required>
                    </div>
                    <div class="form-group">
                        <label for="site_url">Site URL</label>
                        <input type="url" id="site_url" name="site_url" value="<?php echo 'http://' . $_SERVER['HTTP_HOST']; ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_username">Admin Username</label>
                        <input type="text" id="admin_username" name="admin_username" value="admin" required>
                    </div>
                    <div class="form-group">
                        <label for="admin_password">Admin Password</label>
                        <input type="password" id="admin_password" name="admin_password" required minlength="6">
                    </div>
                    <button type="submit" class="button">Install NeonVox</button>
                </form>
            </div>

        <?php elseif ($step === '3'): ?>
            <div class="card">
                <div class="success-content">
                    <div class="success-icon">✓</div>
                    <h2>Installation Complete!</h2>
                    <p>NeonVox has been successfully installed. You can now access your karaoke platform.</p>
                    <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                        <a href="index.php" class="button">Visit Site</a>
                        <a href="admin/login.php" class="button" style="background: var(--secondary); color: var(--secondary-foreground);">Admin Panel</a>
                    </div>
                    <p style="margin-top: 2rem; font-size: 0.9rem;">
                        <strong>Security Note:</strong> Please delete install.php after completing installation.
                    </p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
