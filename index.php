<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATS - Alumni Tracking System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            box-sizing: border-box;
            padding:0;
            margin:0;
        }
        
        .login-container {
            max-width: 450px;
            margin: 1rem auto;
        }
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2rem;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-section i {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .logo-section h2 {
            color: #333;
            font-weight: 600;
        }
        
        .logo-section p {
            color: #666;
            margin: 0;
        }
        
        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 12px 15px;
            /* margin-bottom: 1rem; */
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .divider {
            text-align: center;
            margin: 1rem 0 0.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #ddd;
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            color: #666;
            z-index: 1;
            position: relative;
        }
        
        .register-section {
            text-align: center;
        }
        
        .btn-register {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            border: 2px solid #667eea;
            padding: 10px 20px;
            border-radius: 10px;
            display: inline-block;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            background: #667eea;
            color: white;
            text-decoration: none;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .college-info {
            text-align: center;
            margin-top: 2rem;
            color: rgba(255,255,255,0.8);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <!-- Logo Section -->
                <div class="logo-section">
                    <i class="fas fa-graduation-cap"></i>
                    <h2>Alumni Tracking System</h2>
                    <p>Welcome back! Please sign in to your account</p>
                </div>

                <!-- Error/Success Messages -->
                <div id="message-area">
                    <!-- Messages will appear here -->
                </div>

                <!-- Login Form -->
                <form id="loginForm" method="POST">
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius: 10px 0 0 10px;">
                                <i class="fas fa-envelope"></i>
                            </span>
                            <input type="email" 
                                   class="form-control" 
                                   id="email" 
                                   name="email" 
                                   placeholder="Enter your email address" 
                                   required
                                   style="border-radius: 0 10px 10px 0;">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="input-group">
                            <span class="input-group-text" style="border-radius: 10px 0 0 10px;">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="password" 
                                   name="password" 
                                   placeholder="Enter your password" 
                                   required
                                   style="border-radius: 0 10px 10px 0;">
                        </div>
                    </div>
                    
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-login">
                        <i class="fas fa-sign-in-alt me-2"></i>Sign In
                    </button>
                </form>

                <!-- Divider -->
                <div class="divider">
                    <span>New Alumni?</span>
                </div>

                <!-- Registration Section -->
                <div class="register-section">
                    <p class="mb-3">Don't have an account yet?</p>
                    <a href="modules/auth/register.php" class="btn-register">
                        <i class="fas fa-user-plus me-2"></i>Register as Alumni
                    </a>
                </div>
            </div>

            <!-- College Info -->
            <div class="college-info">
                <p><small>© 2025 JSPM's RSCOE. All rights reserved.</small></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // // Simple form validation
        // document.getElementById('loginForm').addEventListener('submit', function(e) {
        //     const email = document.getElementById('email').value.trim();
        //     const password = document.getElementById('password').value;
            
        //     if (!email || !password) {
        //         e.preventDefault();
        //         showMessage('Please fill in all fields', 'danger');
        //         return;
        //     }
            
        //     if (!isValidEmail(email)) {
        //         e.preventDefault();
        //         showMessage('Please enter a valid email address', 'danger');
        //         return;
        //     }
        // });

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function showMessage(message, type) {
            const messageArea = document.getElementById('message-area');
            messageArea.innerHTML = `
                <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        }

        // Check for URL parameters & then show(success/error messages)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('registered')) {
            showMessage('Registration successful! Your account is pending approval.', 'success');
        }
        if (urlParams.has('error')) {
            showMessage('Login failed. Please check your credentials.', 'danger');
        }
    </script>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const email = document.getElementById('email').value.trim();
        const password = document.getElementById('password').value;

        // Basic validation
        if (!email || !password) {
            showMessage('Please fill in all fields', 'danger');
            return;
        }

        if (!isValidEmail(email)) {
            showMessage('Please enter a valid email address', 'danger');
            return;
        }

        showMessage('Logging in...', 'info');

        try {
            const res = await fetch('api/auth.php?action=login', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email, password }),
                credentials: 'same-origin'
            });

            const data = await res.json();

            if (res.ok && data.success) {
                showMessage(data.message, 'success');
                // Use the redirect URL from backend response
                setTimeout(() => {
                    window.location.href = data.redirect || 'modules/alumni/dashboard.php';
                }, 1000);
                return;
            }

            showMessage(data.message || 'Login failed', 'danger');

        } catch (err) {
            console.error(err);
            showMessage('Network error. Please try again.', 'danger');
        }
    });
    </script>

</body>
</html>