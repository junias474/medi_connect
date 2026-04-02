<?php
/**
 * Registration Page
 * Medical Consultation Application
 */

require_once 'config.php';

// If the user is already logged in, redirect to their dashboard
if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
}

$error = '';
$success = '';

// Process the registration form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = sanitize($_POST['nom'] ?? '');
    $prenom = sanitize($_POST['prenom'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $telephone = sanitize($_POST['telephone'] ?? '');
    $genre = sanitize($_POST['genre'] ?? '');
    $date_naissance = sanitize($_POST['date_naissance'] ?? '');
    $ville = sanitize($_POST['ville'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    $confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
    $role = sanitize($_POST['role'] ?? 'patient');
    
    // Field validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($telephone) || empty($genre) || empty($mot_de_passe)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "The email address is not valid.";
    } elseif (strlen($mot_de_passe) < 6) {
        $error = "The password must contain at least 6 characters.";
    } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
        $error = "Passwords do not match.";
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Check if email already exists
            $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetch()) {
                $error = "This email address is already in use.";
            } else {
                // Check if phone already exists
                $stmt = $db->prepare("SELECT id FROM utilisateurs WHERE telephone = :telephone");
                $stmt->execute([':telephone' => $telephone]);
                if ($stmt->fetch()) {
                    $error = "This phone number is already in use.";
                } else {
                    // Hash the password
                    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
                    
                    // Start a transaction
                    $db->beginTransaction();
                    
                    try {
                        // Insert the user
                        $stmt = $db->prepare("
                            INSERT INTO utilisateurs 
                            (nom, prenom, email, telephone, mot_de_passe, genre, role, date_naissance, ville, statut)
                            VALUES 
                            (:nom, :prenom, :email, :telephone, :mot_de_passe, :genre, :role, :date_naissance, :ville, 'actif')
                        ");
                        
                        $stmt->execute([
                            ':nom' => $nom,
                            ':prenom' => $prenom,
                            ':email' => $email,
                            ':telephone' => $telephone,
                            ':mot_de_passe' => $mot_de_passe_hash,
                            ':genre' => $genre,
                            ':role' => $role,
                            ':date_naissance' => !empty($date_naissance) ? $date_naissance : null,
                            ':ville' => !empty($ville) ? $ville : null
                        ]);
                        
                        $utilisateur_id = $db->lastInsertId();
                        
                        // Create profile based on role
                        if ($role === 'patient') {
                            $groupe_sanguin = sanitize($_POST['groupe_sanguin'] ?? '');
                            
                            $stmt = $db->prepare("
                                INSERT INTO patients (utilisateur_id, groupe_sanguin)
                                VALUES (:utilisateur_id, :groupe_sanguin)
                            ");
                            $stmt->execute([
                                ':utilisateur_id' => $utilisateur_id,
                                ':groupe_sanguin' => !empty($groupe_sanguin) ? $groupe_sanguin : null
                            ]);
                            
                        } elseif ($role === 'medecin') {
                            $specialite = sanitize($_POST['specialite'] ?? '');
                            $numero_ordre = sanitize($_POST['numero_ordre'] ?? '');
                            $annees_experience = intval($_POST['annees_experience'] ?? 0);
                            
                            if (empty($specialite) || empty($numero_ordre)) {
                                throw new Exception("Specialty and order number are required for doctors.");
                            }
                            
                            // Check if order number already exists
                            $checkStmt = $db->prepare("SELECT id FROM medecins WHERE numero_ordre = :numero_ordre");
                            $checkStmt->execute([':numero_ordre' => $numero_ordre]);
                            if ($checkStmt->fetch()) {
                                throw new Exception("This order number is already in use.");
                            }
                            
                            $stmt = $db->prepare("
                                INSERT INTO medecins 
                                (utilisateur_id, specialite, numero_ordre, annees_experience)
                                VALUES 
                                (:utilisateur_id, :specialite, :numero_ordre, :annees_experience)
                            ");
                            $stmt->execute([
                                ':utilisateur_id' => $utilisateur_id,
                                ':specialite' => $specialite,
                                ':numero_ordre' => $numero_ordre,
                                ':annees_experience' => $annees_experience
                            ]);
                        }
                        
                        // Commit the transaction
                        $db->commit();
                        
                        $success = "Your account has been created successfully! You can now log in.";
                        
                        // Redirect after 2 seconds
                        header("refresh:2;url=login.php");
                        
                    } catch(Exception $e) {
                        // Roll back the transaction on error
                        $db->rollBack();
                        $error = $e->getMessage();
                    }
                }
            }
            
        } catch(PDOException $e) {
            $error = "An error occurred during registration. Please try again.";
            error_log("Registration error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --danger-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .register-container {
            max-width: 700px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .register-header i { font-size: 40px; margin-bottom: 10px; }
        .register-header h2 { margin: 0; font-size: 28px; font-weight: 600; }
        .register-body { padding: 40px 30px; }
        
        .form-label { font-weight: 600; color: #333; margin-bottom: 8px; }
        .form-label .required { color: #dc3545; }
        
        .form-control, .form-select {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-register {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
            color: white;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .alert { border-radius: 10px; margin-bottom: 20px; }
        
        .role-selector { display: flex; gap: 15px; margin-bottom: 30px; }
        
        .role-option {
            flex: 1;
            text-align: center;
            padding: 20px;
            border: 3px solid #e0e0e0;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-option:hover { border-color: #667eea; }
        .role-option.active { border-color: #667eea; background: #f8f9ff; }
        .role-option i { font-size: 35px; margin-bottom: 10px; color: #667eea; }
        .role-option input[type="radio"] { display: none; }
        .role-option h5 { margin: 0; font-weight: 600; }
        
        .doctor-fields {
            display: none;
            padding: 20px;
            background: #f8f9ff;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .doctor-fields.show { display: block; }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        .position-relative { position: relative; }
        
        .login-link { text-align: center; margin-top: 20px; color: #666; }
        .login-link a { color: #667eea; text-decoration: none; font-weight: 600; }
        .login-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="register-container">
            <div class="register-card">
                <div class="register-header">
                    <i class="fas fa-user-plus"></i>
                    <h2>Create an Account</h2>
                    <p class="mb-0">Join our healthcare platform</p>
                </div>
                
                <div class="register-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="registerForm">
                        <!-- Role selection -->
                        <div class="mb-4">
                            <label class="form-label">
                                <i class="fas fa-users"></i> I am registering as <span class="required">*</span>
                            </label>
                            <div class="role-selector">
                                <label class="role-option active">
                                    <input type="radio" name="role" value="patient" checked>
                                    <i class="fas fa-user"></i>
                                    <h5>Patient</h5>
                                </label>
                                <label class="role-option">
                                    <input type="radio" name="role" value="medecin">
                                    <i class="fas fa-user-md"></i>
                                    <h5>Doctor</h5>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Personal information -->
                        <h5 class="mb-3"><i class="fas fa-id-card"></i> Personal Information</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="nom" class="form-label">Last Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="nom" name="nom" required
                                       value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="prenom" class="form-label">First Name <span class="required">*</span></label>
                                <input type="text" class="form-control" id="prenom" name="prenom" required
                                       value="<?php echo isset($_POST['prenom']) ? htmlspecialchars($_POST['prenom']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="required">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="telephone" class="form-label">Phone <span class="required">*</span></label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" 
                                       placeholder="+237XXXXXXXXX" required
                                       value="<?php echo isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : ''; ?>">
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="genre" class="form-label">Gender <span class="required">*</span></label>
                                <select class="form-select" id="genre" name="genre" required>
                                    <option value="">Select</option>
                                    <option value="Homme" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Homme') ? 'selected' : ''; ?>>Male</option>
                                    <option value="Femme" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Femme') ? 'selected' : ''; ?>>Female</option>
                                    <option value="Autre" <?php echo (isset($_POST['genre']) && $_POST['genre'] === 'Autre') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="date_naissance" class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" id="date_naissance" name="date_naissance"
                                       value="<?php echo isset($_POST['date_naissance']) ? htmlspecialchars($_POST['date_naissance']) : ''; ?>">
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <label for="ville" class="form-label">City</label>
                                <input type="text" class="form-control" id="ville" name="ville"
                                       value="<?php echo isset($_POST['ville']) ? htmlspecialchars($_POST['ville']) : ''; ?>">
                            </div>
                        </div>
                        
                        <!-- Patient-specific fields -->
                        <div id="patientFields" class="mb-3">
                            <label for="groupe_sanguin" class="form-label">Blood Type</label>
                            <select class="form-select" id="groupe_sanguin" name="groupe_sanguin">
                                <option value="">Select (optional)</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                            </select>
                        </div>
                        
                        <!-- Doctor-specific fields -->
                        <div id="doctorFields" class="doctor-fields">
                            <h6 class="mb-3"><i class="fas fa-stethoscope"></i> Professional Information</h6>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="specialite" class="form-label">Specialty <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="specialite" name="specialite"
                                           placeholder="e.g. General Medicine, Cardiology..."
                                           value="<?php echo isset($_POST['specialite']) ? htmlspecialchars($_POST['specialite']) : ''; ?>">
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="numero_ordre" class="form-label">Order Number <span class="required">*</span></label>
                                    <input type="text" class="form-control" id="numero_ordre" name="numero_ordre"
                                           placeholder="e.g. ORD-CM-12345"
                                           value="<?php echo isset($_POST['numero_ordre']) ? htmlspecialchars($_POST['numero_ordre']) : ''; ?>">
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="annees_experience" class="form-label">Years of Experience</label>
                                <input type="number" class="form-control" id="annees_experience" name="annees_experience" 
                                       min="0" value="0">
                            </div>
                        </div>
                        
                        <!-- Password -->
                        <h5 class="mb-3 mt-4"><i class="fas fa-lock"></i> Security</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mot_de_passe" class="form-label">Password <span class="required">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="mot_de_passe" name="mot_de_passe" 
                                           required minlength="6" placeholder="Min. 6 characters">
                                    <i class="fas fa-eye password-toggle" id="togglePassword1"></i>
                                </div>
                                <small class="text-muted">At least 6 characters</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirmer_mot_de_passe" class="form-label">Confirm Password <span class="required">*</span></label>
                                <div class="position-relative">
                                    <input type="password" class="form-control" id="confirmer_mot_de_passe" 
                                           name="confirmer_mot_de_passe" required minlength="6">
                                    <i class="fas fa-eye password-toggle" id="togglePassword2"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="acceptTerms" required>
                            <label class="form-check-label" for="acceptTerms">
                                I accept the <a href="#" target="_blank">terms of use</a> and the 
                                <a href="#" target="_blank">privacy policy</a>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-register">
                            <i class="fas fa-user-plus"></i> Create My Account
                        </button>
                    </form>
                    
                    <div class="login-link">
                        <p class="mb-2">Already have an account?</p>
                        <a href="login.php">
                            <i class="fas fa-sign-in-alt"></i> Sign In
                        </a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-home"></i> Back to home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Role management
        const roleOptions = document.querySelectorAll('.role-option');
        const patientFields = document.getElementById('patientFields');
        const doctorFields = document.getElementById('doctorFields');
        
        roleOptions.forEach(option => {
            option.addEventListener('click', function() {
                roleOptions.forEach(opt => opt.classList.remove('active'));
                this.classList.add('active');
                
                const role = this.querySelector('input').value;
                
                if (role === 'medecin') {
                    patientFields.style.display = 'none';
                    doctorFields.classList.add('show');
                    document.getElementById('specialite').setAttribute('required', 'required');
                    document.getElementById('numero_ordre').setAttribute('required', 'required');
                } else {
                    patientFields.style.display = 'block';
                    doctorFields.classList.remove('show');
                    document.getElementById('specialite').removeAttribute('required');
                    document.getElementById('numero_ordre').removeAttribute('required');
                }
            });
        });
        
        // Toggle password visibility
        document.getElementById('togglePassword1').addEventListener('click', function() {
            togglePasswordVisibility('mot_de_passe', this);
        });
        
        document.getElementById('togglePassword2').addEventListener('click', function() {
            togglePasswordVisibility('confirmer_mot_de_passe', this);
        });
        
        function togglePasswordVisibility(inputId, icon) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
        
        // Form validation
        document.getElementById('registerForm').addEventListener('submit', function(e) {
            const password = document.getElementById('mot_de_passe').value;
            const confirmPassword = document.getElementById('confirmer_mot_de_passe').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('The password must contain at least 6 characters.');
                return false;
            }
            
            const role = document.querySelector('input[name="role"]:checked').value;
            if (role === 'medecin') {
                const specialite = document.getElementById('specialite').value;
                const orderNumber = document.getElementById('numero_ordre').value;
                
                if (!specialite || !orderNumber) {
                    e.preventDefault();
                    alert('Please fill in all required fields for doctors.');
                    return false;
                }
            }
        });
        
        // Format phone number
        document.getElementById('telephone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (!value.startsWith('237') && value.length > 0) {
                value = '237' + value;
            }
            if (value.length > 0) {
                e.target.value = '+' + value;
            }
        });
    </script>
</body>
</html>