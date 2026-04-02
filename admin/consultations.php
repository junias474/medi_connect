<?php
/**
 * Consultations - Administrateur
 */
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') { header("Location: ../login.php"); exit(); }
try {
    $db = Database::getInstance()->getConnection();
    $search = trim($_GET['q'] ?? '');
    $where = "WHERE 1=1"; $params = [];
    if ($search) { $where .= " AND (CONCAT(up.nom,' ',up.prenom) LIKE ? OR CONCAT(um.nom,' ',um.prenom) LIKE ?)"; $s="%$search%"; $params=[$s,$s]; }
    $stmt = $db->prepare("
        SELECT c.*, CONCAT(up.nom,' ',up.prenom) as patient_nom,
               CONCAT(um.nom,' ',um.prenom) as medecin_nom, m.specialite,
               rv.date_rendez_vous, rv.type_consultation
        FROM consultations c
        INNER JOIN patients p ON c.patient_id=p.id
        INNER JOIN utilisateurs up ON p.utilisateur_id=up.id
        INNER JOIN medecins m ON c.medecin_id=m.id
        INNER JOIN utilisateurs um ON m.utilisateur_id=um.id
        INNER JOIN rendez_vous rv ON c.rendez_vous_id=rv.id
        $where
        ORDER BY c.date_consultation DESC
    ");
    $stmt->execute($params);
    $consultations = $stmt->fetchAll();
    $total = $db->query("SELECT COUNT(*) FROM consultations")->fetchColumn();
    $ce_mois = $db->query("SELECT COUNT(*) FROM consultations WHERE MONTH(date_consultation)=MONTH(NOW()) AND YEAR(date_consultation)=YEAR(NOW())")->fetchColumn();
} catch(PDOException $e) { error_log($e->getMessage()); $consultations=[]; $total=0; $ce_mois=0; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Consultations - Admin</title>
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
        .mini-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:22px}
        .mini-card{background:#fff;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,.05);text-align:center}
        .mini-card .n{font-size:28px;font-weight:800;color:var(--primary)}.mini-card p{font-size:12px;color:#888;margin-top:4px}
        .toolbar{display:flex;gap:12px;margin-bottom:18px}
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
        .badge-success{background:#dcfce7;color:#16a34a}.badge-info{background:#dbeafe;color:#2563eb}.badge-gray{background:#f3f4f6;color:#6b7280}
        .consult-preview{max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;font-size:12px;color:#888}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
        /* Modal détail */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal{background:#fff;border-radius:20px;padding:32px;max-width:600px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:80vh;overflow-y:auto}
        .modal h4{font-size:18px;font-weight:700;margin-bottom:20px;display:flex;align-items:center;gap:10px;color:var(--primary)}
        .detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .detail-item label{font-size:11px;text-transform:uppercase;color:#888;font-weight:700;display:block;margin-bottom:4px}
        .detail-item p{font-size:13px;font-weight:500}
        .detail-full{grid-column:1/-1}
        .btn-close-modal{float:right;background:#f0f2f8;border:none;border-radius:10px;padding:8px 16px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Outfit',sans-serif}
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
        <a href="consultations.php" class="active"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../../logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
<div class="main-content">
    <div class="topbar"><div><h2>Consultations</h2><p><?php echo count($consultations); ?> trouvée(s)</p></div></div>
    <div class="content">
        <div class="mini-stats">
            <div class="mini-card"><div class="n"><?php echo $total; ?></div><p>Total consultations</p></div>
            <div class="mini-card"><div class="n"><?php echo $ce_mois; ?></div><p>Ce mois-ci</p></div>
            <div class="mini-card"><div class="n"><?php echo count($consultations); ?></div><p>Résultats filtrés</p></div>
        </div>
        <div class="toolbar">
            <form method="GET" style="display:flex;gap:12px;flex:1">
                <div class="search-box"><i class="bi bi-search" style="color:#888"></i><input type="text" name="q" placeholder="Rechercher patient ou médecin..." value="<?php echo htmlspecialchars($search); ?>"></div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Chercher</button>
            </form>
        </div>
        <div class="card">
            <?php if (count($consultations) > 0): ?>
            <table>
                <thead><tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Diagnostic</th><th>Durée</th><th>Statut</th><th>Détail</th></tr></thead>
                <tbody>
                <?php foreach ($consultations as $c): ?>
                <tr>
                    <td style="font-size:12px;color:#555"><?php echo date('d/m/Y H:i', strtotime($c['date_consultation'])); ?></td>
                    <td><strong><?php echo htmlspecialchars($c['patient_nom']); ?></strong></td>
                    <td>Dr. <?php echo htmlspecialchars($c['medecin_nom']); ?><br><span style="font-size:11px;color:#888"><?php echo $c['specialite']; ?></span></td>
                    <td><div class="consult-preview"><?php echo htmlspecialchars($c['diagnostic'] ?? 'Non renseigné'); ?></div></td>
                    <td><?php echo $c['duree_consultation'] ? $c['duree_consultation'].' min' : '—'; ?></td>
                    <td><span class="badge <?php echo $c['statut']==='termine'?'badge-success':'badge-info'; ?>"><?php echo ucfirst($c['statut']); ?></span></td>
                    <td>
                        <button class="btn" style="padding:5px 12px;background:#eef0ff;color:var(--primary);font-size:12px;border-radius:8px"
                            onclick="showDetail(<?php echo htmlspecialchars(json_encode($c)); ?>)">
                            <i class="bi bi-eye"></i> Voir
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?><div class="empty"><i class="bi bi-clipboard2-x"></i><p>Aucune consultation trouvée</p></div><?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal détail -->
<div class="modal-overlay" id="detailModal">
    <div class="modal">
        <button class="btn-close-modal" onclick="document.getElementById('detailModal').classList.remove('open')">✕ Fermer</button>
        <h4><i class="bi bi-clipboard2-pulse"></i> Détail consultation</h4>
        <div class="detail-grid" id="detailContent"></div>
    </div>
</div>

<script>
function showDetail(c) {
    const fields = [
        ['Patient', c.patient_nom], ['Médecin', 'Dr. '+c.medecin_nom],
        ['Date', c.date_consultation], ['Durée', (c.duree_consultation||'—')+' min'],
        ['Diagnostic', c.diagnostic||'Non renseigné', true],
        ['Prescription', c.prescription||'—', true],
        ['Examens demandés', c.examens_demandes||'—', true],
        ['Notes médicales', c.notes_medicales||'—', true],
        ['Recommandations', c.recommandations||'—', true],
    ];
    document.getElementById('detailContent').innerHTML = fields.map(([l,v,full])=>
        `<div class="detail-item${full?' detail-full':''}"><label>${l}</label><p>${String(v).replace(/</g,'&lt;')}</p></div>`
    ).join('');
    document.getElementById('detailModal').classList.add('open');
}
</script>
</body>
</html>