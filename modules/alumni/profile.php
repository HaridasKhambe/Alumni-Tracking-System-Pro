<?php
session_start();

// make sure that user is set & role is correct
require_once '../../config/auth_helper.php';
requireRole('alumni');

// Page configuration
$page_title = "Alumni Dashboard - ATS";
$current_page = "profile";

// Include templates
include_once '../../templates/header.php'; //navbar global style
include_once '../../templates/navbar_alumni.php'; //navbar for alumini
?>
<!-- ---------------------------------------------------------------------------------------------------------  -->
<style>
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
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-update {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            padding: 12px 30px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .btn-update:hover {
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
        
        .password-divider {
            border-top: 2px solid #e9ecef;
            margin: 2rem 0 1.5rem 0;
            position: relative;
        }
        
        .password-divider::after {
            content: "Optional Password Change";
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: white;
            padding: 0 1rem;
            color: #6c757d;
            font-size: 0.9rem;
        }
    </style>

    <div class="container p-4">
        <div class="registration-container">
            <div class="registration-card">
                <!-- Header -->
                <div class="header-section">
                    <i class="fas fa-user-edit"></i>
                    <h2>Update Profile</h2>
                    <p class="text-muted">Update your profile information</p>
                </div>

                <!-- Error/Success Messages -->
                <div id="message-area"></div>

                <!-- Update Form -->
                <form id="updateForm">
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
                                <input type="text" class="form-control" id="prn_no" name="prn_no" required>
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

                    <!-- Employment Information Section -->
                    <div class="form-section" id="employment-section" style="display: none;">
                        <h5 class="section-title">
                            <i class="fas fa-briefcase me-2"></i>Employment Information
                        </h5>
                        
                        <div class="mb-3">
                            <label for="company_name" class="form-label">Company Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-update">
                        <i class="fas fa-save me-2"></i>Update Profile
                    </button>
                </form>

                <!-- Password Change Section -->
                <div class="password-divider"></div>
                
                <form id="passwordForm">
                    <div class="form-section">
                        <h5 class="section-title">
                            <i class="fas fa-key me-2"></i>Change Password
                        </h5>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label">New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="new_password" 
                                           name="new_password" minlength="8">
                                </div>
                                <small class="text-muted">Minimum 8 characters</small>
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_new_password" class="form-label">Confirm New Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_new_password" 
                                           name="confirm_new_password" minlength="8">
                                </div>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-warning btn-update">
                        <i class="fas fa-key me-2"></i>Change Password
                    </button>
                </form>
                
                <div class="text-center mt-3">
                    <a href="../alumni/dashboard.php" class="btn-back">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- script -->
      <script>
        // Load user data on page load
        document.addEventListener('DOMContentLoaded', async function() {
            await loadUserProfile();
        });

        // Load user profile data
        async function loadUserProfile() {
            try {
                const res = await fetch('../../api/alumni_profile.php?action=get_profile', {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server did not return JSON response');
                }

                const data = await res.json();

                if (res.ok && data.success) {
                    populateForm(data.profile);
                } else {
                    showMessage(data.message || 'Failed to load profile', 'danger');
                }
            } catch (err) {
                console.error('Load profile error:', err);
                showMessage('Failed to load profile data: ' + err.message, 'danger');
            }
        }

        // Populate form with user data
        function populateForm(profile) {
            document.getElementById('first_name').value = profile.first_name || '';
            document.getElementById('last_name').value = profile.last_name || '';
            document.getElementById('email').value = profile.email || '';
            document.getElementById('phone').value = profile.phone || '';
            document.getElementById('prn_no').value = profile.prn_no || '';
            document.getElementById('branch').value = profile.branch || '';
            document.getElementById('passout_year').value = profile.passout_year || '';
            document.getElementById('employment_status').value = profile.employment_status || '';
            document.getElementById('company_name').value = profile.company_name || '';
            
            // Show employment section if applicable
            if (profile.employment_status === 'employed' || profile.employment_status === 'self-employed') {
                document.getElementById('employment-section').style.display = 'block';
            }
        }

        // Show/hide employment section based on status
        document.getElementById('employment_status').addEventListener('change', function() {
            const employmentSection = document.getElementById('employment-section');
            if (this.value === 'employed' || this.value === 'self-employed') {
                employmentSection.style.display = 'block';
            } else {
                employmentSection.style.display = 'none';
            }
        });

        // Handle profile update form submission
        document.getElementById('updateForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const payload = {
                first_name: document.getElementById('first_name').value.trim(),
                last_name: document.getElementById('last_name').value.trim(),
                email: document.getElementById('email').value.trim(),
                phone: document.getElementById('phone').value.trim(),
                prn_no: document.getElementById('prn_no').value.trim(),
                branch: document.getElementById('branch').value,
                passout_year: document.getElementById('passout_year').value,
                employment_status: document.getElementById('employment_status').value,
                company_name: document.getElementById('company_name').value.trim()
            };

            showMessage('Updating profile...', 'info');

            try {
                const res = await fetch('../../api/alumni_profile.php?action=update_profile', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server did not return JSON response');
                }

                const data = await res.json();

                if (res.ok && data.success) {
                    showMessage(data.message, 'success');
                } else {
                    if (data.errors && Array.isArray(data.errors)) {
                        showMessage(data.errors.join('<br/>'), 'danger');
                    } else {
                        showMessage(data.message || 'Profile update failed', 'danger');
                    }
                }
            } catch (err) {
                console.error('Update profile error:', err);
                showMessage('Network error. Please try again. Error: ' + err.message, 'danger');
            }
        });

        // Handle password change form submission
        document.getElementById('passwordForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const currentPass = document.getElementById('current_password').value;
            const newPass = document.getElementById('new_password').value;
            const confirmPass = document.getElementById('confirm_new_password').value;
            
            if (!currentPass || !newPass || !confirmPass) {
                showMessage('Please fill all password fields', 'danger');
                return;
            }
            
            if (newPass !== confirmPass) {
                showMessage('New passwords do not match', 'danger');
                return;
            }

            const payload = {
                current_password: currentPass,
                new_password: newPass,
                confirm_new_password: confirmPass
            };

            showMessage('Changing password...', 'info');
            
            try {
                const res = await fetch('../../api/alumni_profile.php?action=change_password', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    throw new Error('Server did not return JSON response');
                }

                const data = await res.json();

                if (res.ok && data.success) {
                    showMessage(data.message, 'success');
                    document.getElementById('passwordForm').reset();
                } else {
                    if (data.errors && Array.isArray(data.errors)) {
                        showMessage(data.errors.join('<br/>'), 'danger');
                    } else {
                        showMessage(data.message || 'Password change failed', 'danger');
                    }
                }
            } catch (err) {
                console.error('Change password error:', err);
                showMessage('Network error. Please try again. Error: ' + err.message, 'danger');
            }
        });

        // Show message function
        function showMessage(message, type) {
            const messageArea = document.getElementById('message-area');
            messageArea.innerHTML = `<div class="alert alert-${type}">${message}</div>`;
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(() => {
                    messageArea.innerHTML = '';
                }, 3000);
            }
        }
    </script>
<!-- ---------------------------------------------------------------------------------------------------------  -->

<?php include_once '../../templates/footer.php'; ?>