<?php
/**
 * Tableau de bord Médecin
 * Application de Consultation Médicale
 */

require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') {
    header("Location: ../../login.php");
    exit();
}

$medecin_id = $_SESSION['medecin_id'];
$user_id    = $_SESSION['user_id'];

try {
    $db = Database::getInstance()->getConnection();

    // Infos médecin
    $stmt = $db->prepare("SELECT m.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.photo_profil
                          FROM medecins m
                          INNER JOIN utilisateurs u ON m.utilisateur_id = u.id
                          WHERE m.id = ?");
    $stmt->execute([$medecin_id]);
    $medecin = $stmt->fetch();

    // RDV aujourd'hui
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM rendez_vous
                          WHERE medecin_id = ? AND date_rendez_vous = CURDATE()
                          AND statut IN ('en_attente','confirme')");
    $stmt->execute([$medecin_id]);
    $rdv_aujourdhui = $stmt->fetch();

    // RDV en attente
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM rendez_vous
                          WHERE medecin_id = ? AND statut = 'en_attente'");
    $stmt->execute([$medecin_id]);
    $rdv_attente = $stmt->fetch();

    // Total consultations
    $stmt = $db->prepare("SELECT nombre_consultations as total FROM medecins WHERE id = ?");
    $stmt->execute([$medecin_id]);
    $total_consultations = $stmt->fetch();

    // Note moyenne
    $stmt = $db->prepare("SELECT note_moyenne FROM medecins WHERE id = ?");
    $stmt->execute([$medecin_id]);
    $note = $stmt->fetch();

    // Prochains RDV (5 prochains)
    $stmt = $db->prepare("
        SELECT rv.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, u.telephone as patient_tel
        FROM rendez_vous rv
        INNER JOIN patients p ON rv.patient_id = p.id
        INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
        WHERE rv.medecin_id = ?
          AND rv.date_rendez_vous >= CURDATE()
          AND rv.statut IN ('en_attente','confirme')
        ORDER BY rv.date_rendez_vous ASC, rv.heure_debut ASC
        LIMIT 5
    ");
    $stmt->execute([$medecin_id]);
    $prochains_rdv = $stmt->fetchAll();

    // Dernières consultations (5)
    $stmt = $db->prepare("
        SELECT c.*, rv.date_rendez_vous, CONCAT(u.nom,' ',u.prenom) as patient_nom
        FROM consultations c
        INNER JOIN rendez_vous rv ON c.rendez_vous_id = rv.id
        INNER JOIN patients p ON c.patient_id = p.id
        INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
        WHERE c.medecin_id = ?
        ORDER BY c.date_consultation DESC
        LIMIT 5
    ");
    $stmt->execute([$medecin_id]);
    $dernieres_consultations = $stmt->fetchAll();

    // Notifications non lues
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE utilisateur_id = ? AND lu = 0");
    $stmt->execute([$user_id]);
    $notif_non_lues = $stmt->fetch();

} catch (PDOException $e) {
    error_log("Erreur dashboard médecin : " . $e->getMessage());
    $error = "Une erreur est survenue.";
}

$months = ['','Jan','Fév','Mar','Avr','Mai','Jun','Jul','Aoû','Sep','Oct','Nov','Déc'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord Médecin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
            --sidebar-w: 260px;
            --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Outfit', sans-serif; background: #f5f7fa; color: #333; }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0;
            height: 100vh; width: var(--sidebar-w);
            background: var(--gradient);
            color: #fff; z-index: 1000;
            display: flex; flex-direction: column;
            box-shadow: 2px 0 15px rgba(0,0,0,.15);
        }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,.15); }
        .sidebar-header .avatar {
            width: 70px; height: 70px; border-radius: 50%;
            background: rgba(255,255,255,.25); border: 3px solid rgba(255,255,255,.5);
            display: flex; align-items: center; justify-content: center;
            font-size: 28px; margin: 0 auto 12px;
        }
        .sidebar-header h4 { font-size: 16px; font-weight: 600; margin-bottom: 4px; }
        .sidebar-header p { font-size: 12px; opacity: .75; }
        .badge-disponible { display: inline-block; background: #4ade80; color: #fff; border-radius: 20px; padding: 2px 10px; font-size: 11px; margin-top: 6px; }

        .sidebar-menu { flex: 1; padding: 20px 0; overflow-y: auto; }
        .sidebar-menu a {
            display: flex; align-items: center; gap: 12px;
            padding: 13px 25px; color: rgba(255,255,255,.85);
            text-decoration: none; font-size: 14px; font-weight: 500;
            border-left: 3px solid transparent; transition: all .25s;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,.12); color: #fff;
            border-left-color: #fff;
        }
        .sidebar-menu a i { font-size: 18px; width: 22px; }
        .sidebar-menu .menu-section { padding: 10px 25px 5px; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: .5; }

        .sidebar-footer { padding: 20px; border-top: 1px solid rgba(255,255,255,.15); }
        .sidebar-footer a { display: flex; align-items: center; gap: 10px; color: rgba(255,255,255,.8); text-decoration: none; font-size: 14px; }
        .sidebar-footer a:hover { color: #fff; }

        /* MAIN */
        .main-content { margin-left: var(--sidebar-w); }
        .topbar {
            background: #fff; padding: 18px 30px;
            display: flex; justify-content: space-between; align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.06); position: sticky; top: 0; z-index: 100;
        }
        .topbar-left h2 { font-size: 22px; font-weight: 700; color: #1a1a2e; }
        .topbar-left p { font-size: 13px; color: #888; margin-top: 2px; }
        .topbar-right { display: flex; align-items: center; gap: 18px; }
        .notif-btn { position: relative; font-size: 20px; color: #666; cursor: pointer; background: none; border: none; }
        .notif-btn .dot { position: absolute; top: -4px; right: -4px; width: 18px; height: 18px; background: #ef4444; border-radius: 50%; font-size: 10px; display: flex; align-items: center; justify-content: center; color: #fff; }
        .user-chip { display: flex; align-items: center; gap: 10px; background: #f5f7fa; border-radius: 30px; padding: 6px 14px 6px 6px; }
        .user-chip .av { width: 34px; height: 34px; border-radius: 50%; background: var(--gradient); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 14px; }
        .user-chip span { font-size: 13px; font-weight: 600; }

        /* CONTENT */
        .content { padding: 28px 30px; }

        /* STATUS BAR */
        .status-bar {
            display: flex; align-items: center; gap: 15px;
            background: #fff; border-radius: 14px; padding: 16px 22px;
            margin-bottom: 24px; box-shadow: 0 2px 8px rgba(0,0,0,.05);
        }
        .status-bar i { font-size: 22px; color: var(--primary); }
        .status-bar .info { flex: 1; }
        .status-bar .info h5 { font-size: 15px; font-weight: 600; margin-bottom: 2px; }
        .status-bar .info p { font-size: 13px; color: #666; }
        .toggle-disponible { padding: 8px 20px; border-radius: 30px; border: none; cursor: pointer; font-weight: 600; font-size: 13px; transition: all .25s; }
        .toggle-disponible.on { background: #dcfce7; color: #16a34a; }
        .toggle-disponible.off { background: #fee2e2; color: #dc2626; }

        /* STATS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px,1fr)); gap: 18px; margin-bottom: 24px; }
        .stat-card {
            background: #fff; border-radius: 16px; padding: 22px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05);
            display: flex; align-items: center; gap: 18px;
            transition: transform .3s, box-shadow .3s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 8px 20px rgba(0,0,0,.1); }
        .stat-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 24px; flex-shrink: 0; }
        .stat-icon.blue   { background: linear-gradient(135deg,#667eea,#764ba2); color: #fff; }
        .stat-icon.green  { background: linear-gradient(135deg,#11998e,#38ef7d); color: #fff; }
        .stat-icon.orange { background: linear-gradient(135deg,#f093fb,#f5576c); color: #fff; }
        .stat-icon.cyan   { background: linear-gradient(135deg,#4facfe,#00f2fe); color: #fff; }
        .stat-info h3 { font-size: 28px; font-weight: 700; line-height: 1; }
        .stat-info p { font-size: 13px; color: #888; margin-top: 4px; }

        /* CARDS */
        .row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        @media(max-width:900px){ .row { grid-template-columns: 1fr; } }
        .card { background: #fff; border-radius: 16px; box-shadow: 0 2px 8px rgba(0,0,0,.05); overflow: hidden; }
        .card-header { padding: 18px 22px; border-bottom: 1px solid #f0f2f5; display: flex; justify-content: space-between; align-items: center; }
        .card-header h5 { font-size: 15px; font-weight: 700; display: flex; align-items: center; gap: 8px; }
        .card-header a { font-size: 13px; color: var(--primary); text-decoration: none; }
        .card-body { padding: 18px 22px; }

        /* RDV ITEM */
        .rdv-item {
            display: flex; align-items: center; gap: 15px;
            padding: 14px; background: #f8f9ff; border-radius: 12px; margin-bottom: 10px;
            transition: all .25s; border-left: 3px solid transparent;
        }
        .rdv-item:hover { background: #eef0ff; border-left-color: var(--primary); }
        .rdv-date { text-align: center; min-width: 52px; background: #fff; border-radius: 10px; padding: 8px; }
        .rdv-date .day { font-size: 22px; font-weight: 700; color: var(--primary); line-height: 1; }
        .rdv-date .mon { font-size: 11px; text-transform: uppercase; color: #888; }
        .rdv-info { flex: 1; }
        .rdv-info h6 { font-size: 14px; font-weight: 600; margin-bottom: 3px; }
        .rdv-info p { font-size: 12px; color: #888; display: flex; align-items: center; gap: 5px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #16a34a; }
        .badge-warning { background: #fef9c3; color: #ca8a04; }
        .badge-danger  { background: #fee2e2; color: #dc2626; }
        .rdv-actions { display: flex; gap: 6px; }
        .btn-sm { padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; border: none; cursor: pointer; transition: all .2s; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
        .btn-primary { background: var(--gradient); color: #fff; }
        .btn-success { background: #dcfce7; color: #16a34a; }
        .btn-danger  { background: #fee2e2; color: #dc2626; }
        .btn-sm:hover { opacity: .85; transform: translateY(-1px); }

        /* CONSULT ITEM */
        .consult-item { padding: 13px; background: #f8f9ff; border-radius: 10px; margin-bottom: 8px; }
        .consult-item h6 { font-size: 13px; font-weight: 600; margin-bottom: 4px; }
        .consult-item p { font-size: 12px; color: #888; }

        /* EMPTY */
        .empty { text-align: center; padding: 35px; color: #bbb; }
        .empty i { font-size: 42px; display: block; margin-bottom: 10px; }
        .empty p { font-size: 14px; }

        /* ACTION RAPIDES */
        .quick-actions { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px,1fr)); gap: 14px; margin-bottom: 24px; }
        .qa-btn {
            display: flex; align-items: center; gap: 12px;
            background: #fff; border-radius: 12px; padding: 16px 18px;
            text-decoration: none; color: #333; font-weight: 600; font-size: 13px;
            box-shadow: 0 2px 8px rgba(0,0,0,.05); transition: all .25s;
        }
        .qa-btn i { font-size: 24px; color: var(--primary); }
        .qa-btn:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(102,126,234,.2); color: var(--primary); }
    </style>
</head>
<body>
<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="avatar"><i class="bi bi-person-badge"></i></div>
        <h4>Dr. <?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h4>
        <p><?php echo htmlspecialchars($medecin['specialite'] ?? ''); ?></p>
        <span class="badge-disponible"><i class="bi bi-circle-fill" style="font-size:8px"></i> <?php echo $medecin['disponible'] ? 'Disponible' : 'Indisponible'; ?></span>
    </div>
    <nav class="sidebar-menu">
        <span class="menu-section">Principal</span>
        <a href="index.php" class="active"><i class="bi bi-house"></i> Tableau de bord</a>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
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
    <div class="sidebar-footer">
        <a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a>
    </div>
</aside>

<!-- MAIN -->
<div class="main-content">
    <div class="topbar">
        <div class="topbar-left">
            <h2>Tableau de bord</h2>
            <p><?php echo date('l d F Y'); ?></p>
        </div>
        <div class="topbar-right">
            <button class="notif-btn">
                <i class="bi bi-bell"></i>
                <?php if ($notif_non_lues['total'] > 0): ?>
                    <span class="dot"><?php echo $notif_non_lues['total']; ?></span>
                <?php endif; ?>
            </button>
            <div class="user-chip">
                <div class="av"><?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?></div>
                <span>Dr. <?php echo htmlspecialchars($_SESSION['user_prenom']); ?></span>
            </div>
        </div>
    </div>

    <div class="content">
        <!-- STATUS -->
        <div class="status-bar">
            <i class="bi bi-activity"></i>
            <div class="info">
                <h5>Statut de disponibilité</h5>
                <p>Gérez votre disponibilité en temps réel pour les patients.</p>
            </div>
            <button class="toggle-disponible <?php echo $medecin['disponible'] ? 'on' : 'off'; ?>">
                <i class="bi bi-<?php echo $medecin['disponible'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                <?php echo $medecin['disponible'] ? 'Disponible' : 'Indisponible'; ?>
            </button>
        </div>

        <!-- ACTIONS RAPIDES -->
        <div class="quick-actions">
            <a href="rendez-vous.php?action=confirmer" class="qa-btn"><i class="bi bi-calendar-check"></i> Confirmer RDV</a>
            <a href="consultations.php?action=nouveau" class="qa-btn"><i class="bi bi-clipboard2-plus"></i> Nouvelle consultation</a>
            <a href="ordonnances.php?action=creer" class="qa-btn"><i class="bi bi-file-earmark-plus"></i> Rédiger ordonnance</a>
            <a href="messages.php" class="qa-btn"><i class="bi bi-chat-text"></i> Messagerie</a>
        </div>

        <!-- STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue"><i class="bi bi-calendar-day"></i></div>
                <div class="stat-info">
                    <h3><?php echo $rdv_aujourdhui['total']; ?></h3>
                    <p>RDV aujourd'hui</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon orange"><i class="bi bi-hourglass-split"></i></div>
                <div class="stat-info">
                    <h3><?php echo $rdv_attente['total']; ?></h3>
                    <p>En attente de confirmation</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green"><i class="bi bi-clipboard2-check"></i></div>
                <div class="stat-info">
                    <h3><?php echo $total_consultations['total'] ?? 0; ?></h3>
                    <p>Total consultations</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon cyan"><i class="bi bi-star-fill"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($note['note_moyenne'] ?? 0, 1); ?>/5</h3>
                    <p>Note moyenne</p>
                </div>
            </div>
        </div>

        <!-- LISTES -->
        <div class="row">
            <!-- Prochains RDV -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-calendar-event" style="color:var(--primary)"></i> Prochains rendez-vous</h5>
                    <a href="rendez-vous.php">Voir tout <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (count($prochains_rdv) > 0): ?>
                        <?php foreach ($prochains_rdv as $rdv):
                            $dt = new DateTime($rdv['date_rendez_vous']); ?>
                        <div class="rdv-item">
                            <div class="rdv-date">
                                <div class="day"><?php echo $dt->format('d'); ?></div>
                                <div class="mon"><?php echo $months[(int)$dt->format('n')]; ?></div>
                            </div>
                            <div class="rdv-info">
                                <h6><?php echo htmlspecialchars($rdv['patient_nom']); ?></h6>
                                <p><i class="bi bi-clock"></i><?php echo substr($rdv['heure_debut'],0,5); ?>
                                   &nbsp;|&nbsp;<i class="bi bi-camera-video"></i><?php echo $rdv['type_consultation'] === 'teleconsultation' ? 'Téléconsultation' : 'Présentiel'; ?></p>
                            </div>
                            <div>
                                <?php if ($rdv['statut'] === 'confirme'): ?>
                                    <span class="badge badge-success">Confirmé</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">En attente</span>
                                <?php endif; ?>
                            </div>
                            <div class="rdv-actions">
                                <a href="rendez-vous.php?action=confirmer&id=<?php echo $rdv['id']; ?>" class="btn-sm btn-success"><i class="bi bi-check-lg"></i></a>
                                <a href="rendez-vous.php?action=annuler&id=<?php echo $rdv['id']; ?>" class="btn-sm btn-danger"><i class="bi bi-x-lg"></i></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty"><i class="bi bi-calendar-x"></i><p>Aucun rendez-vous à venir</p></div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Dernières consultations -->
            <div class="card">
                <div class="card-header">
                    <h5><i class="bi bi-clipboard2-pulse" style="color:var(--primary)"></i> Dernières consultations</h5>
                    <a href="consultations.php">Voir tout <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="card-body">
                    <?php if (count($dernieres_consultations) > 0): ?>
                        <?php foreach ($dernieres_consultations as $c):
                            $dt = new DateTime($c['date_consultation']); ?>
                        <div class="consult-item">
                            <h6><?php echo htmlspecialchars($c['patient_nom']); ?></h6>
                            <p><i class="bi bi-calendar3"></i> <?php echo $dt->format('d/m/Y H:i'); ?>
                               &nbsp;|&nbsp; <span class="badge badge-success">Terminée</span></p>
                        </div>
                        <?php endforeach; ?>
                        <div style="text-align:center;margin-top:12px">
                            <a href="consultations.php" class="btn-sm btn-primary">Voir l'historique complet</a>
                        </div>
                    <?php else: ?>
                        <div class="empty"><i class="bi bi-folder2-open"></i><p>Aucune consultation enregistrée</p></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>