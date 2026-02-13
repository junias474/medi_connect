<?php
/**
 * Page de connexion - VERSION DÉFINITIVEMENT CORRIGÉE
 * Application de Consultation Médicale
 */

require_once 'config.php';

// Si l'utilisateur est déjà connecté, le rediriger vers son dashboard
if (isLoggedIn()) {
    redirectToDashboard($_SESSION['user_role']);
}

$error = '';
$success = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifiant = sanitize($_POST['identifiant'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';
    
    if (empty($identifiant) || empty($mot_de_passe)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        try {
            $db = Database::getInstance()->getConnection();
            
            // Rechercher l'utilisateur par email ou téléphone
            $stmt = $db->prepare("
                SELECT id, nom, prenom, email, telephone, mot_de_passe, role, statut
                FROM utilisateurs
                WHERE (email = ? OR telephone = ?)
                LIMIT 1
            ");
            
            $stmt->execute([$identifiant, $identifiant]);
            $user = $stmt->fetch();
            
            if ($user) {
                // Vérifier le statut du compte
                if ($user['statut'] !== 'actif') {
                    $error = "Votre compte est " . $user['statut'] . ". Veuillez contacter l'administrateur.";
                } 
                // Vérifier le mot de passe
                elseif (password_verify($mot_de_passe, $user['mot_de_passe'])) {
                    
                    // Mettre à jour la dernière connexion
                    $updateStmt = $db->prepare("UPDATE utilisateurs SET derniere_connexion = NOW() WHERE id = ?");
                    $updateStmt->execute([$user['id']]);
                    
                    // Enregistrer les informations de session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_nom'] = $user['nom'];
                    $_SESSION['user_prenom'] = $user['prenom'];
                    $_SESSION['user_email'] = $user['email'];
                    
                    // Récupérer l'ID spécifique selon le rôle
                    if ($user['role'] === 'patient') {
                        $roleStmt = $db->prepare("SELECT id FROM patients WHERE utilisateur_id = ?");
                        $roleStmt->execute([$user['id']]);
                        $roleData = $roleStmt->fetch();
                        if ($roleData) {
                            $_SESSION['patient_id'] = $roleData['id'];
                        }
                    } elseif ($user['role'] === 'medecin') {
                        $roleStmt = $db->prepare("SELECT id FROM medecins WHERE utilisateur_id = ?");
                        $roleStmt->execute([$user['id']]);
                        $roleData = $roleStmt->fetch();
                        if ($roleData) {
                            $_SESSION['medecin_id'] = $roleData['id'];
                        }
                    } elseif ($user['role'] === 'administrateur') {
                        $roleStmt = $db->prepare("SELECT id FROM administrateurs WHERE utilisateur_id = ?");
                        $roleStmt->execute([$user['id']]);
                        $roleData = $roleStmt->fetch();
                        if ($roleData) {
                            $_SESSION['admin_id'] = $roleData['id'];
                        }
                    }
                    
                    // Enregistrer l'activité - VERSION AVEC ? AU LIEU DE :param
                    try {
                        $logStmt = $db->prepare("
                            INSERT INTO logs_activite (utilisateur_id, action, description, adresse_ip, user_agent)
                            VALUES (?, ?, ?, ?, ?)
                        ");
                        
                        $logStmt->execute([
                            $user['id'],
                            'connexion',
                            'Connexion réussie',
                            $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
                            $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu'
                        ]);
                    } catch(PDOException $e) {
                        // Si le log échoue, on continue quand même
                        error_log("Erreur log : " . $e->getMessage());
                    }
                    
                    // Rediriger vers le dashboard approprié
                    redirectToDashboard($user['role']);
                    
                } else {
                    $error = "Identifiant ou mot de passe incorrect.";
                }
            } else {
                $error = "Identifiant ou mot de passe incorrect.";
            }
            
        } catch(PDOException $e) {
            $error = "Une erreur est survenue. Veuillez réessayer.";
            error_log("Erreur de connexion : " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?php echo SITE_NAME; ?></title>
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
            display: flex;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            max-width: 450px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
        }
        
        .login-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-header i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        
        .login-header h2 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control {
            border-radius: 10px;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .input-group-text {
            background: white;
            border: 2px solid #e0e0e0;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .input-group .form-control {
            border-left: none;
            border-radius: 0 10px 10px 0;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: transform 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: #e0e0e0;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            position: relative;
            color: #666;
            font-size: 14px;
        }
        
        .register-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .register-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
        
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #666;
        }
        
        .position-relative {
            position: relative;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container">
            <div class="login-card">
                <div class="login-header">
                    <i class="fas fa-user-md"></i>
                    <h2>Connexion</h2>
                    <p class="mb-0">Accédez à votre espace</p>
                </div>
                
                <div class="login-body">
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
                    
                    <?php if (isset($_GET['message']) && $_GET['message'] === 'deconnecte'): ?>
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle"></i> Vous avez été déconnecté avec succès.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="loginForm">
                        <div class="mb-3">
                            <label for="identifiant" class="form-label">
                                <i class="fas fa-envelope"></i> Email ou Téléphone
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="identifiant" 
                                   name="identifiant" 
                                   placeholder="Entrez votre email ou numéro de téléphone"
                                   required
                                   value="<?php echo isset($_POST['identifiant']) ? htmlspecialchars($_POST['identifiant']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="mot_de_passe" class="form-label">
                                <i class="fas fa-lock"></i> Mot de passe
                            </label>
                            <div class="position-relative">
                                <input type="password" 
                                       class="form-control" 
                                       id="mot_de_passe" 
                                       name="mot_de_passe" 
                                       placeholder="Entrez votre mot de passe"
                                       required>
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                        </div>
                        
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">
                                Se souvenir de moi
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt"></i> Se connecter
                        </button>
                    </form>
                    
                    <div class="divider">
                        <span>OU</span>
                    </div>
                    
                    <div class="register-link">
                        <p class="mb-2">Vous n'avez pas de compte ?</p>
                        <a href="register.php">
                            <i class="fas fa-user-plus"></i> Créer un compte
                        </a>
                    </div>
                    
                    <div class="text-center mt-3">
                        <a href="index.php" class="text-muted text-decoration-none">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('mot_de_passe');
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Form validation
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const identifiant = document.getElementById('identifiant').value.trim();
            const motDePasse = document.getElementById('mot_de_passe').value;
            
            if (!identifiant || !motDePasse) {
                e.preventDefault();
                alert('Veuillez remplir tous les champs.');
            }
        });
    </script>
</body>
</html>