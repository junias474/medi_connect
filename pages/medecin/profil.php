<?php
/**
 * Mon Profil - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$user_id    = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_profil') {
        $nom      = sanitize($_POST['nom'] ?? '');
        $prenom   = sanitize($_POST['prenom'] ?? '');
        $telephone = sanitize($_POST['telephone'] ?? '');
        $ville    = sanitize($_POST['ville'] ?? '');
        $adresse  = sanitize($_POST['adresse'] ?? '');
        $specialite = sanitize($_POST['specialite'] ?? '');
        $experience = intval($_POST['annees_experience'] ?? 0);
        $tarif    = floatval($_POST['tarif_consultation'] ?? 0);
        $hopital  = sanitize($_POST['hopital_affiliation'] ?? '');
        $description = $_POST['description'] ?? '';
        $langues  = sanitize($_POST['langues_parlees'] ?? 'Français');
        $disponible = isset($_POST['disponible']) ? 1 : 0;

        $db->prepare("UPDATE utilisateurs SET nom=?,prenom=?,telephone=?,ville=?,adresse=? WHERE id=?")->execute([$nom,$prenom,$telephone,$ville,$adresse,$user_id]);
        $db->prepare("UPDATE medecins SET specialite=?,annees_experience=?,tarif_consultation=?,hopital_affiliation=?,description=?,langues_parlees=?,disponible=? WHERE id=?")->execute([$specialite,$experience,$tarif,$hopital,$description,$langues,$disponible,$medecin_id]);
        $_SESSION['user_nom']    = $nom;
        $_SESSION['user_prenom'] = $prenom;
        $message = "Profil mis à jour avec succès.";
    } elseif ($action === 'change_password') {
        $old = $_POST['ancien_mdp'] ?? '';
        $new = $_POST['nouveau_mdp'] ?? '';
        $confirm = $_POST['confirm_mdp'] ?? '';
        $stmt = $db->prepare("SELECT mot_de_passe FROM utilisateurs WHERE id=?");
        $stmt->execute([$user_id]);
        $u = $stmt->fetch();
        if (!password_verify($old, $u['mot_de_passe'])) { $error = "Ancien mot de passe incorrect."; }
        elseif ($new !== $confirm) { $error = "Les mots de passe ne correspondent pas."; }
        elseif (strlen($new) < 6) { $error = "Le mot de passe doit contenir au moins 6 caractères."; }
        else { $db->prepare("UPDATE utilisateurs SET mot_de_passe=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),$user_id]); $message = "Mot de passe modifié."; }
    }
}

$stmt = $db->prepare("SELECT m.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.adresse, u.genre, u.date_naissance, u.date_inscription FROM medecins m INNER JOIN utilisateurs u ON m.utilisateur_id=u.id WHERE m.id=?");
$stmt->execute([$medecin_id]);
$medecin = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mon profil - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:260px;--gradient:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:'Outfit',sans-serif;background:#f5f7fa;color:#333}
        .sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:var(--gradient);color:#fff;z-index:1000;display:flex;flex-direction:column;box-shadow:2px 0 15px rgba(0,0,0,.15)}
        .sidebar-header{padding:30px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.15)}
        .sidebar-header .avatar{width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.25);border:3px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px}
        .sidebar-header h4{font-size:16px;font-weight:600}.sidebar-header p{font-size:12px;opacity:.75}
        .sidebar-menu{flex:1;padding:20px 0;overflow-y:auto}
        .sidebar-menu a{display:flex;align-items:center;gap:12px;padding:13px 25px;color:rgba(255,255,255,.85);text-decoration:none;font-size:14px;font-weight:500;border-left:3px solid transparent;transition:all .25s}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.12);color:#fff;border-left-color:#fff}
        .sidebar-menu a i{font-size:18px;width:22px}
        .sidebar-menu .menu-section{padding:10px 25px 5px;font-size:11px;text-transform:uppercase;letter-spacing:1px;opacity:.5}
        .sidebar-footer{padding:20px;border-top:1px solid rgba(255,255,255,.15)}
        .sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.8);text-decoration:none;font-size:14px}
        .main-content{margin-left:var(--sidebar-w)}
        .topbar{background:#fff;padding:18px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.06);position:sticky;top:0;z-index:100}
        .topbar h2{font-size:22px;font-weight:700}.content{padding:28px 30px}
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}.alert-danger{background:#fee2e2;color:#dc2626}
        .layout{display:grid;grid-template-columns:280px 1fr;gap:20px}
        @media(max-width:900px){.layout{grid-template-columns:1fr}}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
        .card-header{padding:18px 22px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
        .card-body{padding:22px}
        .avatar-big{width:100px;height:100px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-size:36px;font-weight:700;margin:0 auto 16px}
        .profile-info-item{display:flex;justify-content:space-between;padding:10px 0;border-bottom:1px solid #f5f5f5;font-size:13px}
        .profile-info-item:last-child{border:none}
        .profile-info-item .label{color:#888;font-weight:600}.profile-info-item .val{color:#333;font-weight:500;text-align:right}
        .tabs{display:flex;gap:4px;background:#f0f2f5;border-radius:12px;padding:4px;margin-bottom:20px}
        .tab{flex:1;padding:9px;text-align:center;border-radius:10px;cursor:pointer;font-size:13px;font-weight:600;transition:all .25s;border:none;background:none;font-family:'Outfit',sans-serif;color:#666}
        .tab.active{background:#fff;color:var(--primary);box-shadow:0 2px 6px rgba(0,0,0,.08)}
        .tab-content{display:none}.tab-content.active{display:block}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;transition:border-color .2s}
        .form-group input:focus,.form-group textarea:focus{border-color:var(--primary)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .btn{padding:11px 22px;border-radius:10px;border:none;cursor:pointer;font-weight:700;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:7px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .toggle-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f5f5f5}
        .toggle-row label{font-size:14px;font-weight:600;color:#333}
        .toggle-switch{position:relative;width:44px;height:24px}
        .toggle-switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;top:0;left:0;right:0;bottom:0;background:#d1d5db;border-radius:24px;cursor:pointer;transition:.3s}
        .slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
        input:checked+.slider{background:var(--primary)}
        input:checked+.slider:before{transform:translateX(20px)}
        .stat-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px}
        .mini-s{background:#f8f9ff;border-radius:12px;padding:14px;text-align:center}
        .mini-s .n{font-size:22px;font-weight:700;color:var(--primary)}.mini-s p{font-size:12px;color:#888;margin-top:4px}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="avatar"><i class="bi bi-person-badge"></i></div>
        <h4>Dr. <?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h4>
        <p>Médecin</p>
    </div>
    <nav class="sidebar-menu">
        <span class="menu-section">Principal</span>
        <a href="index.php"><i class="bi bi-house"></i> Tableau de bord</a>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="patients.php"><i class="bi bi-people"></i> Mes patients</a>
        <span class="menu-section">Gestion</span>
        <a href="agenda.php"><i class="bi bi-calendar3"></i> Mon agenda</a>
        <a href="horaires.php"><i class="bi bi-clock"></i> Mes horaires</a>
        <a href="ordonnances.php"><i class="bi bi-file-medical"></i> Ordonnances</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Compte</span>
        <a href="profil.php" class="active"><i class="bi bi-person-gear"></i> Mon profil</a>
        <a href="evaluations.php"><i class="bi bi-star"></i> Évaluations</a>
    </nav>
    <div class="sidebar-footer"><a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <h2><i class="bi bi-person-gear" style="color:var(--primary)"></i> Mon profil</h2>
    </div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div><?php endif; ?>
        <div class="layout">
            <!-- Carte profil -->
            <div>
                <div class="card">
                    <div class="card-body" style="text-align:center">
                        <div class="avatar-big"><?php echo strtoupper(substr($medecin['prenom'],0,1).substr($medecin['nom'],0,1)); ?></div>
                        <h4 style="font-size:18px;font-weight:700">Dr. <?php echo htmlspecialchars($medecin['prenom'].' '.$medecin['nom']); ?></h4>
                        <p style="color:#888;font-size:13px;margin:4px 0 2px"><?php echo htmlspecialchars($medecin['specialite']); ?></p>
                        <p style="color:#888;font-size:12px"><?php echo htmlspecialchars($medecin['numero_medecin']); ?></p>
                        <div style="margin:14px 0;padding:10px;background:<?php echo $medecin['disponible']?'#dcfce7':'#fee2e2'; ?>;border-radius:10px;font-size:13px;font-weight:600;color:<?php echo $medecin['disponible']?'#16a34a':'#dc2626'; ?>">
                            <i class="bi bi-circle-fill" style="font-size:8px"></i> <?php echo $medecin['disponible']?'Disponible':'Indisponible'; ?>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-graph-up" style="color:var(--primary)"></i> Statistiques</h5></div>
                    <div class="card-body">
                        <div class="stat-row">
                            <div class="mini-s"><div class="n"><?php echo $medecin['nombre_consultations']; ?></div><p>Consultations</p></div>
                            <div class="mini-s"><div class="n"><?php echo number_format($medecin['note_moyenne'],1); ?>/5</div><p>Note moy.</p></div>
                            <div class="mini-s"><div class="n"><?php echo $medecin['annees_experience']; ?></div><p>Ans exp.</p></div>
                            <div class="mini-s"><div class="n"><?php echo number_format($medecin['tarif_consultation'],0); ?></div><p>F CFA</p></div>
                        </div>
                        <div class="profile-info-item"><span class="label">N° Ordre</span><span class="val"><?php echo htmlspecialchars($medecin['numero_ordre']); ?></span></div>
                        <div class="profile-info-item"><span class="label">Membre depuis</span><span class="val"><?php echo date('M Y',strtotime($medecin['date_inscription'])); ?></span></div>
                        <div class="profile-info-item"><span class="label">Langues</span><span class="val"><?php echo htmlspecialchars($medecin['langues_parlees']); ?></span></div>
                    </div>
                </div>
            </div>

            <!-- Formulaires -->
            <div>
                <div class="tabs">
                    <button class="tab active" onclick="switchTab('infos',this)">Informations</button>
                    <button class="tab" onclick="switchTab('securite',this)">Sécurité</button>
                </div>

                <!-- Onglet infos -->
                <div id="tab_infos" class="tab-content active">
                    <div class="card">
                        <div class="card-header"><h5><i class="bi bi-person-lines-fill" style="color:var(--primary)"></i> Informations personnelles & professionnelles</h5></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="update_profil">
                                <div class="form-row">
                                    <div class="form-group"><label>Nom</label><input type="text" name="nom" value="<?php echo htmlspecialchars($medecin['nom']); ?>" required></div>
                                    <div class="form-group"><label>Prénom</label><input type="text" name="prenom" value="<?php echo htmlspecialchars($medecin['prenom']); ?>" required></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Téléphone</label><input type="tel" name="telephone" value="<?php echo htmlspecialchars($medecin['telephone']); ?>"></div>
                                    <div class="form-group"><label>Ville</label><input type="text" name="ville" value="<?php echo htmlspecialchars($medecin['ville']??''); ?>"></div>
                                </div>
                                <div class="form-group"><label>Adresse</label><input type="text" name="adresse" value="<?php echo htmlspecialchars($medecin['adresse']??''); ?>"></div>
                                <hr style="margin:16px 0;border:none;border-top:1px solid #f0f2f5">
                                <div class="form-row">
                                    <div class="form-group"><label>Spécialité</label><input type="text" name="specialite" value="<?php echo htmlspecialchars($medecin['specialite']); ?>" required></div>
                                    <div class="form-group"><label>Années d'expérience</label><input type="number" name="annees_experience" value="<?php echo $medecin['annees_experience']; ?>" min="0"></div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group"><label>Tarif consultation (FCFA)</label><input type="number" name="tarif_consultation" value="<?php echo $medecin['tarif_consultation']; ?>" step="500"></div>
                                    <div class="form-group"><label>Hôpital / Affiliation</label><input type="text" name="hopital_affiliation" value="<?php echo htmlspecialchars($medecin['hopital_affiliation']??''); ?>"></div>
                                </div>
                                <div class="form-group"><label>Langues parlées</label><input type="text" name="langues_parlees" value="<?php echo htmlspecialchars($medecin['langues_parlees']); ?>" placeholder="Ex: Français, Anglais"></div>
                                <div class="form-group"><label>Description / Biographie</label><textarea name="description" rows="4" placeholder="Présentez-vous..."><?php echo htmlspecialchars($medecin['description']??''); ?></textarea></div>
                                <div class="toggle-row">
                                    <label>Disponible pour consultations</label>
                                    <label class="toggle-switch"><input type="checkbox" name="disponible" <?php echo $medecin['disponible']?'checked':''; ?>><span class="slider"></span></label>
                                </div>
                                <div style="margin-top:20px"><button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer les modifications</button></div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Onglet sécurité -->
                <div id="tab_securite" class="tab-content">
                    <div class="card">
                        <div class="card-header"><h5><i class="bi bi-shield-lock" style="color:var(--primary)"></i> Changer de mot de passe</h5></div>
                        <div class="card-body">
                            <form method="POST">
                                <input type="hidden" name="action" value="change_password">
                                <div class="form-group"><label>Ancien mot de passe</label><input type="password" name="ancien_mdp" required></div>
                                <div class="form-group"><label>Nouveau mot de passe</label><input type="password" name="nouveau_mdp" required minlength="6"></div>
                                <div class="form-group"><label>Confirmer le nouveau mot de passe</label><input type="password" name="confirm_mdp" required minlength="6"></div>
                                <button type="submit" class="btn btn-primary"><i class="bi bi-key"></i> Modifier le mot de passe</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
    document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
    document.getElementById('tab_'+name).classList.add('active');
    btn.classList.add('active');
}
</script>
</body>
</html>