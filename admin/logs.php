<?php
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') { header("Location: ../../login.php"); exit(); }
try {
    $db = Database::getInstance()->getConnection();
    $filtre = $_GET['filtre'] ?? 'tous';
    $search = trim($_GET['q'] ?? '');
    $where = "WHERE 1=1"; $params = [];
    if ($filtre !== 'tous') { $where .= " AND l.action=?"; $params[] = $filtre; }
    if ($search) { $where .= " AND (CONCAT(u.nom,' ',u.prenom) LIKE ? OR l.description LIKE ?)"; $s="%$search%"; $params[]=$s; $params[]=$s; }
    $stmt = $db->prepare("
        SELECT l.*, CONCAT(u.nom,' ',u.prenom) as user_nom, u.role as user_role
        FROM logs_activite l
        LEFT JOIN utilisateurs u ON l.utilisateur_id=u.id
        $where
        ORDER BY l.created_at DESC LIMIT 200
    ");
    $stmt->execute($params);
    $logs = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM logs_activite")->fetchColumn();
    $actions = $db->query("SELECT DISTINCT action FROM logs_activite ORDER BY action")->fetchAll(PDO::FETCH_COLUMN);
} catch(PDOException $e) { error_log($e->getMessage()); $logs=[]; $total=0; $actions=[]; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Logs - Admin</title>
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
        .toolbar{display:flex;gap:12px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
        .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:12px;padding:10px 16px;border:2px solid #e8eaf5;flex:1}
        .search-box input{border:none;outline:none;font-size:14px;font-family:'Outfit',sans-serif;width:100%;background:transparent}
        select.filter-sel{padding:10px 14px;border-radius:12px;border:2px solid #e8eaf5;font-size:13px;font-family:'Outfit',sans-serif;background:#fff;color:#333;outline:none;cursor:pointer}
        .btn{padding:10px 20px;border-radius:12px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:8px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        .log-item{display:flex;align-items:flex-start;gap:14px;padding:14px 22px;border-bottom:1px solid #f0f2f5;transition:background .2s}
        .log-item:last-child{border-bottom:none}.log-item:hover{background:#fafbff}
        .log-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
        .li-connexion{background:#dcfce7;color:#16a34a}.li-deconnexion{background:#fef9c3;color:#ca8a04}
        .li-rdv,.li-rendez_vous{background:#dbeafe;color:#2563eb}.li-consultation{background:#ede9fe;color:#7c3aed}
        .li-default{background:#f0f2f8;color:#667eea}
        .log-body{flex:1}
        .log-body h6{font-size:13px;font-weight:600;margin-bottom:3px}
        .log-body p{font-size:12px;color:#888}
        .log-body .desc{font-size:12px;color:#555;margin-top:3px}
        .log-meta{text-align:right;flex-shrink:0}
        .log-meta .time{font-size:11px;color:#888}
        .log-meta .ip{font-size:10px;color:#bbb;font-family:monospace;margin-top:3px}
        .badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-purple{background:#ede9fe;color:#7c3aed}.badge-info{background:#dbeafe;color:#2563eb}.badge-gray{background:#f3f4f6;color:#6b7280}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
        .count-info{background:#fff;border-radius:12px;padding:12px 20px;margin-bottom:16px;font-size:13px;color:#555;box-shadow:0 2px 8px rgba(0,0,0,.04);display:flex;align-items:center;gap:8px}
        .count-info strong{color:var(--primary)}
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
        <a href="logs.php" class="active"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../../logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
<div class="main-content">
    <div class="topbar"><div><h2>Logs d'activité</h2><p><?php echo $total; ?> événement(s) enregistré(s)</p></div></div>
    <div class="content">
        <div class="toolbar">
            <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
                <div class="search-box"><i class="bi bi-search" style="color:#888"></i><input type="text" name="q" placeholder="Rechercher utilisateur ou action..." value="<?php echo htmlspecialchars($search); ?>"></div>
                <select name="filtre" class="filter-sel" onchange="this.form.submit()">
                    <option value="tous" <?php echo $filtre==='tous'?'selected':''; ?>>Toutes les actions</option>
                    <?php foreach ($actions as $a): ?>
                    <option value="<?php echo $a; ?>" <?php echo $filtre===$a?'selected':''; ?>><?php echo ucfirst($a); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrer</button>
            </form>
        </div>
        <div class="count-info"><i class="bi bi-info-circle" style="color:var(--primary)"></i>Affichage des <strong><?php echo count($logs); ?></strong> derniers événements (max 200)</div>
        <div class="card">
            <?php if (count($logs) > 0):
                $role_b = ['patient'=>['info','Patient'],'medecin'=>['purple','Médecin'],'administrateur'=>['success','Admin']];
                $action_icons = ['connexion'=>'bi-box-arrow-in-right','deconnexion'=>'bi-box-arrow-left','rdv'=>'bi-calendar','consultation'=>'bi-clipboard2-pulse'];
                foreach ($logs as $log):
                    $icon = $action_icons[$log['action']] ?? 'bi-activity';
                    $li_class = 'li-'.preg_replace('/[^a-z0-9]/','',$log['action']);
                    $rb = $role_b[$log['user_role'] ?? ''] ?? ['gray','?'];
            ?>
            <div class="log-item">
                <div class="log-icon <?php echo $li_class; ?> li-default">
                    <i class="bi <?php echo $icon; ?>"></i>
                </div>
                <div class="log-body">
                    <h6>
                        <?php echo htmlspecialchars($log['user_nom'] ?? 'Utilisateur inconnu'); ?>
                        <?php if ($log['user_role']): ?><span class="badge badge-<?php echo $rb[0]; ?>"><?php echo $rb[1]; ?></span><?php endif; ?>
                        — <span style="color:var(--primary);font-weight:700"><?php echo htmlspecialchars(ucfirst($log['action'])); ?></span>
                    </h6>
                    <?php if ($log['description']): ?><div class="desc"><?php echo htmlspecialchars($log['description']); ?></div><?php endif; ?>
                </div>
                <div class="log-meta">
                    <div class="time"><?php echo date('d/m/Y H:i:s', strtotime($log['created_at'])); ?></div>
                    <div class="ip"><?php echo htmlspecialchars($log['adresse_ip'] ?? ''); ?></div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <div class="empty"><i class="bi bi-journal"></i><p>Aucun log trouvé</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>