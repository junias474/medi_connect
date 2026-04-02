<?php
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') { header("Location: ../../login.php"); exit(); }
$message = ''; $message_type = '';
try {
    $db = Database::getInstance()->getConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['parametres'])) {
        foreach ($_POST['parametres'] as $cle => $valeur) {
            $stmt = $db->prepare("INSERT INTO parametres_systeme (cle_parametre, valeur_parametre) VALUES (?,?) ON DUPLICATE KEY UPDATE valeur_parametre=?");
            $stmt->execute([sanitize($cle), sanitize($valeur), sanitize($valeur)]);
        }
        $message = "Paramètres enregistrés avec succès."; $message_type = 'success';
    }
    $stmt = $db->query("SELECT * FROM parametres_systeme ORDER BY id");
    $parametres = $stmt->fetchAll();
    $param_map = [];
    foreach ($parametres as $p) $param_map[$p['cle_parametre']] = $p;
    // Infos système
    $info = [
        'Version PHP' => PHP_VERSION,
        'Serveur' => $_SERVER['SERVER_SOFTWARE'] ?? 'N/A',
        'Base de données' => DB_NAME,
        'Hôte BDD' => DB_HOST,
        'URL site' => SITE_URL,
        'Heure serveur' => date('d/m/Y H:i:s'),
    ];
    // Stats rapides
    $stats = [
        'Utilisateurs' => $db->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn(),
        'Médecins' => $db->query("SELECT COUNT(*) FROM medecins")->fetchColumn(),
        'Patients' => $db->query("SELECT COUNT(*) FROM patients")->fetchColumn(),
        'Rendez-vous' => $db->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn(),
        'Consultations' => $db->query("SELECT COUNT(*) FROM consultations")->fetchColumn(),
        'Logs' => $db->query("SELECT COUNT(*) FROM logs_activite")->fetchColumn(),
    ];
} catch(PDOException $e) { error_log($e->getMessage()); $parametres=[]; $param_map=[]; $info=[]; $stats=[]; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Paramètres - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--sidebar-w:270px;--gradient:linear-gradient(135deg,#667eea,#764ba2)}
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
        .layout{display:grid;grid-template-columns:1.4fr 1fr;gap:20px}
        @media(max-width:900px){.layout{grid-template-columns:1fr}}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden;margin-bottom:18px}
        .card-header{padding:18px 24px;border-bottom:1px solid #f0f2f5;display:flex;align-items:center;gap:10px}
        .card-header h5{font-size:15px;font-weight:700}
        .card-header h5 i{color:var(--primary)}
        .card-body{padding:22px 24px}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:12px;font-weight:700;text-transform:uppercase;color:#888;margin-bottom:7px;letter-spacing:.5px}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:11px 14px;border:2px solid #e8eaf5;border-radius:12px;font-size:14px;font-family:'Outfit',sans-serif;color:#1a1a2e;transition:border-color .2s;outline:none;background:#fafbff}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:var(--primary);background:#fff}
        .form-group .hint{font-size:11px;color:#aaa;margin-top:5px}
        .btn{padding:12px 24px;border-radius:12px;border:none;cursor:pointer;font-weight:700;font-size:14px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:8px;transition:all .25s}
        .btn-primary{background:var(--gradient);color:#fff}.btn-primary:hover{opacity:.88;transform:translateY(-1px)}
        .alert{padding:12px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0}
        /* Info système */
        .info-item{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-bottom:1px solid #f0f2f5;font-size:13px}
        .info-item:last-child{border-bottom:none}
        .info-item .key{color:#888;font-weight:500}
        .info-item .val{font-weight:700;color:#1a1a2e;font-size:12px;font-family:monospace;background:#f0f2f8;padding:3px 10px;border-radius:8px}
        /* Stats grid */
        .stats-mini{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:4px}
        .sm-item{background:#f8f9ff;border-radius:10px;padding:12px;text-align:center}
        .sm-item .n{font-size:20px;font-weight:800;color:var(--primary)}.sm-item p{font-size:11px;color:#888;margin-top:3px}
        /* Param table existants */
        .param-row{display:flex;justify-content:space-between;align-items:center;padding:12px 0;border-bottom:1px solid #f0f2f5}
        .param-row:last-child{border-bottom:none}
        .param-row .pk{font-size:13px;font-weight:600;font-family:monospace;color:var(--primary)}
        .param-row .pv{font-size:13px;color:#555}
        .param-row .pd{font-size:11px;color:#aaa;margin-top:2px}
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
        <span class="menu-section">Tableau de bord</span><a href="index.php"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a>
        <span class="menu-section">Gestion</span>
        <a href="medecins.php"><i class="bi bi-person-badge"></i> Médecins</a>
        <a href="patients.php"><i class="bi bi-people"></i> Patients</a>
        <a href="utilisateurs.php"><i class="bi bi-person-gear"></i> Utilisateurs</a>
        <span class="menu-section">Activité</span>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php" class="active"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
<div class="main-content">
    <div class="topbar"><div><h2>Paramètres système</h2><p>Configuration de l'application</p></div></div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-<?php echo $message_type; ?>"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <div class="layout">
            <!-- Colonne gauche : Formulaire -->
            <div>
                <!-- Paramètres existants en BDD -->
                <?php if (count($parametres) > 0): ?>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-database"></i> Paramètres enregistrés</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <?php foreach ($parametres as $p): ?>
                            <div class="form-group">
                                <label><?php echo htmlspecialchars($p['cle_parametre']); ?></label>
                                <input type="text" name="parametres[<?php echo htmlspecialchars($p['cle_parametre']); ?>]"
                                       value="<?php echo htmlspecialchars($p['valeur_parametre']); ?>">
                                <?php if ($p['description']): ?>
                                <div class="hint"><?php echo htmlspecialchars($p['description']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                        </form>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Ajouter nouveau paramètre -->
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-plus-circle"></i> Ajouter un paramètre</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="form-group">
                                <label>Clé du paramètre</label>
                                <input type="text" name="parametres[nouvelle_cle]" placeholder="ex: nom_site, email_contact...">
                            </div>
                            <div class="form-group">
                                <label>Valeur</label>
                                <input type="text" name="parametres[nouvelle_valeur]" placeholder="Valeur du paramètre">
                            </div>
                            <button type="submit" class="btn btn-primary"><i class="bi bi-plus"></i> Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Info système -->
            <div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-server"></i> Informations système</h5></div>
                    <div class="card-body">
                        <?php foreach ($info as $k=>$v): ?>
                        <div class="info-item">
                            <span class="key"><?php echo $k; ?></span>
                            <span class="val"><?php echo htmlspecialchars($v); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-bar-chart"></i> Statistiques base de données</h5></div>
                    <div class="card-body">
                        <div class="stats-mini">
                            <?php foreach ($stats as $label=>$val): ?>
                            <div class="sm-item"><div class="n"><?php echo $val; ?></div><p><?php echo $label; ?></p></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-shield-check"></i> Actions rapides</h5></div>
                    <div class="card-body" style="display:flex;flex-direction:column;gap:10px">
                        <a href="logs.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f0f4ff;border-radius:12px;text-decoration:none;color:#333;font-size:13px;font-weight:600;transition:background .2s" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f0f4ff'">
                            <i class="bi bi-journal-text" style="color:var(--primary);font-size:18px"></i> Voir les logs d'activité
                        </a>
                        <a href="utilisateurs.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#f0f4ff;border-radius:12px;text-decoration:none;color:#333;font-size:13px;font-weight:600;transition:background .2s" onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#f0f4ff'">
                            <i class="bi bi-people" style="color:var(--primary);font-size:18px"></i> Gérer les utilisateurs
                        </a>
                        <a href="../../logout.php" style="display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fee2e2;border-radius:12px;text-decoration:none;color:#dc2626;font-size:13px;font-weight:600">
                            <i class="bi bi-box-arrow-left" style="font-size:18px"></i> Se déconnecter
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>