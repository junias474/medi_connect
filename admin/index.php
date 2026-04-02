<?php
/**
 * Tableau de bord Administrateur
 * Application de Consultation Médicale
 */
require_once '../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') {
    header("Location: ../login.php"); exit();
}

$admin_id = $_SESSION['admin_id'];
$user_id  = $_SESSION['user_id'];

try {
    $db = Database::getInstance()->getConnection();

    // Statistiques globales
    $stats = [];

    $stmt = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='medecin' AND statut='actif'");
    $stats['medecins'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE role='patient' AND statut='actif'");
    $stats['patients'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM rendez_vous WHERE DATE(created_at) = CURDATE()");
    $stats['rdv_aujourdhui'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM rendez_vous WHERE statut='en_attente'");
    $stats['rdv_attente'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM consultations");
    $stats['consultations'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COALESCE(SUM(montant),0) FROM paiements WHERE statut='valide'");
    $stats['revenus'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM utilisateurs WHERE statut='suspendu'");
    $stats['suspendus'] = $stmt->fetchColumn();

    $stmt = $db->query("SELECT COUNT(*) FROM messages WHERE lu=0");
    $stats['messages_non_lus'] = $stmt->fetchColumn();

    // Derniers utilisateurs inscrits
    $stmt = $db->query("SELECT nom, prenom, email, role, date_inscription, statut FROM utilisateurs ORDER BY date_inscription DESC LIMIT 8");
    $derniers_users = $stmt->fetchAll();

    // Derniers rendez-vous
    $stmt = $db->query("
        SELECT rv.*, CONCAT(up.nom,' ',up.prenom) as patient_nom,
               CONCAT(um.nom,' ',um.prenom) as medecin_nom, m.specialite
        FROM rendez_vous rv
        INNER JOIN patients p ON rv.patient_id=p.id
        INNER JOIN utilisateurs up ON p.utilisateur_id=up.id
        INNER JOIN medecins m ON rv.medecin_id=m.id
        INNER JOIN utilisateurs um ON m.utilisateur_id=um.id
        ORDER BY rv.created_at DESC LIMIT 6
    ");
    $derniers_rdv = $stmt->fetchAll();

    // RDV par statut (pour graphique)
    $stmt = $db->query("SELECT statut, COUNT(*) as nb FROM rendez_vous GROUP BY statut");
    $rdv_stats = $stmt->fetchAll();
    $rdv_chart = ['en_attente'=>0,'confirme'=>0,'termine'=>0,'annule'=>0,'patient_absent'=>0];
    foreach ($rdv_stats as $r) $rdv_chart[$r['statut']] = $r['nb'];

    // Médecins les plus actifs
    $stmt = $db->query("
        SELECT CONCAT(u.nom,' ',u.prenom) as nom, m.specialite, m.nombre_consultations, m.note_moyenne
        FROM medecins m INNER JOIN utilisateurs u ON m.utilisateur_id=u.id
        ORDER BY m.nombre_consultations DESC LIMIT 5
    ");
    $top_medecins = $stmt->fetchAll();

    // Notifications non lues
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE utilisateur_id=? AND lu=0");
    $stmt->execute([$user_id]);
    $notif_count = $stmt->fetchColumn();

} catch(PDOException $e) {
    error_log($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --sidebar-w: 270px;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #f0f2f8; color: #1a1a2e; }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0;
            height: 100vh; width: var(--sidebar-w);
            background: var(--gradient);
            color: #fff; z-index: 1000;
            display: flex; flex-direction: column;
            box-shadow: 4px 0 20px rgba(102,126,234,.25);
        }
        .sidebar-brand {
            padding: 28px 22px 20px;
            border-bottom: 1px solid rgba(255,255,255,.15);
        }
        .sidebar-brand .logo {
            display: flex; align-items: center; gap: 12px;
        }
        .sidebar-brand .logo-icon {
            width: 42px; height: 42px; border-radius: 12px;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .sidebar-brand h3 { font-size: 17px; font-weight: 700; }
        .sidebar-brand p { font-size: 11px; opacity: .7; margin-top: 2px; }
        .admin-chip {
            margin-top: 14px; background: rgba(255,255,255,.15);
            border-radius: 10px; padding: 10px 14px;
            display: flex; align-items: center; gap: 10px;
        }
        .admin-chip .av {
            width: 34px; height: 34px; border-radius: 50%;
            background: rgba(255,255,255,.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 700;
        }
        .admin-chip .info h5 { font-size: 13px; font-weight: 600; }
        .admin-chip .info p { font-size: 11px; opacity: .75; }

        .sidebar-menu { flex: 1; padding: 18px 0; overflow-y: auto; }
        .menu-section {
            padding: 12px 22px 5px;
            font-size: 10px; text-transform: uppercase;
            letter-spacing: 1.5px; opacity: .5; font-weight: 600;
        }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 22px; color: rgba(255,255,255,.82);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .22s;
            position: relative;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,.13); color: #fff;
            border-left-color: rgba(255,255,255,.9);
        }
        .sidebar-menu a i { font-size: 17px; width: 20px; }
        .sidebar-menu a .badge-menu {
            margin-left: auto; background: #ef4444;
            color: #fff; border-radius: 20px;
            padding: 2px 8px; font-size: 10px; font-weight: 700;
        }
        .sidebar-footer {
            padding: 18px 22px;
            border-top: 1px solid rgba(255,255,255,.15);
        }
        .sidebar-footer a {
            display: flex; align-items: center; gap: 10px;
            color: rgba(255,255,255,.8); text-decoration: none; font-size: 13px;
        }
        .sidebar-footer a:hover { color: #fff; }

        /* MAIN */
        .main-content { margin-left: var(--sidebar-w); }
        .topbar {
            background: #fff; padding: 16px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,.06);
            position: sticky; top: 0; z-index: 100;
        }
        .topbar-left h2 { font-size: 21px; font-weight: 800; color: #1a1a2e; }
        .topbar-left p { font-size: 13px; color: #888; margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 16px; }
        .icon-btn {
            position: relative; width: 40px; height: 40px;
            border-radius: 12px; background: #f0f2f8;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #555; cursor: pointer;
            border: none; transition: all .2s;
        }
        .icon-btn:hover { background: #e8eaf5; color: var(--primary); }
        .icon-btn .dot {
            position: absolute; top: 6px; right: 6px;
            width: 8px; height: 8px; background: #ef4444; border-radius: 50%;
        }

        /* CONTENT */
        .content { padding: 26px 30px; }

        /* ALERT BANNER */
        .alert-banner {
            background: linear-gradient(135deg,#fef3c7,#fde68a);
            border-left: 4px solid #f59e0b; border-radius: 12px;
            padding: 14px 18px; margin-bottom: 22px;
            display: flex; align-items: center; gap: 12px; font-size: 13px;
        }
        .alert-banner i { font-size: 20px; color: #d97706; }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px; margin-bottom: 22px;
        }
        @media(max-width:1200px){ .stats-grid { grid-template-columns: repeat(2,1fr); } }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 20px 22px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05);
            display: flex; align-items: center; gap: 16px;
            transition: transform .3s, box-shadow .3s;
            text-decoration: none; color: inherit;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,.1); }
        .stat-icon {
            width: 50px; height: 50px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; flex-shrink: 0;
        }
        .si-blue   { background: linear-gradient(135deg,#667eea,#764ba2); color:#fff; }
        .si-green  { background: linear-gradient(135deg,#11998e,#38ef7d); color:#fff; }
        .si-orange { background: linear-gradient(135deg,#f093fb,#f5576c); color:#fff; }
        .si-cyan   { background: linear-gradient(135deg,#4facfe,#00f2fe); color:#fff; }
        .si-pink   { background: linear-gradient(135deg,#fa709a,#fee140); color:#fff; }
        .si-teal   { background: linear-gradient(135deg,#43e97b,#38f9d7); color:#fff; }
        .si-red    { background: linear-gradient(135deg,#f5576c,#f093fb); color:#fff; }
        .si-indigo { background: linear-gradient(135deg,#4776e6,#8e54e9); color:#fff; }
        .stat-info h3 { font-size: 26px; font-weight: 800; line-height: 1; }
        .stat-info p  { font-size: 12px; color: #888; margin-top: 4px; font-weight: 500; }
        .stat-info .trend { font-size: 11px; color: #22c55e; margin-top: 4px; font-weight: 600; }

        /* GRID LAYOUT */
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 18px; }
        .grid-3-2 { display: grid; grid-template-columns: 1.6fr 1fr; gap: 18px; margin-bottom: 18px; }
        @media(max-width:900px){ .grid-2,.grid-3-2 { grid-template-columns:1fr; } }

        /* CARDS */
        .card {
            background: #fff; border-radius: 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,.05); overflow: hidden;
        }
        .card-header {
            padding: 18px 22px; border-bottom: 1px solid #f0f2f5;
            display: flex; justify-content: space-between; align-items: center;
        }
        .card-header h5 {
            font-size: 15px; font-weight: 700;
            display: flex; align-items: center; gap: 8px;
        }
        .card-header h5 i { color: var(--primary); }
        .card-header a {
            font-size: 12px; color: var(--primary);
            text-decoration: none; font-weight: 600;
            padding: 6px 12px; background: #eef0ff;
            border-radius: 8px; transition: all .2s;
        }
        .card-header a:hover { background: var(--primary); color: #fff; }
        .card-body { padding: 18px 22px; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; }
        th {
            font-size: 11px; text-transform: uppercase; letter-spacing: .8px;
            color: #888; font-weight: 700; padding: 0 0 12px; text-align: left;
        }
        td { padding: 11px 0; font-size: 13px; border-bottom: 1px solid #f0f2f5; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbff; }

        /* BADGE */
        .badge {
            display: inline-block; padding: 4px 11px;
            border-radius: 20px; font-size: 11px; font-weight: 700;
        }
        .badge-success  { background:#dcfce7; color:#16a34a; }
        .badge-warning  { background:#fef9c3; color:#ca8a04; }
        .badge-danger   { background:#fee2e2; color:#dc2626; }
        .badge-info     { background:#dbeafe; color:#2563eb; }
        .badge-purple   { background:#ede9fe; color:#7c3aed; }
        .badge-gray     { background:#f3f4f6; color:#6b7280; }

        /* AVATAR */
        .user-av {
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--gradient); display: inline-flex;
            align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 12px;
        }
        .user-cell { display: flex; align-items: center; gap: 10px; }
        .user-cell .uinfo h6 { font-size: 13px; font-weight: 600; margin-bottom: 1px; }
        .user-cell .uinfo p { font-size: 11px; color: #888; }

        /* RDV MINI */
        .rdv-item {
            display: flex; align-items: center; gap: 14px;
            padding: 12px; background: #f8f9ff; border-radius: 10px;
            margin-bottom: 8px; border-left: 3px solid transparent;
            transition: all .2s;
        }
        .rdv-item:hover { background: #eef0ff; border-left-color: var(--primary); }
        .rdv-date {
            text-align: center; min-width: 46px;
            background: #fff; border-radius: 8px; padding: 6px 4px;
        }
        .rdv-date .day { font-size: 18px; font-weight: 800; color: var(--primary); line-height:1; }
        .rdv-date .mon { font-size: 10px; text-transform: uppercase; color: #888; }
        .rdv-info { flex: 1; }
        .rdv-info h6 { font-size: 13px; font-weight: 600; margin-bottom: 2px; }
        .rdv-info p { font-size: 11px; color: #888; }

        /* DONUT CHART */
        .chart-wrap { display: flex; align-items: center; gap: 24px; padding: 10px 0; }
        .donut-svg { flex-shrink: 0; }
        .legend { flex: 1; }
        .legend-item {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 10px; font-size: 13px;
        }
        .legend-dot { width: 12px; height: 12px; border-radius: 4px; flex-shrink: 0; }
        .legend-item span { font-weight: 600; margin-left: auto; }

        /* TOP MEDECINS */
        .med-item {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 0; border-bottom: 1px solid #f0f2f5;
        }
        .med-item:last-child { border-bottom: none; }
        .med-rank {
            width: 26px; height: 26px; border-radius: 8px;
            background: var(--gradient); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 800; flex-shrink: 0;
        }
        .med-info { flex: 1; }
        .med-info h6 { font-size: 13px; font-weight: 600; }
        .med-info p { font-size: 11px; color: #888; }
        .med-stats { text-align: right; }
        .med-stats .n { font-size: 16px; font-weight: 800; color: var(--primary); }
        .med-stats p { font-size: 10px; color: #888; }
        .stars { color: #f59e0b; font-size: 11px; }

        /* QUICK ACTIONS */
        .quick-grid {
            display: grid; grid-template-columns: repeat(4,1fr);
            gap: 12px; margin-bottom: 20px;
        }
        @media(max-width:900px){ .quick-grid { grid-template-columns: repeat(2,1fr); } }
        .quick-btn {
            display: flex; flex-direction: column; align-items: center; gap: 10px;
            background: #fff; border-radius: 14px; padding: 20px 14px;
            text-decoration: none; color: #333;
            box-shadow: 0 2px 10px rgba(0,0,0,.05); transition: all .25s;
            font-size: 13px; font-weight: 600; text-align: center;
        }
        .quick-btn i { font-size: 26px; color: var(--primary); }
        .quick-btn:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(102,126,234,.2); color: var(--primary); }

        .empty { text-align: center; padding: 30px; color: #bbb; }
        .empty i { font-size: 36px; display: block; margin-bottom: 8px; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div>
                <h3><?php echo SITE_NAME; ?></h3>
                <p>Panneau d'administration</p>
            </div>
        </div>
        <div class="admin-chip">
            <div class="av"><?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?></div>
            <div class="info">
                <h5><?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h5>
                <p>Super Administrateur</p>
            </div>
        </div>
    </div>

    <nav class="sidebar-menu">
        <span class="menu-section">Tableau de bord</span>
        <a href="index.php" class="active"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a>

        <span class="menu-section">Gestion</span>
        <a href="medecins.php"><i class="bi bi-person-badge"></i> Médecins</a>
        <a href="patients.php"><i class="bi bi-people"></i> Patients</a>
        <a href="utilisateurs.php"><i class="bi bi-person-gear"></i> Utilisateurs</a>

        <span class="menu-section">Activité</span>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages
            <?php if (!empty($stats['messages_non_lus']) && $stats['messages_non_lus'] > 0): ?>
                <span class="badge-menu"><?php echo $stats['messages_non_lus']; ?></span>
            <?php endif; ?>
        </a>

        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>

    <div class="sidebar-footer">
        <a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>Vue d'ensemble</h2>
            <p><?php echo strftime('%A %d %B %Y') ?: date('d/m/Y'); ?> &mdash; Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?> 👋</p>
        </div>
        <div class="topbar-right">
            <button class="icon-btn" title="Notifications">
                <i class="bi bi-bell"></i>
                <?php if (!empty($notif_count) && $notif_count > 0): ?><span class="dot"></span><?php endif; ?>
            </button>
            <button class="icon-btn" title="Paramètres" onclick="location.href='parametres.php'">
                <i class="bi bi-gear"></i>
            </button>
        </div>
    </div>

    <div class="content">

        <?php if (!empty($stats['rdv_attente']) && $stats['rdv_attente'] > 0): ?>
        <div class="alert-banner">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span><strong><?php echo $stats['rdv_attente']; ?> rendez-vous</strong> en attente de confirmation nécessitent votre attention.</span>
            <a href="rendez-vous.php?filtre=en_attente" style="margin-left:auto;color:#d97706;font-weight:700;text-decoration:none;font-size:12px">Voir →</a>
        </div>
        <?php endif; ?>

        <!-- STATS -->
        <div class="stats-grid">
            <a href="medecins.php" class="stat-card">
                <div class="stat-icon si-blue"><i class="bi bi-person-badge"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['medecins']; ?></h3>
                    <p>Médecins actifs</p>
                </div>
            </a>
            <a href="patients.php" class="stat-card">
                <div class="stat-icon si-green"><i class="bi bi-people"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['patients']; ?></h3>
                    <p>Patients inscrits</p>
                </div>
            </a>
            <a href="rendez-vous.php" class="stat-card">
                <div class="stat-icon si-cyan"><i class="bi bi-calendar-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['rdv_aujourdhui']; ?></h3>
                    <p>RDV aujourd'hui</p>
                </div>
            </a>
            <a href="rendez-vous.php?filtre=en_attente" class="stat-card">
                <div class="stat-icon si-orange"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['rdv_attente']; ?></h3>
                    <p>RDV en attente</p>
                </div>
            </a>
            <a href="consultations.php" class="stat-card">
                <div class="stat-icon si-teal"><i class="bi bi-clipboard2-pulse"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['consultations']; ?></h3>
                    <p>Consultations totales</p>
                </div>
            </a>
            <a href="paiements.php" class="stat-card">
                <div class="stat-icon si-pink"><i class="bi bi-cash-stack"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($stats['revenus'],0,',',' '); ?></h3>
                    <p>Revenus (FCFA)</p>
                </div>
            </a>
            <a href="utilisateurs.php?filtre=suspendu" class="stat-card">
                <div class="stat-icon si-red"><i class="bi bi-person-x"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['suspendus']; ?></h3>
                    <p>Comptes suspendus</p>
                </div>
            </a>
            <a href="messages.php" class="stat-card">
                <div class="stat-icon si-indigo"><i class="bi bi-chat-dots"></i></div>
                <div class="stat-info">
                    <h3><?php echo $stats['messages_non_lus']; ?></h3>
                    <p>Messages non lus</p>
                </div>
            </a>
        </div>

        <!-- ACTIONS RAPIDES -->
        <div class="quick-grid">
            <a href="medecins.php?action=ajouter" class="quick-btn">
                <i class="bi bi-person-plus"></i> Ajouter un médecin
            </a>
            <a href="patients.php" class="quick-btn">
                <i class="bi bi-people-fill"></i> Gérer les patients
            </a>
            <a href="rendez-vous.php" class="quick-btn">
                <i class="bi bi-calendar-week"></i> Tous les RDV
            </a>
            <a href="parametres.php" class="quick-btn">
                <i class="bi bi-sliders"></i> Paramètres système
            </a>
        </div>

        <!-- LIGNE 1 : RDV récents + Répartition -->
        <div class="grid-3-2">
            <!-- Derniers RDV -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-calendar-event"></i> Derniers rendez-vous</h5>
                    <a href="rendez-vous.php">Voir tout →</a>
                </div>
                <div class="card-body">
                    <?php if (count($derniers_rdv) > 0):
                        $months = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
                        foreach ($derniers_rdv as $rdv):
                            $dt = new DateTime($rdv['date_rendez_vous']);
                            $badges = [
                                'en_attente'=>['warning','En attente'],
                                'confirme'  =>['success','Confirmé'],
                                'annule'    =>['danger','Annulé'],
                                'termine'   =>['info','Terminé'],
                                'patient_absent'=>['gray','Absent'],
                            ];
                            $b = $badges[$rdv['statut']] ?? ['gray','?'];
                    ?>
                    <div class="rdv-item">
                        <div class="rdv-date">
                            <div class="day"><?php echo $dt->format('d'); ?></div>
                            <div class="mon"><?php echo $months[(int)$dt->format('n')]; ?></div>
                        </div>
                        <div class="rdv-info">
                            <h6><?php echo htmlspecialchars($rdv['patient_nom']); ?></h6>
                            <p>Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?> &mdash; <?php echo htmlspecialchars($rdv['specialite']); ?></p>
                        </div>
                        <span class="badge badge-<?php echo $b[0]; ?>"><?php echo $b[1]; ?></span>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="empty"><i class="bi bi-calendar-x"></i><p>Aucun rendez-vous</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Répartition RDV -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-pie-chart"></i> Répartition des RDV</h5>
                </div>
                <div class="card-body">
                    <?php
                    $total_rdv = array_sum($rdv_chart);
                    $colors = ['en_attente'=>'#f59e0b','confirme'=>'#22c55e','termine'=>'#667eea','annule'=>'#ef4444','patient_absent'=>'#6b7280'];
                    $labels = ['en_attente'=>'En attente','confirme'=>'Confirmés','termine'=>'Terminés','annule'=>'Annulés','patient_absent'=>'Absents'];
                    ?>
                    <div class="chart-wrap">
                        <svg class="donut-svg" width="130" height="130" viewBox="0 0 130 130">
                            <?php
                            $cx = 65; $cy = 65; $r = 50; $stroke = 22;
                            $circumference = 2 * M_PI * $r;
                            $offset = 0;
                            foreach ($rdv_chart as $key => $val):
                                if ($val == 0) continue;
                                $pct = $total_rdv > 0 ? $val/$total_rdv : 0;
                                $dash = $pct * $circumference;
                                $gap  = $circumference - $dash;
                                $rotate = ($offset / $circumference) * 360 - 90;
                            ?>
                            <circle cx="<?php echo $cx;?>" cy="<?php echo $cy;?>" r="<?php echo $r;?>"
                                fill="none" stroke="<?php echo $colors[$key];?>"
                                stroke-width="<?php echo $stroke;?>"
                                stroke-dasharray="<?php echo round($dash,2).' '.round($gap,2);?>"
                                stroke-dashoffset="0"
                                transform="rotate(<?php echo round($rotate,2);?> <?php echo $cx.' '.$cy;?>)"
                            />
                            <?php $offset += $dash; endforeach; ?>
                            <text x="65" y="60" text-anchor="middle" font-size="18" font-weight="800" fill="#1a1a2e"><?php echo $total_rdv; ?></text>
                            <text x="65" y="76" text-anchor="middle" font-size="10" fill="#888">Total</text>
                        </svg>
                        <div class="legend">
                            <?php foreach ($rdv_chart as $key => $val): if ($val == 0) continue; ?>
                            <div class="legend-item">
                                <div class="legend-dot" style="background:<?php echo $colors[$key];?>"></div>
                                <?php echo $labels[$key]; ?>
                                <span><?php echo $val; ?></span>
                            </div>
                            <?php endforeach; ?>
                            <?php if ($total_rdv == 0): ?>
                            <p style="color:#bbb;font-size:13px">Aucune donnée</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- LIGNE 2 : Utilisateurs + Top médecins -->
        <div class="grid-2">
            <!-- Derniers utilisateurs -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-person-plus"></i> Dernières inscriptions</h5>
                    <a href="utilisateurs.php">Voir tout →</a>
                </div>
                <div class="card-body" style="padding:0 22px">
                    <table>
                        <thead>
                            <tr>
                                <th>Utilisateur</th>
                                <th>Rôle</th>
                                <th>Date</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($derniers_users as $u):
                                $role_b = ['patient'=>['info','Patient'],'medecin'=>['purple','Médecin'],'administrateur'=>['danger','Admin']];
                                $rb = $role_b[$u['role']] ?? ['gray','?'];
                                $stat_b = ['actif'=>'success','inactif'=>'warning','suspendu'=>'danger'];
                                $sb = $stat_b[$u['statut']] ?? 'gray';
                                $initials = strtoupper(substr($u['prenom'],0,1).substr($u['nom'],0,1));
                            ?>
                            <tr>
                                <td>
                                    <div class="user-cell">
                                        <div class="user-av"><?php echo $initials; ?></div>
                                        <div class="uinfo">
                                            <h6><?php echo htmlspecialchars($u['prenom'].' '.$u['nom']); ?></h6>
                                            <p><?php echo htmlspecialchars($u['email']); ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-<?php echo $rb[0]; ?>"><?php echo $rb[1]; ?></span></td>
                                <td style="color:#888;font-size:12px"><?php echo date('d/m/Y', strtotime($u['date_inscription'])); ?></td>
                                <td><span class="badge badge-<?php echo $sb; ?>"><?php echo ucfirst($u['statut']); ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top médecins -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-trophy"></i> Top médecins</h5>
                    <a href="medecins.php">Voir tout →</a>
                </div>
                <div class="card-body">
                    <?php if (count($top_medecins) > 0):
                        foreach ($top_medecins as $i => $med): ?>
                    <div class="med-item">
                        <div class="med-rank"><?php echo $i+1; ?></div>
                        <div class="med-info">
                            <h6>Dr. <?php echo htmlspecialchars($med['nom']); ?></h6>
                            <p><?php echo htmlspecialchars($med['specialite']); ?></p>
                            <div class="stars">
                                <?php for ($s=1;$s<=5;$s++) echo $s<=$med['note_moyenne']?'★':'☆'; ?>
                                <span style="color:#888;font-size:10px">(<?php echo number_format($med['note_moyenne'],1); ?>)</span>
                            </div>
                        </div>
                        <div class="med-stats">
                            <div class="n"><?php echo $med['nombre_consultations']; ?></div>
                            <p>consultations</p>
                        </div>
                    </div>
                    <?php endforeach; else: ?>
                    <div class="empty"><i class="bi bi-person-x"></i><p>Aucun médecin enregistré</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

</body>
</html>