<?php
/**
 * Rendez-vous - Administrateur
 */
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') {
    header("Location: ../login.php"); exit();
}
$message = ''; $message_type = '';
try {
    $db = Database::getInstance()->getConnection();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $id = (int)($_POST['id'] ?? 0);
        $map = ['confirmer'=>'confirme','annuler'=>'annule','terminer'=>'termine'];
        if (isset($map[$action]) && $id) {
            $db->prepare("UPDATE rendez_vous SET statut=? WHERE id=?")->execute([$map[$action],$id]);
            $message = "Statut mis à jour."; $message_type = 'success';
        }
    }
    $filtre = $_GET['filtre'] ?? 'tous';
    $search = trim($_GET['q'] ?? '');
    $where = "WHERE 1=1";
    $params = [];
    if ($filtre !== 'tous') { $where .= " AND rv.statut=?"; $params[] = $filtre; }
    if ($search) { $where .= " AND (CONCAT(up.nom,' ',up.prenom) LIKE ? OR CONCAT(um.nom,' ',um.prenom) LIKE ?)"; $s="%$search%"; $params[]=$s; $params[]=$s; }
    $stmt = $db->prepare("
        SELECT rv.*, CONCAT(up.nom,' ',up.prenom) as patient_nom, up.telephone as patient_tel,
               CONCAT(um.nom,' ',um.prenom) as medecin_nom, m.specialite
        FROM rendez_vous rv
        INNER JOIN patients p ON rv.patient_id=p.id
        INNER JOIN utilisateurs up ON p.utilisateur_id=up.id
        INNER JOIN medecins m ON rv.medecin_id=m.id
        INNER JOIN utilisateurs um ON m.utilisateur_id=um.id
        $where
        ORDER BY rv.date_rendez_vous DESC, rv.heure_debut DESC
    ");
    $stmt->execute($params);
    $rdvs = $stmt->fetchAll();
    $counts = [];
    foreach (['tous'=>'1=1','en_attente'=>"statut='en_attente'",'confirme'=>"statut='confirme'",'termine'=>"statut='termine'",'annule'=>"statut='annule'"] as $k=>$w) {
        $counts[$k] = $db->query("SELECT COUNT(*) FROM rendez_vous WHERE $w")->fetchColumn();
    }
} catch(PDOException $e) { error_log($e->getMessage()); $rdvs=[]; $counts=array_fill_keys(['tous','en_attente','confirme','termine','annule'],0); }
$months=['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Rendez-vous - Admin</title>
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
        .filter-tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .tab{padding:9px 18px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;color:#555;background:#fff;border:2px solid #e8eaf5;transition:all .2s;display:flex;align-items:center;gap:6px}
        .tab:hover,.tab.active{background:var(--gradient);color:#fff;border-color:transparent}
        .tab .count{background:rgba(255,255,255,.25);border-radius:20px;padding:1px 7px;font-size:11px}
        .tab:not(.active) .count{background:#f0f2f8;color:#888}
        .toolbar{display:flex;gap:12px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
        .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:12px;padding:10px 16px;border:2px solid #e8eaf5;flex:1}
        .search-box input{border:none;outline:none;font-size:14px;font-family:'Outfit',sans-serif;width:100%;background:transparent}
        .btn{padding:10px 20px;border-radius:12px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:8px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#888;font-weight:700;padding:14px 20px;text-align:left;background:#fafbff}
        td{padding:13px 20px;font-size:13px;border-bottom:1px solid #f0f2f5;vertical-align:middle}
        tr:last-child td{border-bottom:none}tr:hover td{background:#fafbff}
        .badge{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-danger{background:#fee2e2;color:#dc2626}.badge-info{background:#dbeafe;color:#2563eb}.badge-gray{background:#f3f4f6;color:#6b7280}
        .date-cell{text-align:center;background:#f8f9ff;border-radius:8px;padding:6px 10px;min-width:56px;display:inline-block}
        .date-cell .d{font-size:18px;font-weight:800;color:var(--primary);line-height:1}
        .date-cell .m{font-size:10px;text-transform:uppercase;color:#888}
        .actions{display:flex;gap:5px;flex-wrap:wrap}
        .btn-xs{padding:5px 10px;border-radius:7px;font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:3px;transition:all .2s}
        .btn-xs-success{background:#dcfce7;color:#16a34a}.btn-xs-danger{background:#fee2e2;color:#dc2626}.btn-xs-info{background:#dbeafe;color:#2563eb}
        .btn-xs:hover{opacity:.8}
        .alert{padding:12px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
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
        <a href="rendez-vous.php" class="active"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../../logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
<div class="main-content">
    <div class="topbar"><div><h2>Rendez-vous</h2><p><?php echo count($rdvs); ?> trouvé(s)</p></div></div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-<?php echo $message_type; ?>"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <div class="filter-tabs">
            <?php foreach (['tous'=>'Tous','en_attente'=>'En attente','confirme'=>'Confirmés','termine'=>'Terminés','annule'=>'Annulés'] as $k=>$l): ?>
            <a href="?filtre=<?php echo $k; ?>" class="tab <?php echo $filtre===$k?'active':''; ?>">
                <?php echo $l; ?> <span class="count"><?php echo $counts[$k]; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="toolbar">
            <form method="GET" style="display:flex;gap:12px;flex:1">
                <input type="hidden" name="filtre" value="<?php echo $filtre; ?>">
                <div class="search-box"><i class="bi bi-search" style="color:#888"></i><input type="text" name="q" placeholder="Rechercher patient ou médecin..." value="<?php echo htmlspecialchars($search); ?>"></div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Chercher</button>
            </form>
        </div>
        <div class="card">
            <?php if (count($rdvs) > 0): ?>
            <table>
                <thead><tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Type</th><th>Horaire</th><th>Statut</th><th>Actions</th></tr></thead>
                <tbody>
                <?php foreach ($rdvs as $r):
                    $dt = new DateTime($r['date_rendez_vous']);
                    $badges = ['en_attente'=>['warning','En attente'],'confirme'=>['success','Confirmé'],'annule'=>['danger','Annulé'],'termine'=>['info','Terminé'],'patient_absent'=>['gray','Absent']];
                    $b = $badges[$r['statut']] ?? ['gray','?'];
                ?>
                <tr>
                    <td>
                        <div class="date-cell">
                            <div class="d"><?php echo $dt->format('d'); ?></div>
                            <div class="m"><?php echo $months[(int)$dt->format('n')]; ?></div>
                        </div>
                    </td>
                    <td><strong><?php echo htmlspecialchars($r['patient_nom']); ?></strong><br><span style="font-size:11px;color:#888"><?php echo htmlspecialchars($r['patient_tel']); ?></span></td>
                    <td>Dr. <?php echo htmlspecialchars($r['medecin_nom']); ?><br><span style="font-size:11px;color:#888"><?php echo htmlspecialchars($r['specialite']); ?></span></td>
                    <td><span class="badge badge-<?php echo $r['type_consultation']==='teleconsultation'?'info':'gray'; ?>"><?php echo $r['type_consultation']==='teleconsultation'?'📹 Télé':'🏥 Présentiel'; ?></span></td>
                    <td style="font-size:12px"><?php echo substr($r['heure_debut'],0,5).' - '.substr($r['heure_fin'],0,5); ?></td>
                    <td><span class="badge badge-<?php echo $b[0]; ?>"><?php echo $b[1]; ?></span></td>
                    <td>
                        <div class="actions">
                            <?php if ($r['statut']==='en_attente'): ?>
                            <form method="POST" style="display:inline"><input type="hidden" name="action" value="confirmer"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><button type="submit" class="btn-xs btn-xs-success"><i class="bi bi-check"></i> Confirmer</button></form>
                            <?php endif; ?>
                            <?php if (in_array($r['statut'],['en_attente','confirme'])): ?>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Annuler ce RDV ?')"><input type="hidden" name="action" value="annuler"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><button type="submit" class="btn-xs btn-xs-danger"><i class="bi bi-x"></i> Annuler</button></form>
                            <form method="POST" style="display:inline"><input type="hidden" name="action" value="terminer"><input type="hidden" name="id" value="<?php echo $r['id']; ?>"><button type="submit" class="btn-xs btn-xs-info"><i class="bi bi-check-all"></i> Terminer</button></form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty"><i class="bi bi-calendar-x"></i><p>Aucun rendez-vous trouvé</p></div><?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>