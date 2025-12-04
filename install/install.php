<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flipbook Plugin - Installation Wizard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .installer-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 32px;
            color: #667eea;
            margin-bottom: 10px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            z-index: 1;
            background: white;
            padding: 0 10px;
        }

        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            color: #999;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .step.active .step-number {
            background: #667eea;
            color: white;
        }

        .step.completed .step-number {
            background: #4caf50;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #666;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }

        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }

        .button-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        button {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
        }

        button.primary {
            background: #667eea;
            color: white;
        }

        button.primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        button.secondary {
            background: #e0e0e0;
            color: #333;
        }

        button.secondary:hover {
            background: #d0d0d0;
        }

        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert.warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .requirements-list {
            list-style: none;
            margin-top: 15px;
        }

        .requirements-list li {
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .requirements-list li.pass {
            background: #d4edda;
            color: #155724;
        }

        .requirements-list li.fail {
            background: #f8d7da;
            color: #721c24;
        }

        .hidden {
            display: none;
        }

        .success-icon {
            font-size: 64px;
            text-align: center;
            margin-bottom: 20px;
        }

        .completion-message {
            text-align: center;
        }

        .completion-message h2 {
            color: #4caf50;
            margin-bottom: 15px;
        }

        .completion-message p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .next-steps {
            background: #f5f5f5;
            padding: 20px;
            border-radius: 6px;
            margin-top: 20px;
            text-align: left;
        }

        .next-steps h3 {
            margin-bottom: 15px;
            color: #333;
        }

        .next-steps ol {
            margin-left: 20px;
            line-height: 1.8;
        }
    </style>
</head>
<body>
    <div class="installer-container">
        <div class="logo">
            <h1>📚 Flipbook Plugin</h1>
            <p>Professional flipbook system with audio support</p>
        </div>

        <div class="step-indicator">
            <div class="step active" data-step="1">
                <div class="step-number">1</div>
                <div class="step-label">Requirements</div>
            </div>
            <div class="step" data-step="2">
                <div class="step-number">2</div>
                <div class="step-label">Database</div>
            </div>
            <div class="step" data-step="3">
                <div class="step-number">3</div>
                <div class="step-label">Admin</div>
            </div>
            <div class="step" data-step="4">
                <div class="step-number">4</div>
                <div class="step-label">Complete</div>
            </div>
        </div>

        <!-- Step 1: Requirements Check -->
        <div id="step-1" class="step-content">
            <h2>System Requirements</h2>
            <p style="margin: 15px 0; color: #666;">Checking if your system meets the requirements...</p>
            <ul class="requirements-list" id="requirements-list">
                <!-- Populated by JavaScript -->
            </ul>
            <div class="button-group">
                <button class="primary" onclick="checkRequirements()">Check Requirements</button>
            </div>
        </div>

        <!-- Step 2: Database Configuration -->
        <div id="step-2" class="step-content hidden">
            <h2>Database Configuration</h2>
            <p style="margin: 15px 0; color: #666;">Enter your database connection details</p>

            <div id="db-error" class="alert error hidden"></div>

            <form id="db-form">
                <div class="form-group">
                    <label>Database Host</label>
                    <input type="text" name="db_host" value="localhost" required>
                    <small>Usually "localhost" for most servers</small>
                </div>

                <div class="form-group">
                    <label>Database Name</label>
                    <input type="text" name="db_name" required>
                    <small>The name of your MySQL database</small>
                </div>

                <div class="form-group">
                    <label>Database User</label>
                    <input type="text" name="db_user" required>
                    <small>Your MySQL username</small>
                </div>

                <div class="form-group">
                    <label>Database Password</label>
                    <input type="password" name="db_pass">
                    <small>Your MySQL password (leave blank if none)</small>
                </div>

                <div class="form-group">
                    <label>Table Prefix</label>
                    <input type="text" name="table_prefix" value="flipbook_">
                    <small>Prefix for all database tables (e.g., "flipbook_")</small>
                </div>

                <div class="button-group">
                    <button type="button" class="secondary" onclick="previousStep()">Back</button>
                    <button type="submit" class="primary">Test Connection</button>
                </div>
            </form>
        </div>

        <!-- Step 3: Admin Configuration -->
        <div id="step-3" class="step-content hidden">
            <h2>Admin Account</h2>
            <p style="margin: 15px 0; color: #666;">Create your administrator account</p>

            <form id="admin-form">
                <div class="form-group">
                    <label>Admin Username</label>
                    <input type="text" name="admin_user" value="admin" required>
                    <small>Username for admin login</small>
                </div>

                <div class="form-group">
                    <label>Admin Password</label>
                    <input type="password" name="admin_pass" required minlength="8">
                    <small>Minimum 8 characters (use a strong password!)</small>
                </div>

                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="admin_pass_confirm" required minlength="8">
                    <small>Re-enter your password</small>
                </div>

                <div class="button-group">
                    <button type="button" class="secondary" onclick="previousStep()">Back</button>
                    <button type="submit" class="primary">Install</button>
                </div>
            </form>
        </div>

        <!-- Step 4: Completion -->
        <div id="step-4" class="step-content hidden">
            <div class="completion-message">
                <div class="success-icon">✅</div>
                <h2>Installation Complete!</h2>
                <p>Your Flipbook plugin has been successfully installed and configured.</p>

                <div class="next-steps">
                    <h3>Next Steps:</h3>
                    <ol>
                        <li><strong>Delete install folder</strong> - For security, delete the /install/ directory</li>
                        <li><strong>Login to admin</strong> - <a href="../src/admin/login.php" target="_blank">Go to Admin Panel</a></li>
                        <li><strong>Create your first flipbook</strong> - Upload a PDF and start creating!</li>
                        <li><strong>Read the documentation</strong> - Check /docs/ for integration guides</li>
                    </ol>
                </div>

                <div class="button-group">
                    <button class="primary" onclick="window.location.href='../src/admin/login.php'">Go to Admin Panel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let installData = {};

        // Check requirements
        function checkRequirements() {
            const list = document.getElementById('requirements-list');
            list.innerHTML = '<li>Checking...</li>';

            fetch('?action=check_requirements')
                .then(res => res.json())
                .then(data => {
                    list.innerHTML = '';
                    let allPass = true;

                    data.checks.forEach(check => {
                        const li = document.createElement('li');
                        li.className = check.pass ? 'pass' : 'fail';
                        li.innerHTML = `<span>${check.pass ? '✓' : '✗'}</span> ${check.message}`;
                        list.appendChild(li);

                        if (!check.pass) allPass = false;
                    });

                    if (allPass) {
                        setTimeout(() => nextStep(), 1000);
                    }
                })
                .catch(err => {
                    list.innerHTML = '<li class="fail">Error checking requirements</li>';
                });
        }

        // Handle database form
        document.getElementById('db-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            formData.append('action', 'test_database');

            document.getElementById('db-error').classList.add('hidden');

            fetch('', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    // Store database config
                    installData.database = Object.fromEntries(formData);
                    nextStep();
                } else {
                    const errorDiv = document.getElementById('db-error');
                    errorDiv.textContent = data.message;
                    errorDiv.classList.remove('hidden');
                }
            })
            .catch(err => {
                const errorDiv = document.getElementById('db-error');
                errorDiv.textContent = 'Error testing database connection';
                errorDiv.classList.remove('hidden');
            });
        });

        // Handle admin form
        document.getElementById('admin-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const pass = formData.get('admin_pass');
            const passConfirm = formData.get('admin_pass_confirm');

            if (pass !== passConfirm) {
                alert('Passwords do not match!');
                return;
            }

            // Combine all install data
            const installFormData = new FormData();
            installFormData.append('action', 'install');

            // Add database config
            Object.entries(installData.database).forEach(([key, value]) => {
                installFormData.append(key, value);
            });

            // Add admin config
            installFormData.append('admin_user', formData.get('admin_user'));
            installFormData.append('admin_pass', formData.get('admin_pass'));

            fetch('', {
                method: 'POST',
                body: installFormData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    nextStep();
                } else {
                    alert('Installation failed: ' + data.message);
                }
            })
            .catch(err => {
                alert('Installation error');
            });
        });

        function nextStep() {
            if (currentStep < 4) {
                document.getElementById('step-' + currentStep).classList.add('hidden');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('completed');

                currentStep++;

                document.getElementById('step-' + currentStep).classList.remove('hidden');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                document.getElementById('step-' + currentStep).classList.add('hidden');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('active');

                currentStep--;

                document.getElementById('step-' + currentStep).classList.remove('hidden');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.remove('completed');
                document.querySelector(`.step[data-step="${currentStep}"]`).classList.add('active');
            }
        }

        // Auto-check requirements on load
        window.addEventListener('load', function() {
            setTimeout(checkRequirements, 500);
        });
    </script>
</body>
</html>

<?php
// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? $_GET['action'] ?? '';

    switch ($action) {
        case 'check_requirements':
            $checks = [
                [
                    'name' => 'PHP Version',
                    'pass' => version_compare(PHP_VERSION, '7.4.0', '>='),
                    'message' => 'PHP ' . PHP_VERSION . ' ' . (version_compare(PHP_VERSION, '7.4.0', '>=') ? '(✓ Required: 7.4+)' : '(✗ Required: 7.4+)')
                ],
                [
                    'name' => 'PDO Extension',
                    'pass' => extension_loaded('pdo') && extension_loaded('pdo_mysql'),
                    'message' => 'PDO MySQL Extension ' . (extension_loaded('pdo') && extension_loaded('pdo_mysql') ? 'Installed' : 'Not Installed')
                ],
                [
                    'name' => 'File Permissions',
                    'pass' => is_writable(__DIR__ . '/..'),
                    'message' => 'Write permission ' . (is_writable(__DIR__ . '/..') ? 'Granted' : 'Denied')
                ],
                [
                    'name' => 'PHP Max Upload',
                    'pass' => true,
                    'message' => 'Max upload size: ' . ini_get('upload_max_filesize') . ' (50MB+ recommended)'
                ]
            ];

            echo json_encode(['checks' => $checks]);
            exit;

        case 'test_database':
            try {
                $host = $_POST['db_host'] ?? 'localhost';
                $name = $_POST['db_name'] ?? '';
                $user = $_POST['db_user'] ?? '';
                $pass = $_POST['db_pass'] ?? '';

                $dsn = "mysql:host=$host;dbname=$name;charset=utf8mb4";
                $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                echo json_encode(['success' => true, 'message' => 'Database connection successful']);
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
            }
            exit;

        case 'install':
            try {
                // Get form data
                $config = [
                    'db_host' => $_POST['db_host'] ?? 'localhost',
                    'db_name' => $_POST['db_name'] ?? '',
                    'db_user' => $_POST['db_user'] ?? '',
                    'db_pass' => $_POST['db_pass'] ?? '',
                    'table_prefix' => $_POST['table_prefix'] ?? 'flipbook_',
                    'admin_user' => $_POST['admin_user'] ?? 'admin',
                    'admin_pass' => password_hash($_POST['admin_pass'] ?? '', PASSWORD_BCRYPT),
                ];

                // Connect to database
                $dsn = "mysql:host={$config['db_host']};dbname={$config['db_name']};charset=utf8mb4";
                $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

                // Read and execute schema
                $schema = file_get_contents(__DIR__ . '/schema.sql');
                $schema = str_replace('{{TABLE_PREFIX}}', $config['table_prefix'], $schema);

                // Execute each statement
                $statements = array_filter(array_map('trim', explode(';', $schema)));
                foreach ($statements as $stmt) {
                    if (!empty($stmt)) {
                        $pdo->exec($stmt);
                    }
                }

                // Create config file
                $configContent = file_get_contents(__DIR__ . '/config.template.php');
                $configContent = str_replace('{{DB_HOST}}', $config['db_host'], $configContent);
                $configContent = str_replace('{{DB_NAME}}', $config['db_name'], $configContent);
                $configContent = str_replace('{{DB_USER}}', $config['db_user'], $configContent);
                $configContent = str_replace('{{DB_PASS}}', $config['db_pass'], $configContent);
                $configContent = str_replace('{{TABLE_PREFIX}}', $config['table_prefix'], $configContent);
                $configContent = str_replace('{{ADMIN_USER}}', $config['admin_user'], $configContent);
                $configContent = str_replace('{{ADMIN_PASS}}', $config['admin_pass'], $configContent);
                $configContent = str_replace('{{UPLOAD_DIR}}', __DIR__ . '/../uploads/', $configContent);
                $configContent = str_replace('{{DEBUG}}', '0', $configContent);

                file_put_contents(__DIR__ . '/../src/config.php', $configContent);

                // Create upload directories
                $uploadDir = __DIR__ . '/../uploads/';
                @mkdir($uploadDir, 0755, true);
                @mkdir($uploadDir . 'pdfs/', 0755, true);
                @mkdir($uploadDir . 'audio/', 0755, true);
                @mkdir($uploadDir . 'pages/', 0755, true);

                echo json_encode(['success' => true, 'message' => 'Installation completed successfully']);
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            exit;
    }
}
?>
