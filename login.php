
<?php
require_once 'auth.php';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    
    if (authenticate($password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid password. Please try again.';
    }
}

// If already authenticated, redirect to main page
if (isAuthenticated() && !isSessionExpired()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SFGS Migration System - Login</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --error-color: #dc2626;
            --background-color: #f8fafc;
            --surface-color: #ffffff;
            --text-primary: #0f172a;
            --text-secondary: #475569;
            --border-color: #e2e8f0;
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .login-container {
            background: var(--surface-color);
            border-radius: 1rem;
            padding: 2.5rem;
            box-shadow: var(--shadow-lg);
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            color: var(--text-primary);
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            font-size: 1rem;
            transition: all 0.2s ease;
            background: var(--background-color);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(37 99 235 / 0.1);
        }

        .login-btn {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.875rem;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .login-btn:hover {
            background: var(--primary-hover);
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
            color: var(--error-color);
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .security-notice {
            background: #fefce8;
            border: 1px solid #fef08a;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1.5rem;
            color: #92400e;
            font-size: 0.875rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-secondary);
            font-size: 0.75rem;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 2rem;
            }
            
            .login-header h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Secure Access</h1>
            <p>SFGS to CBT Migration System</p>
        </div>

        <div class="security-notice">
            <span>⚠️</span>
            <div>
                <strong>Security Notice:</strong><br>
                This system handles sensitive database operations. Auto-logout after 5 minutes of inactivity.
            </div>
        </div>

        <?php if (isset($error)): ?>
        <div class="error-message">
            <span>❌</span>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="password" class="form-label">Master Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    class="form-input" 
                    placeholder="Enter master password" 
                    required 
                    autofocus
                >
            </div>

            <button type="submit" class="login-btn">
                🚀 Access Migration System
            </button>
        </form>

        <div class="footer-text">
            Authorized access only • Session timeout: 5 minutes
        </div>
    </div>

    <script>
        // Auto-focus password field
        document.getElementById('password').focus();
        
        // Clear any stored form data on page load
        window.onload = function() {
            if (window.history && window.history.pushState) {
                window.history.pushState('', null, './');
            }
        };
    </script>
</body>
</html>
