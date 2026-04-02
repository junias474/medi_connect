<?php
/**
 * Gestion des rendez-vous - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

// Traitement actions
$message = ''; $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['action'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    $rdv_id = intval($_POST['rdv_id'] ?? $_GET['id'] ?? 0);

    if ($rdv_id > 0) {
        // Vérifier que le RDV appartient à ce médecin
        $check = $db->prepare("SELECT id FROM rendez_vous WHERE id = ? AND medecin_id = ?");
        $check->execute([$rdv_id, $medecin_id]);
        if ($check->fetch()) {
            if ($action === 'confirmer') {
                $db->prepare("UPDATE rendez_vous SET statut='confirme' WHERE id=?")->execute([$rdv_id]);
                $message = "Rendez-vous confirmé avec succès.";
            } elseif ($action === 'annuler') {
                $db->prepare("UPDATE rendez_vous SET statut='annule' WHERE id=?")->execute([$rdv_id]);
                $message = "Rendez-vous annulé.";
            } elseif ($action === 'terminer') {
                $db->prepare("UPDATE rendez_vous SET statut='termine' WHERE id=?")->execute([$rdv_id]);
                $message = "Rendez-vous marqué comme terminé.";
            }
        }
    }
}

// Filtres
$filtre_statut = $_GET['statut'] ?? '';
$filtre_date   = $_GET['date'] ?? '';
$search        = $_GET['search'] ?? '';

$where = "WHERE rv.medecin_id = ?";
$params = [$medecin_id];
if ($filtre_statut) { $where .= " AND rv.statut = ?"; $params[] = $filtre_statut; }
if ($filtre_date)   { $where .= " AND rv.date_rendez_vous = ?"; $params[] = $filtre_date; }
if ($search) { $where .= " AND (CONCAT(u.nom,' ',u.prenom) LIKE ? OR rv.motif LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $db->prepare("
    SELECT rv.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, u.telephone as patient_tel, u.email as patient_email
    FROM rendez_vous rv
    INNER JOIN patients p ON rv.patient_id = p.id
    INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
    $where
    ORDER BY rv.date_rendez_vous DESC, rv.heure_debut DESC
");
$stmt->execute($params);
$rendez_vous = $stmt->fetchAll();

$months = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendez-vous - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root { --primary:#667eea; --secondary:#764ba2; --sidebar-w:260px; --gradient:linear-gradient(135deg,#667eea 0%,#764ba2 100%); }
        *{box-sizing:border-box;margin:0;padding:0}
        body{font-family:'Outfit',sans-serif;background:#f5f7fa;color:#333}
        .sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:var(--gradient);color:#fff;z-index:1000;display:flex;flex-direction:column;box-shadow:2px 0 15px rgba(0,0,0,.15)}
        .sidebar-header{padding:30px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.15)}
        .sidebar-header .avatar{width:70px;height:70px;border-radius:50%;background:rgba(255,255,255,.25);border:3px solid rgba(255,255,255,.5);display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 12px}
        .sidebar-header h4{font-size:16px;font-weight:600;margin-bottom:4px}.sidebar-header p{font-size:12px;opacity:.75}
        .sidebar-menu{flex:1;padding:20px 0;overflow-y:auto}
        .sidebar-menu a{display:flex;align-items:center;gap:12px;padding:13px 25px;color:rgba(255,255,255,.85);text-decoration:none;font-size:14px;font-weight:500;border-left:3px solid transparent;transition:all .25s}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.12);color:#fff;border-left-color:#fff}
        .sidebar-menu a i{font-size:18px;width:22px}
        .sidebar-menu .menu-section{padding:10px 25px 5px;font-size:11px;text-transform:uppercase;letter-spacing:1px;opacity:.5}
        .sidebar-footer{padding:20px;border-top:1px solid rgba(255,255,255,.15)}
        .sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.8);text-decoration:none;font-size:14px}
        .main-content{margin-left:var(--sidebar-w)}
        .topbar{background:#fff;padding:18px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.06);position:sticky;top:0;z-index:100}
        .topbar h2{font-size:22px;font-weight:700}
        .content{padding:28px 30px}
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}.alert-danger{background:#fee2e2;color:#dc2626}
        .filters{background:#fff;border-radius:14px;padding:20px;margin-bottom:20px;box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end}
        .filter-group{display:flex;flex-direction:column;gap:6px;flex:1;min-width:160px}
        .filter-group label{font-size:12px;font-weight:600;color:#666}
        .filter-group input,.filter-group select{padding:9px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;transition:border-color .2s}
        .filter-group input:focus,.filter-group select:focus{border-color:var(--primary)}
        .btn{padding:9px 20px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-outline{background:#fff;border:2px solid #e5e7eb;color:#555}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .stats-row{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:20px}
        .mini-stat{background:#fff;border-radius:12px;padding:16px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .mini-stat .num{font-size:26px;font-weight:700;line-height:1}.mini-stat p{font-size:12px;color:#888;margin-top:4px}
        .mini-stat.blue .num{color:var(--primary)}.mini-stat.green .num{color:#16a34a}.mini-stat.orange .num{color:#f59e0b}.mini-stat.red .num{color:#dc2626}
        .table-wrap{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden}
        table{width:100%;border-collapse:collapse}
        th{background:#f8f9ff;padding:14px 18px;text-align:left;font-size:12px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px}
        td{padding:14px 18px;border-top:1px solid #f0f2f5;font-size:14px;vertical-align:middle}
        tr:hover td{background:#fafbff}
        .badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-danger{background:#fee2e2;color:#dc2626}.badge-info{background:#dbeafe;color:#2563eb}
        .badge-secondary{background:#f1f5f9;color:#64748b}
        .action-btns{display:flex;gap:6px}
        .btn-xs{padding:5px 10px;border-radius:8px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:3px;transition:all .2s;text-decoration:none}
        .btn-xs.confirm{background:#dcfce7;color:#16a34a}.btn-xs.cancel{background:#fee2e2;color:#dc2626}.btn-xs.done{background:#dbeafe;color:#2563eb}.btn-xs.view{background:#f3f4f6;color:#374151}
        .btn-xs:hover{opacity:.8}
        .empty-row td{text-align:center;padding:50px;color:#bbb}
        .empty-row i{font-size:40px;display:block;margin-bottom:10px}
        .type-icon{display:inline-flex;align-items:center;gap:5px;font-size:12px;padding:3px 10px;border-radius:20px;background:#f1f5f9;color:#555}
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
        <a href="rendez-vous.php" class="active"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="patients.php"><i class="bi bi-people"></i> Mes patients</a>
        <span class="menu-section">Gestion</span>
        <a href="agenda.php"><i class="bi bi-calendar3"></i> Mon agenda</a>
        <a href="horaires.php"><i class="bi bi-clock"></i> Mes horaires</a>
        <a href="ordonnances.php"><i class="bi bi-file-medical"></i> Ordonnances</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Compte</span>
        <a href="profil.php"><i class="bi bi-person-gear"></i> Mon profil</a>
        <a href="evaluations.php"><i class="bi bi-star"></i> Évaluations</a>
    </nav>
    <div class="sidebar-footer"><a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <h2><i class="bi bi-calendar-check" style="color:var(--primary)"></i> Mes rendez-vous</h2>
        <span style="font-size:13px;color:#888"><?php echo count($rendez_vous); ?> rendez-vous trouvé(s)</span>
    </div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div><?php endif; ?>

        <!-- Stats rapides -->
        <?php
        $counts = ['en_attente'=>0,'confirme'=>0,'termine'=>0,'annule'=>0];
        foreach ($rendez_vous as $r) if (isset($counts[$r['statut']])) $counts[$r['statut']]++;
        ?>
        <div class="stats-row">
            <div class="mini-stat orange"><div class="num"><?php echo $counts['en_attente']; ?></div><p>En attente</p></div>
            <div class="mini-stat green"><div class="num"><?php echo $counts['confirme']; ?></div><p>Confirmés</p></div>
            <div class="mini-stat blue"><div class="num"><?php echo $counts['termine']; ?></div><p>Terminés</p></div>
            <div class="mini-stat red"><div class="num"><?php echo $counts['annule']; ?></div><p>Annulés</p></div>
        </div>

        <!-- Filtres -->
        <form method="GET" class="filters">
            <div class="filter-group">
                <label><i class="bi bi-search"></i> Recherche patient</label>
                <input type="text" name="search" placeholder="Nom du patient..." value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="filter-group">
                <label><i class="bi bi-funnel"></i> Statut</label>
                <select name="statut">
                    <option value="">Tous les statuts</option>
                    <option value="en_attente" <?php echo $filtre_statut==='en_attente'?'selected':''; ?>>En attente</option>
                    <option value="confirme"   <?php echo $filtre_statut==='confirme'?'selected':''; ?>>Confirmé</option>
                    <option value="termine"    <?php echo $filtre_statut==='termine'?'selected':''; ?>>Terminé</option>
                    <option value="annule"     <?php echo $filtre_statut==='annule'?'selected':''; ?>>Annulé</option>
                </select>
            </div>
            <div class="filter-group">
                <label><i class="bi bi-calendar3"></i> Date</label>
                <input type="date" name="date" value="<?php echo htmlspecialchars($filtre_date); ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Filtrer</button>
            <a href="rendez-vous.php" class="btn btn-outline"><i class="bi bi-x-lg"></i> Réinitialiser</a>
        </form>

        <!-- Tableau -->
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th><th>Date & Heure</th><th>Type</th><th>Motif</th><th>Statut</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($rendez_vous) > 0): ?>
                    <?php foreach ($rendez_vous as $rdv):
                        $dt = new DateTime($rdv['date_rendez_vous']);
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($rdv['patient_nom']); ?></strong><br>
                            <small style="color:#888"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($rdv['patient_tel']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo $dt->format('d').' '.$months[(int)$dt->format('n')].' '.$dt->format('Y'); ?></strong><br>
                            <small style="color:#888"><i class="bi bi-clock"></i> <?php echo substr($rdv['heure_debut'],0,5).' – '.substr($rdv['heure_fin'],0,5); ?></small>
                        </td>
                        <td><span class="type-icon"><i class="bi bi-<?php echo $rdv['type_consultation']==='teleconsultation'?'camera-video':'hospital'; ?>"></i><?php echo $rdv['type_consultation']==='teleconsultation'?'Téléconsultation':'Présentiel'; ?></span></td>
                        <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?php echo htmlspecialchars($rdv['motif']); ?>"><?php echo htmlspecialchars(substr($rdv['motif'],0,60)).(strlen($rdv['motif'])>60?'...':''); ?></td>
                        <td>
                            <?php $badges = ['en_attente'=>['warning','Attente'],'confirme'=>['success','Confirmé'],'termine'=>['info','Terminé'],'annule'=>['danger','Annulé'],'patient_absent'=>['secondary','Absent']];
                            $b = $badges[$rdv['statut']] ?? ['secondary',$rdv['statut']]; ?>
                            <span class="badge badge-<?php echo $b[0]; ?>"><?php echo $b[1]; ?></span>
                        </td>
                        <td>
                            <div class="action-btns">
                                <?php if ($rdv['statut'] === 'en_attente'): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="action" value="confirmer"><input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>"><button type="submit" class="btn-xs confirm"><i class="bi bi-check-lg"></i> Confirmer</button></form>
                                    <form method="POST" style="display:inline"><input type="hidden" name="action" value="annuler"><input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>"><button type="submit" class="btn-xs cancel"><i class="bi bi-x-lg"></i></button></form>
                                <?php elseif ($rdv['statut'] === 'confirme'): ?>
                                    <form method="POST" style="display:inline"><input type="hidden" name="action" value="terminer"><input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>"><button type="submit" class="btn-xs done"><i class="bi bi-check-all"></i> Terminer</button></form>
                                    <form method="POST" style="display:inline"><input type="hidden" name="action" value="annuler"><input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>"><button type="submit" class="btn-xs cancel"><i class="bi bi-x-lg"></i></button></form>
                                <?php endif; ?>
                                <a href="consultations.php?rdv_id=<?php echo $rdv['id']; ?>" class="btn-xs view"><i class="bi bi-eye"></i></a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="empty-row"><td colspan="6"><i class="bi bi-calendar-x"></i>Aucun rendez-vous trouvé</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>