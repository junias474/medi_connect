<?php
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') { header("Location: ../../login.php"); exit(); }
try {
    $db = Database::getInstance()->getConnection();
    $filtre = $_GET['filtre'] ?? 'tous';
    $where = "WHERE 1=1"; $params = [];
    if ($filtre !== 'tous') { $where .= " AND pa.statut=?"; $params[] = $filtre; }
    $stmt = $db->prepare("
        SELECT pa.*, CONCAT(u.nom,' ',u.prenom) as patient_nom,
               c.date_consultation, CONCAT(um.nom,' ',um.prenom) as medecin_nom
        FROM paiements pa
        INNER JOIN patients p ON pa.patient_id=p.id
        INNER JOIN utilisateurs u ON p.utilisateur_id=u.id
        INNER JOIN consultations c ON pa.consultation_id=c.id
        INNER JOIN medecins m ON c.medecin_id=m.id
        INNER JOIN utilisateurs um ON m.utilisateur_id=um.id
        $where ORDER BY pa.date_paiement DESC
    ");
    $stmt->execute($params);
    $paiements = $stmt->fetchAll();
    $total_valide = $db->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut='valide'")->fetchColumn();
    $total_attente = $db->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut='en_attente'")->fetchColumn();
    $counts = [];
    foreach (['tous'=>'1=1','valide'=>"statut='valide'",'en_attente'=>"statut='en_attente'",'echoue'=>"statut='echoue'",'rembourse'=>"statut='rembourse'"] as $k=>$w) {
        $counts[$k] = $db->query("SELECT COUNT(*) FROM paiements WHERE $w")->fetchColumn();
    }
} catch(PDOException $e) { error_log($e->getMessage()); $paiements=[]; $total_valide=0; $total_attente=0; $counts=array_fill_keys(['tous','valide','en_attente','echoue','rembourse'],0); }
$SIDEBAR = <<<HTML
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div><div><h3>Consultation Médicale</h3><p>Administration</p></div></div>
        <div class="admin-chip">
            <div class="av">AD</div>
            <div class="info"><h5>Administrateur</h5><p>Super Admin</p></div>
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
        <a href="paiements.php" class="active"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
HTML;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Paiements - Admin</title>
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
        .revenue-cards{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px}
        .rev-card{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 10px rgba(0,0,0,.05)}
        .rev-card .label{font-size:12px;color:#888;margin-bottom:8px;font-weight:600}
        .rev-card .amount{font-size:24px;font-weight:800}
        .rev-card .amount.green{color:#16a34a}.rev-card .amount.orange{color:#ca8a04}.rev-card .amount.blue{color:var(--primary)}
        .filter-tabs{display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap}
        .tab{padding:9px 18px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:600;color:#555;background:#fff;border:2px solid #e8eaf5;transition:all .2s;display:flex;align-items:center;gap:6px}
        .tab:hover,.tab.active{background:var(--gradient);color:#fff;border-color:transparent}
        .tab .count{background:rgba(255,255,255,.25);border-radius:20px;padding:1px 7px;font-size:11px}
        .tab:not(.active) .count{background:#f0f2f8;color:#888}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#888;font-weight:700;padding:14px 20px;text-align:left;background:#fafbff}
        td{padding:13px 20px;font-size:13px;border-bottom:1px solid #f0f2f5;vertical-align:middle}
        tr:last-child td{border-bottom:none}tr:hover td{background:#fafbff}
        .badge{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-danger{background:#fee2e2;color:#dc2626}.badge-gray{background:#f3f4f6;color:#6b7280}.badge-info{background:#dbeafe;color:#2563eb}
        .mode-icon{font-size:16px}
        .amount-cell{font-weight:800;font-size:15px;color:#1a1a2e}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
    </style>
</head>
<body>
<?php echo $SIDEBAR; ?>
<div class="main-content">
    <div class="topbar"><div><h2>Paiements</h2><p><?php echo count($paiements); ?> transaction(s)</p></div></div>
    <div class="content">
        <div class="revenue-cards">
            <div class="rev-card">
                <div class="label"><i class="bi bi-check-circle text-success"></i> Revenus validés</div>
                <div class="amount green"><?php echo number_format($total_valide,0,',',' '); ?> FCFA</div>
            </div>
            <div class="rev-card">
                <div class="label"><i class="bi bi-hourglass"></i> En attente</div>
                <div class="amount orange"><?php echo number_format($total_attente,0,',',' '); ?> FCFA</div>
            </div>
            <div class="rev-card">
                <div class="label"><i class="bi bi-graph-up"></i> Total transactions</div>
                <div class="amount blue"><?php echo $counts['tous']; ?></div>
            </div>
        </div>
        <div class="filter-tabs">
            <?php foreach (['tous'=>'Tous','valide'=>'Validés','en_attente'=>'En attente','echoue'=>'Échoués','rembourse'=>'Remboursés'] as $k=>$l): ?>
            <a href="?filtre=<?php echo $k; ?>" class="tab <?php echo $filtre===$k?'active':''; ?>">
                <?php echo $l; ?> <span class="count"><?php echo $counts[$k]; ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="card">
            <?php if (count($paiements) > 0): ?>
            <table>
                <thead><tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Mode</th><th>Montant</th><th>Référence</th><th>Statut</th></tr></thead>
                <tbody>
                <?php foreach ($paiements as $p):
                    $mode_icons = ['carte_bancaire'=>'💳','mobile_money'=>'📱','especes'=>'💵','assurance'=>'🏥'];
                    $mode_labels = ['carte_bancaire'=>'Carte','mobile_money'=>'Mobile Money','especes'=>'Espèces','assurance'=>'Assurance'];
                    $sb = ['valide'=>'success','en_attente'=>'warning','echoue'=>'danger','rembourse'=>'info'][$p['statut']] ?? 'gray';
                ?>
                <tr>
                    <td style="font-size:12px;color:#555"><?php echo date('d/m/Y H:i', strtotime($p['date_paiement'])); ?></td>
                    <td><?php echo htmlspecialchars($p['patient_nom']); ?></td>
                    <td>Dr. <?php echo htmlspecialchars($p['medecin_nom']); ?></td>
                    <td><span class="mode-icon"><?php echo $mode_icons[$p['mode_paiement']] ?? '?'; ?></span> <?php echo $mode_labels[$p['mode_paiement']] ?? $p['mode_paiement']; ?></td>
                    <td><span class="amount-cell"><?php echo number_format($p['montant'],0,',',' '); ?> FCFA</span></td>
                    <td style="font-size:11px;color:#888;font-family:monospace"><?php echo htmlspecialchars($p['reference_transaction'] ?? '—'); ?></td>
                    <td><span class="badge badge-<?php echo $sb; ?>"><?php echo ucfirst(str_replace('_',' ',$p['statut'])); ?></span></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty"><i class="bi bi-credit-card"></i><p>Aucun paiement trouvé</p></div><?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>