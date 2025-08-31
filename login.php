
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
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
            --error-color: #ef4444;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --background: #fafafa;
            --surface: #ffffff;
            --text-primary: #111827;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --border: #f3f4f6;
            --border-hover: #e5e7eb;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: var(--background);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            font-size: 14px;
            line-height: 1.5;
            color: var(--text-primary);
        }

        .login-container {
            background: var(--surface);
            border-radius: 16px;
            padding: 2rem;
            box-shadow: var(--shadow);
            width: 100%;
            max-width: 380px;
            border: 1px solid var(--border);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            color: var(--text-primary);
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            letter-spacing: -0.025em;
        }

        .login-header p {
            color: var(--text-muted);
            font-size: 0.875rem;
            font-weight: 400;
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
            padding: 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.15s ease;
            background: var(--surface);
            color: var(--text-primary);
        }

        .form-input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgb(99 102 241 / 0.1);
        }

        .form-input::placeholder {
            color: var(--text-muted);
        }

        .login-btn {
            width: 100%;
            background: var(--primary-color);
            color: white;
            border: none;
            padding: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.15s ease;
            letter-spacing: -0.025em;
        }

        .login-btn:hover {
            background: var(--primary-hover);
            transform: translateY(-1px);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fef2f2;
            border: 1px solid #fed7d7;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            color: var(--error-color);
            font-size: 0.8125rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .security-notice {
            background: #fffbeb;
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 0.75rem;
            margin-bottom: 1.5rem;
            color: #92400e;
            font-size: 0.8125rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }

        .footer-text {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--text-muted);
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
            <h1>Migration System</h1>
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

        <form method="POST" action="login.php">
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
                Sign In
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
