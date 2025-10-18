<?php
/**
 * Flipbook Database Setup Wizard
 * Run this once to set up your database
 * DELETE THIS FILE after setup is complete!
 */

$setupComplete = false;
$errors = [];
$messages = [];

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? 'localhost';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';

    if (empty($dbName) || empty($dbUser)) {
        $errors[] = 'Database name and username are required';
    } else {
        try {
            // Test connection
            $dsn = "mysql:host=$dbHost;charset=utf8mb4";
            $pdo = new PDO($dsn, $dbUser, $dbPass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);

            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $messages[] = "Database '$dbName' created successfully";

            // Connect to new database
            $pdo->exec("USE `$dbName`");

            // Create tables
            $sql = "
            CREATE TABLE IF NOT EXISTS flipbooks (
                id INT AUTO_INCREMENT PRIMARY KEY,
                title VARCHAR(255) NOT NULL,
                description TEXT,
                page_count INT NOT NULL DEFAULT 0,
                orientation VARCHAR(20) DEFAULT 'portrait',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                is_active BOOLEAN DEFAULT TRUE,
                INDEX idx_created (created_at),
                INDEX idx_active (is_active)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS pages (
                id INT AUTO_INCREMENT PRIMARY KEY,
                flipbook_id INT NOT NULL,
                page_number INT NOT NULL,
                image_data LONGTEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (flipbook_id) REFERENCES flipbooks(id) ON DELETE CASCADE,
                INDEX idx_flipbook (flipbook_id),
                INDEX idx_page_number (page_number),
                UNIQUE KEY unique_page (flipbook_id, page_number)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS audio_files (
                id INT AUTO_INCREMENT PRIMARY KEY,
                flipbook_id INT NOT NULL,
                name VARCHAR(255) NOT NULL,
                audio_data LONGTEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (flipbook_id) REFERENCES flipbooks(id) ON DELETE CASCADE,
                INDEX idx_flipbook (flipbook_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS page_audio_assignments (
                id INT AUTO_INCREMENT PRIMARY KEY,
                page_id INT NOT NULL,
                audio_id INT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (page_id) REFERENCES pages(id) ON DELETE CASCADE,
                FOREIGN KEY (audio_id) REFERENCES audio_files(id) ON DELETE CASCADE,
                UNIQUE KEY unique_assignment (page_id),
                INDEX idx_page (page_id),
                INDEX idx_audio (audio_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ";

            $pdo->exec($sql);
            $messages[] = "Database tables created successfully";

            // Update config file
            $configPath = __DIR__ . '/flipbook-config.php';
            $configContent = file_get_contents($configPath);

            $configContent = preg_replace(
                "/define\('DB_HOST', '.*?'\);/",
                "define('DB_HOST', '$dbHost');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('DB_NAME', '.*?'\);/",
                "define('DB_NAME', '$dbName');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('DB_USER', '.*?'\);/",
                "define('DB_USER', '$dbUser');",
                $configContent
            );
            $configContent = preg_replace(
                "/define\('DB_PASS', '.*?'\);/",
                "define('DB_PASS', '$dbPass');",
                $configContent
            );

            file_put_contents($configPath, $configContent);
            $messages[] = "Configuration file updated successfully";

            $setupComplete = true;

        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flipbook Database Setup</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 600px;
            width: 100%;
        }

        h1 {
            color: #333;
            margin-bottom: 10px;
        }

        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            background: #667eea;
            color: white;
            border: none;
            padding: 15px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        button:hover {
            background: #5568d3;
            transform: translateY(-2px);
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }

        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }

        .info {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #667eea;
        }

        .info h3 {
            margin-bottom: 10px;
            color: #667eea;
        }

        .info ul {
            margin-left: 20px;
        }

        .info li {
            margin: 5px 0;
        }

        .complete {
            text-align: center;
        }

        .complete h2 {
            color: #28a745;
            margin-bottom: 20px;
        }

        .btn-secondary {
            background: #28a745;
            margin-top: 10px;
        }

        .btn-secondary:hover {
            background: #218838;
        }

        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($setupComplete): ?>
            <div class="complete">
                <div style="font-size: 64px; margin-bottom: 20px;">✅</div>
                <h2>Setup Complete!</h2>

                <?php foreach ($messages as $msg): ?>
                    <div class="success"><?php echo htmlspecialchars($msg); ?></div>
                <?php endforeach; ?>

                <div class="warning">
                    <strong>⚠️ IMPORTANT SECURITY:</strong><br>
                    Delete this file (flipbook-setup-wizard.php) from your server immediately!
                </div>

                <a href="flipbook-admin-login.php" class="btn btn-secondary" style="display: inline-block; text-decoration: none; margin-top: 20px;">
                    Go to Admin Login
                </a>
            </div>
        <?php else: ?>
            <h1>🗄️ Flipbook Database Setup</h1>
            <p class="subtitle">Set up your database in 30 seconds</p>

            <?php if (!empty($errors)): ?>
                <?php foreach ($errors as $error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="info">
                <h3>Before you start:</h3>
                <ul>
                    <li>Go to your SiteGround cPanel</li>
                    <li>Click on "MySQL Databases"</li>
                    <li>Create a new database (or use existing)</li>
                    <li>Create a database user with a password</li>
                    <li>Add the user to the database with ALL PRIVILEGES</li>
                    <li>Enter those credentials below</li>
                </ul>
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="db_host">Database Host</label>
                    <input type="text" id="db_host" name="db_host" value="localhost" required>
                    <small style="color: #666;">Usually "localhost" for SiteGround</small>
                </div>

                <div class="form-group">
                    <label for="db_name">Database Name *</label>
                    <input type="text" id="db_name" name="db_name" placeholder="e.g., u2330snxbjnoydmhn_flipbook" required>
                </div>

                <div class="form-group">
                    <label for="db_user">Database Username *</label>
                    <input type="text" id="db_user" name="db_user" placeholder="e.g., u2330snxbjnoydmhn_admin" required>
                </div>

                <div class="form-group">
                    <label for="db_pass">Database Password *</label>
                    <input type="password" id="db_pass" name="db_pass" required>
                </div>

                <button type="submit">🚀 Set Up Database</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
