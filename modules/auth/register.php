<?php
session_start();
$success_message = $_SESSION['success_message'] ?? '';
unset($_SESSION['success_message']);

$errors = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Registration - ATS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .registration-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .registration-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            padding: 2.5rem;
        }
        
        .header-section {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .header-section i {
            font-size: 2.5rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .form-section {
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #667eea;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            /* margin-bottom: 1rem; */
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-back {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .btn-back:hover {
            color: #5a6fd8;
            text-decoration: none;
        }
        
        .required {
            color: #dc3545;
        }
        
        .alert {
            border-radius: 8px;
            border: none;
        }
        
        .input-group-text {
            border-radius: 8px 0 0 8px;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-container">
            <div class="registration-card">
                <!-- Header -->
                <div class="header-section">
                    <i class="fas fa-user-graduate"></i>
                    <h2>Alumni Registration</h2>
                    <p class="text-muted">Join our alumni network and stay connected</p>
                </div>

                <!-- Error/Success Messages -->
                <div id="message-area">
                    <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = '../../index.php';
                                }, 4000);
                            </script>
                        <?php endif; ?>

                        <?php foreach ($errors as $error): ?>
                            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; 
                    ?>
                </div>

                <!-- Registration Form -->
                <form id="registrationForm" method="POST" >
                    
                    <!-- Personal Information Section -->
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-user me-2"></i>Personal Information
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">
                                    First Name <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">
                                    Last Name <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="email" class="form-label">
                                    Email Address <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="phone" class="form-label">
                                    Phone Number <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                    <input type="tel" class="form-control" id="phone" name="phone" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Academic Information Section -->
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-graduation-cap me-2"></i>Academic Information
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="prn_no" class="form-label">
                                    PRN Number <span class="required">*</span>
                                </label>
                                <input type="text" class="form-control" id="prn_no" name="prn_no" 
                                       placeholder="Enter your PRN number" required>
                            </div>
                            <div class="col-md-6">
                                <label for="branch" class="form-label">
                                    Branch/Department <span class="required">*</span>
                                </label>
                                <select class="form-select" id="branch" name="branch" required>
                                    <option value="">Select Branch</option>
                                    <option value="Computer Science & Engineering">Computer Science & Engineering</option>
                                    <option value="Information Technology">Information Technology</option>
                                    <option value="Electronics & Communication">Electronics & Communication</option>
                                    <option value="Mechanical Engineering">Mechanical Engineering</option>
                                    <option value="Civil Engineering">Civil Engineering</option>
                                    <option value="Electrical Engineering">Electrical Engineering</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="passout_year" class="form-label">
                                    Passout Year <span class="required">*</span>
                                </label>
                                <select class="form-select" id="passout_year" name="passout_year" required>
                                    <option value="">Select Year</option>
                                    <script>
                                        // Generate years from 2010 to current year
                                        const currentYear = new Date().getFullYear();
                                        for (let year = currentYear; year >= 2010; year--) {
                                            document.write(`<option value="${year}">${year}</option>`);
                                        }
                                    </script>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="employment_status" class="form-label">
                                    Employment Status <span class="required">*</span>
                                </label>
                                <select class="form-select" id="employment_status" name="employment_status" required>
                                    <option value="">Select Status</option>
                                    <option value="employed">Employed</option>
                                    <option value="unemployed">Unemployed</option>
                                    <option value="self-employed">Self Employed</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Information Section (Optional) -->
                    <div class="form-section" id="employment-section" style="display: none;">
                        <h5 class="section-title">
                            <i class="fas fa-briefcase me-2"></i>Employment Information
                        </h5>
                        
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name" 
                                   placeholder="Enter your current company name">
                        </div>
                    </div>

                    <!-- Account Security Section -->
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-shield-alt me-2"></i>Account Security
                        </h5>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="password" class="form-label">
                                    Password <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           minlength="8" required>
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label">
                                    Confirm Password <span class="required">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" 
                                           name="confirm_password" minlength="8" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Terms and Submit -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                        <label class="form-check-label" for="terms">
                            I agree to the <a href="#" class="text-decoration-none">Terms and Conditions</a> 
                            and <a href="#" class="text-decoration-none">Privacy Policy</a> <span class="required">*</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-register">
                        <i class="fas fa-user-plus me-2"></i>Register Now
                    </button>
                    
                    <div class="text-center mt-3">
                        <a href="../../index.php" class="btn-back">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                    </div>
                </form>
            </div>

            <!-- Info Note -->
            <div class="text-center mt-4">
                <small style="color: rgba(255,255,255,0.8);">
                    <i class="fas fa-info-circle me-1"></i>
                    Your registration will be reviewed by college administrators before activation.
                </small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide employment section based on employment status
        document.getElementById('employment_status').addEventListener('change', function() {
            const employmentSection = document.getElementById('employment-section');
            const companyField = document.getElementById('company_name');
            
            if (this.value === 'employed' || this.value === 'self-employed') {
                employmentSection.style.display = 'block';
                companyField.required = true;
            } else {
                employmentSection.style.display = 'none';
                companyField.required = false;
                companyField.value = '';
            }
        });

        // // Form validation
        // document.getElementById('registrationForm').addEventListener('submit', function(e) {
        //     e.preventDefault();
            
        //     // Basic validation
        //     if (!validateForm()) {
        //         return;
        //     }
            
        //     // Submit form
        //     this.submit();
        // });

        function validateForm() {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const email = document.getElementById('email').value;
            const phone = document.getElementById('phone').value;
            
            // Password validation
            if (password !== confirmPassword) {
                showMessage('Passwords do not match!', 'danger');
                return false;
            }
            
            if (password.length < 8) {
                showMessage('Password must be at least 8 characters long!', 'danger');
                return false;
            }
            
            // Email validation
            if (!isValidEmail(email)) {
                showMessage('Please enter a valid email address!', 'danger');
                return false;
            }
            
            // Phone validation
            if (!isValidPhone(phone)) {
                showMessage('Please enter a valid phone number!', 'danger');
                return false;
            }
            
            return true;
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        function isValidPhone(phone) {
            const phoneRegex = /^[\d\s\-\+\(\)]{10,}$/;
            return phoneRegex.test(phone);
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
            messageArea.scrollIntoView({ behavior: 'smooth' });
        }

        // Real-time validation feedback
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword && password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#ced4da';
            }
        });
    </script>

    <script>
            // Fixed JavaScript for registration form
            document.getElementById('registrationForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                if (!validateForm()) return;

                const payload = {
                    first_name: document.getElementById('first_name').value.trim(),
                    last_name: document.getElementById('last_name').value.trim(),
                    email: document.getElementById('email').value.trim(),
                    phone: document.getElementById('phone').value.trim(),
                    prn_no: document.getElementById('prn_no').value.trim(),
                    branch: document.getElementById('branch').value,
                    passout_year: document.getElementById('passout_year').value,
                    employment_status: document.getElementById('employment_status').value,
                    company_name: document.getElementById('company_name').value.trim(),
                    password: document.getElementById('password').value,
                    confirm_password: document.getElementById('confirm_password').value
                };

                showMessage('Registering...', 'info');

                try {
                    // FIX 1: Correct the URL - remove the leading slash before http
                    const res = await fetch('../../api/auth.php?action=register', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                        // FIX 2: Remove credentials for now to avoid CORS issues
                    });

                    // FIX 3: Check if response is actually JSON
                    const contentType = res.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Server did not return JSON response');
                    }

                    const data = await res.json();

                    if (res.ok && data.success) {
                        showMessage(data.message, 'success');
                        setTimeout(() => window.location.href = '../../index.php', 2500);
                        return;
                    }

                    // Show errors
                    if (data.errors && Array.isArray(data.errors)) {
                        showMessage(data.errors.join('<br/>'), 'danger');
                    } else {
                        showMessage(data.message || 'Registration failed', 'danger');
                    }
                } catch (err) {
                    console.error('Registration error:', err);
                    showMessage('Network error. Please try again. Error: ' + err.message, 'danger');
                }
            });
    </script>

</body>
</html>