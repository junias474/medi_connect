<?php
/**
 * Utilisateurs - Administrateur
 */
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') {
    header("Location: ../login.php"); exit();
}
$message = ''; $message_type = '';

// Détection du mode ajout médecin
$mode_ajout = (isset($_GET['action']) && $_GET['action'] === 'ajouter' && isset($_GET['role']) && $_GET['role'] === 'medecin');

try {
    $db = Database::getInstance()->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $uid = (int)($_POST['uid'] ?? 0);

        // ─── AJOUT D'UN NOUVEAU MÉDECIN ───────────────────────────────────────
        if ($action === 'ajouter_medecin') {
            $nom         = trim($_POST['nom'] ?? '');
            $prenom      = trim($_POST['prenom'] ?? '');
            $email       = trim($_POST['email'] ?? '');
            $telephone   = trim($_POST['telephone'] ?? '');
            $genre       = $_POST['genre'] ?? 'Homme';
            $ville       = trim($_POST['ville'] ?? '');
            $mot_de_passe= trim($_POST['mot_de_passe'] ?? '');
            $specialite  = trim($_POST['specialite'] ?? '');
            $numero_ordre= trim($_POST['numero_ordre'] ?? '');
            $experience  = (int)($_POST['annees_experience'] ?? 0);
            $tarif       = (float)($_POST['tarif_consultation'] ?? 0);
            $hopital     = trim($_POST['hopital_affiliation'] ?? '');
            $langues     = trim($_POST['langues_parlees'] ?? 'Français');
            $description = trim($_POST['description'] ?? '');

            // Validations basiques
            $errors = [];
            if (!$nom)          $errors[] = "Le nom est requis.";
            if (!$prenom)       $errors[] = "Le prénom est requis.";
            if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
            if (!$telephone)    $errors[] = "Le téléphone est requis.";
            if (!$specialite)   $errors[] = "La spécialité est requise.";
            if (!$numero_ordre) $errors[] = "Le numéro d'ordre est requis.";
            if (strlen($mot_de_passe) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";

            // Vérifier unicité email & téléphone
            if (!$errors) {
                $chk = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
                $chk->execute([$email]);
                if ($chk->fetchColumn() > 0) $errors[] = "Cet email est déjà utilisé.";

                $chk2 = $db->prepare("SELECT COUNT(*) FROM utilisateurs WHERE telephone = ?");
                $chk2->execute([$telephone]);
                if ($chk2->fetchColumn() > 0) $errors[] = "Ce numéro de téléphone est déjà utilisé.";
            }

            if ($errors) {
                $message = implode('<br>', $errors);
                $message_type = 'danger';
                $mode_ajout = true; // Rester sur le formulaire
            } else {
                $db->beginTransaction();
                try {
                    // 1. Insérer dans utilisateurs
                    $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("
                        INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, genre, role, ville, statut)
                        VALUES (?, ?, ?, ?, ?, ?, 'medecin', ?, 'actif')
                    ");
                    $stmt->execute([$nom, $prenom, $email, $telephone, $hash, $genre, $ville]);
                    $utilisateur_id = $db->lastInsertId();

                    // 2. Insérer dans medecins (le trigger génère le numero_medecin automatiquement)
                    $stmt2 = $db->prepare("
                        INSERT INTO medecins (utilisateur_id, numero_medecin, specialite, numero_ordre,
                            annees_experience, tarif_consultation, hopital_affiliation,
                            langues_parlees, description, disponible)
                        VALUES (?, '', ?, ?, ?, ?, ?, ?, ?, 1)
                    ");
                    $stmt2->execute([
                        $utilisateur_id, $specialite, $numero_ordre,
                        $experience, $tarif, $hopital ?: null,
                        $langues ?: 'Français', $description ?: null
                    ]);

                    $db->commit();
                    $message = "✅ Le médecin <strong>Dr. $prenom $nom</strong> a été ajouté avec succès.";
                    $message_type = 'success';
                    $mode_ajout = false;
                } catch (Exception $e) {
                    $db->rollBack();
                    error_log($e->getMessage());
                    $message = "Erreur lors de l'ajout : " . $e->getMessage();
                    $message_type = 'danger';
                    $mode_ajout = true;
                }
            }
        }

        // ─── SUSPENDRE / ACTIVER / SUPPRIMER ─────────────────────────────────
        elseif ($action === 'suspendre' && $uid) {
            $db->prepare("UPDATE utilisateurs SET statut='suspendu' WHERE id=?")->execute([$uid]);
            $message = "Compte suspendu."; $message_type = 'warning';
        } elseif ($action === 'activer' && $uid) {
            $db->prepare("UPDATE utilisateurs SET statut='actif' WHERE id=?")->execute([$uid]);
            $message = "Compte activé."; $message_type = 'success';
        } elseif ($action === 'supprimer' && $uid) {
            $db->prepare("DELETE FROM utilisateurs WHERE id=? AND role != 'administrateur'")->execute([$uid]);
            $message = "Utilisateur supprimé."; $message_type = 'danger';
        }
    }

    // ─── LISTE DES UTILISATEURS ───────────────────────────────────────────────
    $search = trim($_GET['q'] ?? '');
    $filtre = $_GET['filtre'] ?? 'tous';
    $where = "WHERE 1=1";
    $params = [];
    if ($filtre === 'medecin')      $where .= " AND role='medecin'";
    elseif ($filtre === 'patient')  $where .= " AND role='patient'";
    elseif ($filtre === 'admin')    $where .= " AND role='administrateur'";
    elseif ($filtre === 'suspendu') $where .= " AND statut='suspendu'";
    if ($search) { $where .= " AND (nom LIKE ? OR prenom LIKE ? OR email LIKE ?)"; $s="%$search%"; $params=[$s,$s,$s]; }
    $stmt = $db->prepare("SELECT * FROM utilisateurs $where ORDER BY date_inscription DESC");
    $stmt->execute($params);
    $users = $stmt->fetchAll();

    $counts = [];
    foreach (['tous'=>'1=1','medecin'=>"role='medecin'",'patient'=>"role='patient'",'admin'=>"role='administrateur'",'suspendu'=>"statut='suspendu'"] as $k=>$w) {
        $counts[$k] = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE $w")->fetchColumn();
    }
} catch(PDOException $e) {
    error_log($e->getMessage());
    $users=[]; $counts=array_fill_keys(['tous','medecin','patient','admin','suspendu'],0);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title><?php echo $mode_ajout ? 'Ajouter un médecin' : 'Utilisateurs'; ?> - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:270px;--gradient:linear-gradient(135deg,#667eea,#764ba2)}
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:'Outfit',sans-serif;background:#f0f2f8;color:#1a1a2e}
        .sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:var(--gradient);color:#fff;z-index:1000;display:flex;flex-direction:column;box-shadow:4px 0 20px rgba(102,126,234,.25)}
        .sidebar-brand{padding:28px 22px 20px;border-bottom:1px solid rgba(255,255,255,.15)}
        .logo{display:flex;align-items:center;gap:12px}.logo-icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px}
        .logo h3{font-size:17px;font-weight:700}.logo p{font-size:11px;opacity:.7;margin-top:2px}
        .admin-chip{margin-top:14px;background:rgba(255,255,255,.15);border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px}
        .admin-chip .av{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700}
        .admin-chip .info h5{font-size:13px;font-weight:600}.admin-chip .info p{font-size:11px;opacity:.75}
        .sidebar-menu{flex:1;padding:18px 0;overflow-y:auto}.menu-section{padding:12px 22px 5px;font-size:10px;text-transform:uppercase;letter-spacing:1.5px;opacity:.5;font-weight:600}
        .sidebar-menu a{display:flex;align-items:center;gap:12px;padding:12px 22px;color:rgba(255,255,255,.82);text-decoration:none;font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:all .22s}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.13);color:#fff;border-left-color:rgba(255,255,255,.9)}
        .sidebar-menu a i{font-size:17px;width:20px}
        .sidebar-footer{padding:18px 22px;border-top:1px solid rgba(255,255,255,.15)}
        .sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.8);text-decoration:none;font-size:13px}
        .main-content{margin-left:var(--sidebar-w)}
        .topbar{background:#fff;padding:16px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 10px rgba(0,0,0,.06);position:sticky;top:0;z-index:100}
        .topbar h2{font-size:21px;font-weight:800}.topbar p{font-size:13px;color:#888;margin-top:2px}
        .content{padding:26px 30px}
        /* Tabs */
        .filter-tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .tab{padding:9px 18px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;color:#555;background:#fff;border:2px solid #e8eaf5;transition:all .2s;display:flex;align-items:center;gap:6px}
        .tab:hover,.tab.active{background:var(--gradient);color:#fff;border-color:transparent}
        .tab .count{background:rgba(255,255,255,.25);border-radius:20px;padding:1px 7px;font-size:11px}
        .tab:not(.active) .count{background:#f0f2f8;color:#888}
        /* Toolbar */
        .toolbar{display:flex;gap:12px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
        .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:12px;padding:10px 16px;border:2px solid #e8eaf5;flex:1;min-width:220px}
        .search-box input{border:none;outline:none;font-size:14px;font-family:'Outfit',sans-serif;width:100%;background:transparent}
        .search-box i{color:#888}
        .btn{padding:10px 20px;border-radius:12px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:8px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-primary:hover{opacity:.88;transform:translateY(-1px)}
        .btn-outline{background:#fff;color:#555;border:2px solid #e8eaf5}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
        /* Table */
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#888;font-weight:700;padding:14px 20px;text-align:left;background:#fafbff}
        td{padding:13px 20px;font-size:13px;border-bottom:1px solid #f0f2f5;vertical-align:middle}
        tr:last-child td{border-bottom:none}tr:hover td{background:#fafbff}
        .badge{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-danger{background:#fee2e2;color:#dc2626}.badge-info{background:#dbeafe;color:#2563eb}
        .badge-purple{background:#ede9fe;color:#7c3aed}.badge-gray{background:#f3f4f6;color:#6b7280}
        .user-av{width:36px;height:36px;border-radius:50%;background:var(--gradient);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px}
        .user-cell{display:flex;align-items:center;gap:10px}
        .uinfo h6{font-size:13px;font-weight:600;margin-bottom:1px}.uinfo p{font-size:11px;color:#888}
        .actions{display:flex;gap:6px}
        .btn-xs{padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:4px;transition:all .2s}
        .btn-xs-success{background:#dcfce7;color:#16a34a}.btn-xs-danger{background:#fee2e2;color:#dc2626}
        .btn-xs-warning{background:#fef9c3;color:#ca8a04}.btn-xs:hover{opacity:.8}
        .alert{padding:12px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0}
        .alert-warning{background:#fef9c3;color:#ca8a04;border:1px solid #fde68a}
        .alert-danger{background:#fee2e2;color:#dc2626;border:1px solid #fecaca}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}

        /* ── Formulaire d'ajout médecin ── */
        .form-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.07);overflow:hidden}
        .form-header{background:var(--gradient);padding:28px 32px;color:#fff}
        .form-header h3{font-size:20px;font-weight:800;display:flex;align-items:center;gap:10px}
        .form-header p{font-size:13px;opacity:.8;margin-top:4px}
        .form-body{padding:32px}
        .form-section-title{font-size:11px;text-transform:uppercase;letter-spacing:1.5px;color:#888;font-weight:700;margin:24px 0 14px;padding-bottom:8px;border-bottom:2px solid #f0f2f5;display:flex;align-items:center;gap:8px}
        .form-section-title:first-child{margin-top:0}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
        .form-row.triple{grid-template-columns:1fr 1fr 1fr}
        .form-group{display:flex;flex-direction:column;gap:6px}
        .form-group label{font-size:12px;font-weight:700;color:#444;display:flex;align-items:center;gap:6px}
        .form-group label .req{color:#ef4444}
        .form-control{padding:11px 14px;border-radius:10px;border:2px solid #e8eaf5;font-size:13px;font-family:'Outfit',sans-serif;color:#1a1a2e;transition:border-color .2s;background:#fff;width:100%}
        .form-control:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        select.form-control{cursor:pointer}
        textarea.form-control{resize:vertical;min-height:80px}
        .form-control::placeholder{color:#bbb}
        .form-actions{display:flex;gap:12px;justify-content:flex-end;margin-top:28px;padding-top:20px;border-top:2px solid #f0f2f5}
        .hint{font-size:11px;color:#aaa;margin-top:2px}
        .password-wrap{position:relative}
        .password-wrap input{padding-right:42px}
        .password-wrap .toggle-pw{position:absolute;right:12px;top:50%;transform:translateY(-50%);cursor:pointer;color:#999;font-size:16px;border:none;background:transparent;padding:0}
        .password-wrap .toggle-pw:hover{color:var(--primary)}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div><div><h3><?php echo SITE_NAME; ?></h3><p>Administration</p></div></div>
        <div class="admin-chip">
            <div class="av"><?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?></div>
            <div class="info"><h5><?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h5><p>Super Admin</p></div>
        </div>
    </div>
    <nav class="sidebar-menu">
        <span class="menu-section">Tableau de bord</span>
        <a href="index.php"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a>
        <span class="menu-section">Gestion</span>
        <a href="medecins.php"><i class="bi bi-person-badge"></i> Médecins</a>
        <a href="patients.php"><i class="bi bi-people"></i> Patients</a>
        <a href="utilisateurs.php" class="active"><i class="bi bi-person-gear"></i> Utilisateurs</a>
        <span class="menu-section">Activité</span>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <div>
            <h2><?php echo $mode_ajout ? 'Ajouter un médecin' : 'Gestion des Utilisateurs'; ?></h2>
            <p><?php echo $mode_ajout ? 'Remplissez les informations du nouveau médecin' : count($users).' utilisateur(s) trouvé(s)'; ?></p>
        </div>
        <?php if ($mode_ajout): ?>
            <a href="medecins.php" class="btn btn-outline"><i class="bi bi-arrow-left"></i> Retour aux médecins</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="bi bi-<?php echo $message_type==='success'?'check-circle':'exclamation-triangle'; ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>

        <?php if ($mode_ajout): ?>
        <!-- ════════════════════════════════════════════════════════════
             FORMULAIRE D'AJOUT MÉDECIN
        ════════════════════════════════════════════════════════════ -->
        <div class="form-card">
            <div class="form-header">
                <h3><i class="bi bi-person-plus-fill"></i> Nouveau médecin</h3>
                <p>Les champs marqués <strong>*</strong> sont obligatoires</p>
            </div>
            <div class="form-body">
                <form method="POST" action="utilisateurs.php?action=ajouter&role=medecin" autocomplete="off">
                    <input type="hidden" name="action" value="ajouter_medecin">

                    <!-- ── Informations personnelles ── -->
                    <div class="form-section-title"><i class="bi bi-person-circle" style="color:var(--primary)"></i> Informations personnelles</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Nom <span class="req">*</span></label>
                            <input type="text" name="nom" class="form-control" placeholder="Dupont" value="<?php echo htmlspecialchars($_POST['nom'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-person"></i> Prénom <span class="req">*</span></label>
                            <input type="text" name="prenom" class="form-control" placeholder="Jean" value="<?php echo htmlspecialchars($_POST['prenom'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top:14px">
                        <div class="form-group">
                            <label><i class="bi bi-envelope"></i> Email <span class="req">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="dr.nom@medical.cm" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-phone"></i> Téléphone <span class="req">*</span></label>
                            <input type="text" name="telephone" class="form-control" placeholder="+237677001122" value="<?php echo htmlspecialchars($_POST['telephone'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row" style="margin-top:14px">
                        <div class="form-group">
                            <label><i class="bi bi-gender-ambiguous"></i> Genre <span class="req">*</span></label>
                            <select name="genre" class="form-control">
                                <option value="Homme" <?php echo ($_POST['genre']??'Homme')==='Homme'?'selected':''; ?>>Homme</option>
                                <option value="Femme" <?php echo ($_POST['genre']??'')==='Femme'?'selected':''; ?>>Femme</option>
                                <option value="Autre" <?php echo ($_POST['genre']??'')==='Autre'?'selected':''; ?>>Autre</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-geo-alt"></i> Ville</label>
                            <input type="text" name="ville" class="form-control" placeholder="Yaoundé" value="<?php echo htmlspecialchars($_POST['ville'] ?? ''); ?>">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:14px">
                        <label><i class="bi bi-lock"></i> Mot de passe <span class="req">*</span></label>
                        <div class="password-wrap">
                            <input type="password" name="mot_de_passe" id="mdp" class="form-control" placeholder="Minimum 6 caractères" required>
                            <button type="button" class="toggle-pw" onclick="togglePw()"><i class="bi bi-eye" id="pw-icon"></i></button>
                        </div>
                        <span class="hint">Le médecin pourra le modifier depuis son espace.</span>
                    </div>

                    <!-- ── Informations professionnelles ── -->
                    <div class="form-section-title"><i class="bi bi-briefcase" style="color:var(--primary)"></i> Informations professionnelles</div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="bi bi-heart-pulse"></i> Spécialité <span class="req">*</span></label>
                            <select name="specialite" class="form-control" required>
                                <option value="">-- Choisir --</option>
                                <?php
                                $specialites = ['Médecine Générale','Pédiatrie','Chirurgie','Gynécologie','Cardiologie','Dermatologie','Neurologie','Orthopédie','Ophtalmologie','ORL','Psychiatrie','Radiologie','Anesthésiologie','Urologie','Endocrinologie','Gastro-entérologie','Pneumologie','Rhumatologie','Infectiologie','Médecine d\'urgence'];
                                foreach ($specialites as $sp) {
                                    $sel = (($_POST['specialite']??'') === $sp) ? 'selected' : '';
                                    echo "<option value=\"$sp\" $sel>$sp</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-card-text"></i> Numéro d'ordre <span class="req">*</span></label>
                            <input type="text" name="numero_ordre" class="form-control" placeholder="ORD-CM-12345" value="<?php echo htmlspecialchars($_POST['numero_ordre'] ?? ''); ?>" required>
                        </div>
                    </div>
                    <div class="form-row triple" style="margin-top:14px">
                        <div class="form-group">
                            <label><i class="bi bi-calendar3"></i> Années d'expérience</label>
                            <input type="number" name="annees_experience" class="form-control" placeholder="0" min="0" max="60" value="<?php echo htmlspecialchars($_POST['annees_experience'] ?? '0'); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-cash-coin"></i> Tarif consultation (FCFA)</label>
                            <input type="number" name="tarif_consultation" class="form-control" placeholder="15000" min="0" step="500" value="<?php echo htmlspecialchars($_POST['tarif_consultation'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label><i class="bi bi-translate"></i> Langues parlées</label>
                            <input type="text" name="langues_parlees" class="form-control" placeholder="Français, Anglais" value="<?php echo htmlspecialchars($_POST['langues_parlees'] ?? 'Français'); ?>">
                        </div>
                    </div>
                    <div class="form-group" style="margin-top:14px">
                        <label><i class="bi bi-hospital"></i> Hôpital / Clinique d'affiliation</label>
                        <input type="text" name="hopital_affiliation" class="form-control" placeholder="Hôpital Central de Yaoundé" value="<?php echo htmlspecialchars($_POST['hopital_affiliation'] ?? ''); ?>">
                    </div>
                    <div class="form-group" style="margin-top:14px">
                        <label><i class="bi bi-file-text"></i> Description / Bio</label>
                        <textarea name="description" class="form-control" placeholder="Brève description du médecin..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="medecins.php" class="btn btn-outline"><i class="bi bi-x-circle"></i> Annuler</a>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer le médecin</button>
                    </div>
                </form>
            </div>
        </div>

        <?php else: ?>
        <!-- ════════════════════════════════════════════════════════════
             LISTE DES UTILISATEURS
        ════════════════════════════════════════════════════════════ -->
        <div class="filter-tabs">
            <?php foreach (['tous'=>['Tous','bi-people'],'medecin'=>['Médecins','bi-person-badge'],'patient'=>['Patients','bi-person-heart'],'admin'=>['Admins','bi-shield-check'],'suspendu'=>['Suspendus','bi-person-x']] as $k=>[$label,$icon]): ?>
            <a href="?filtre=<?php echo $k; ?><?php echo $search?"&q=".urlencode($search):''; ?>" class="tab <?php echo $filtre===$k?'active':''; ?>">
                <i class="bi <?php echo $icon; ?>"></i> <?php echo $label; ?> <span class="count"><?php echo $counts[$k]; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="toolbar">
            <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
                <input type="hidden" name="filtre" value="<?php echo htmlspecialchars($filtre); ?>">
                <div class="search-box"><i class="bi bi-search"></i><input type="text" name="q" placeholder="Rechercher..." value="<?php echo htmlspecialchars($search); ?>"></div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Chercher</button>
                <?php if ($search): ?><a href="?filtre=<?php echo $filtre; ?>" class="btn btn-outline"><i class="bi bi-x"></i> Effacer</a><?php endif; ?>
            </form>
        </div>
        <div class="card">
            <?php if (count($users) > 0): ?>
            <table>
                <thead><tr><th>Utilisateur</th><th>Téléphone</th><th>Rôle</th><th>Inscription</th><th>Dernière connexion</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($users as $u):
                    $initials = strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1));
                    $role_b = ['patient'=>['info','Patient'],'medecin'=>['purple','Médecin'],'administrateur'=>['danger','Admin']];
                    $rb = $role_b[$u['role']] ?? ['gray','?'];
                    $sb = ['actif'=>'success','inactif'=>'warning','suspendu'=>'danger'][$u['statut']] ?? 'gray';
                ?>
                <tr>
                    <td><div class="user-cell"><div class="user-av"><?php echo $initials; ?></div><div class="uinfo"><h6><?php echo htmlspecialchars($u['prenom'].' '.$u['nom']); ?></h6><p><?php echo htmlspecialchars($u['email']); ?></p></div></div></td>
                    <td><?php echo htmlspecialchars($u['telephone']); ?></td>
                    <td><span class="badge badge-<?php echo $rb[0]; ?>"><?php echo $rb[1]; ?></span></td>
                    <td style="color:#888;font-size:12px"><?php echo date('d/m/Y', strtotime($u['date_inscription'])); ?></td>
                    <td style="color:#888;font-size:12px"><?php echo $u['derniere_connexion'] ? date('d/m/Y H:i', strtotime($u['derniere_connexion'])) : '—'; ?></td>
                    <td><span class="badge badge-<?php echo $sb; ?>"><?php echo ucfirst($u['statut']); ?></span></td>
                    <td>
                        <div class="actions">
                            <?php if ($u['role'] !== 'administrateur'): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Confirmer ?')">
                                <input type="hidden" name="uid" value="<?php echo $u['id']; ?>">
                                <input type="hidden" name="action" value="<?php echo $u['statut']==='actif'?'suspendre':'activer'; ?>">
                                <button type="submit" class="btn-xs <?php echo $u['statut']==='actif'?'btn-xs-warning':'btn-xs-success'; ?>">
                                    <i class="bi bi-<?php echo $u['statut']==='actif'?'pause-circle':'play-circle'; ?>"></i>
                                    <?php echo $u['statut']==='actif'?'Suspendre':'Activer'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer cet utilisateur ?')">
                                <input type="hidden" name="uid" value="<?php echo $u['id']; ?>">
                                <input type="hidden" name="action" value="supprimer">
                                <button type="submit" class="btn-xs btn-xs-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            <?php else: ?>
                            <span style="font-size:11px;color:#bbb">Protégé</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty"><i class="bi bi-people"></i><p>Aucun utilisateur trouvé</p></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function togglePw() {
    const input = document.getElementById('mdp');
    const icon  = document.getElementById('pw-icon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}
</script>
</body>
</html>