<?php
/**
 * Mon Agenda - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

$mois_param = $_GET['mois'] ?? date('Y-m');
[$annee, $mois] = explode('-', $mois_param);
$annee = intval($annee); $mois = intval($mois);

$prev = date('Y-m', mktime(0,0,0,$mois-1,1,$annee));
$next = date('Y-m', mktime(0,0,0,$mois+1,1,$annee));
$debut_mois = "$annee-".str_pad($mois,2,'0',STR_PAD_LEFT)."-01";
$fin_mois   = date('Y-m-t', strtotime($debut_mois));

// RDV du mois
$stmt = $db->prepare("SELECT rv.*, CONCAT(u.nom,' ',u.prenom) as patient_nom FROM rendez_vous rv INNER JOIN patients p ON rv.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE rv.medecin_id=? AND rv.date_rendez_vous BETWEEN ? AND ? ORDER BY rv.date_rendez_vous ASC, rv.heure_debut ASC");
$stmt->execute([$medecin_id,$debut_mois,$fin_mois]);
$rdv_mois = $stmt->fetchAll();

// Indexer par date
$rdv_by_day = [];
foreach ($rdv_mois as $r) {
    $rdv_by_day[$r['date_rendez_vous']][] = $r;
}

$mois_noms = ['','Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
$jours_semaine = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];

// Construire le calendrier
$premier_jour = date('N', strtotime($debut_mois)); // 1=Lun, 7=Dim
$nb_jours = date('t', strtotime($debut_mois));

// RDV d'aujourd'hui
$aujourd_hui = date('Y-m-d');
$rdv_auj = [];
foreach ($rdv_mois as $r) {
    if ($r['date_rendez_vous'] === $aujourd_hui) $rdv_auj[] = $r;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mon agenda - <?php echo SITE_NAME; ?></title>
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
        .layout{display:grid;grid-template-columns:1fr 320px;gap:20px}
        @media(max-width:1000px){.layout{grid-template-columns:1fr}}
        /* Calendrier */
        .cal-wrap{background:#fff;border-radius:16px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .cal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
        .cal-header h3{font-size:18px;font-weight:700}
        .cal-nav{display:flex;gap:8px}
        .cal-nav a{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;text-decoration:none;background:#f0f2f5;color:#555;font-size:16px;transition:all .2s}
        .cal-nav a:hover{background:var(--primary);color:#fff}
        .cal-today-btn{padding:8px 16px;background:var(--gradient);color:#fff;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none}
        .cal-grid-header{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-bottom:6px}
        .cal-grid-header div{text-align:center;font-size:12px;font-weight:700;color:#888;padding:8px 0;text-transform:uppercase}
        .cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px}
        .cal-day{min-height:90px;border-radius:10px;padding:8px;background:#f8f9ff;cursor:pointer;transition:all .2s;border:2px solid transparent}
        .cal-day:hover{background:#eef0ff;border-color:#c7d2fe}
        .cal-day.empty{background:transparent;cursor:default}
        .cal-day.today{background:#eef0ff;border-color:var(--primary)}
        .cal-day.has-rdv{background:#fff0f6}
        .cal-day .day-num{font-size:14px;font-weight:700;color:#333;margin-bottom:4px}
        .cal-day.today .day-num{color:var(--primary)}
        .cal-day .event{background:var(--gradient);color:#fff;border-radius:6px;padding:2px 6px;font-size:10px;font-weight:600;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .cal-day .more{font-size:10px;color:var(--primary);font-weight:600}
        /* Panel latéral */
        .side-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:16px}
        .side-card h5{font-size:14px;font-weight:700;margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .rdv-mini{display:flex;gap:12px;align-items:center;padding:11px;background:#f8f9ff;border-radius:10px;margin-bottom:8px;border-left:3px solid var(--primary)}
        .rdv-mini .time{font-size:13px;font-weight:700;color:var(--primary);min-width:42px}
        .rdv-mini .info h6{font-size:12px;font-weight:700;margin-bottom:2px}
        .rdv-mini .info p{font-size:11px;color:#888}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:10px;font-weight:600}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .btn{padding:9px 18px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none;width:100%;justify-content:center}
        .btn-primary{background:var(--gradient);color:#fff}
        .btn:hover{opacity:.88}
        .month-stats{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px}
        .mstat{background:#f8f9ff;border-radius:10px;padding:12px;text-align:center}
        .mstat .n{font-size:20px;font-weight:700;color:var(--primary)}.mstat p{font-size:11px;color:#888;margin-top:3px}
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
        <a href="agenda.php" class="active"><i class="bi bi-calendar3"></i> Mon agenda</a>
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
        <h2><i class="bi bi-calendar3" style="color:var(--primary)"></i> Mon agenda</h2>
    </div>
    <div class="content">
        <div class="layout">
            <!-- Calendrier -->
            <div class="cal-wrap">
                <div class="cal-header">
                    <h3><?php echo $mois_noms[$mois].' '.$annee; ?></h3>
                    <div class="cal-nav">
                        <a href="?mois=<?php echo $prev; ?>"><i class="bi bi-chevron-left"></i></a>
                        <a href="?mois=<?php echo date('Y-m'); ?>" class="cal-today-btn">Aujourd'hui</a>
                        <a href="?mois=<?php echo $next; ?>"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
                <div class="cal-grid-header">
                    <?php foreach ($jours_semaine as $j): ?><div><?php echo $j; ?></div><?php endforeach; ?>
                </div>
                <div class="cal-grid">
                    <?php for ($i=1; $i < $premier_jour; $i++): ?><div class="cal-day empty"></div><?php endfor; ?>
                    <?php for ($jour=1; $jour<=$nb_jours; $jour++):
                        $date_str = "$annee-".str_pad($mois,2,'0',STR_PAD_LEFT)."-".str_pad($jour,2,'0',STR_PAD_LEFT);
                        $rdvs = $rdv_by_day[$date_str] ?? [];
                        $is_today = $date_str === $aujourd_hui;
                    ?>
                    <div class="cal-day <?php echo $is_today?'today':''; ?> <?php echo count($rdvs)>0?'has-rdv':''; ?>">
                        <div class="day-num"><?php echo $jour; ?></div>
                        <?php foreach (array_slice($rdvs,0,2) as $r): ?>
                        <div class="event"><?php echo substr($r['heure_debut'],0,5).' '.htmlspecialchars(substr($r['patient_nom'],0,10)); ?></div>
                        <?php endforeach; ?>
                        <?php if (count($rdvs)>2): ?><div class="more">+<?php echo count($rdvs)-2; ?> autres</div><?php endif; ?>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Panneau latéral -->
            <div>
                <!-- Stats du mois -->
                <div class="side-card">
                    <h5><i class="bi bi-bar-chart" style="color:var(--primary)"></i> Ce mois</h5>
                    <div class="month-stats">
                        <div class="mstat"><div class="n"><?php echo count($rdv_mois); ?></div><p>Total RDV</p></div>
                        <?php $confirms = array_filter($rdv_mois,fn($r)=>$r['statut']==='confirme'); ?>
                        <div class="mstat"><div class="n"><?php echo count($confirms); ?></div><p>Confirmés</p></div>
                        <?php $termines = array_filter($rdv_mois,fn($r)=>$r['statut']==='termine'); ?>
                        <div class="mstat"><div class="n"><?php echo count($termines); ?></div><p>Terminés</p></div>
                        <?php $attente = array_filter($rdv_mois,fn($r)=>$r['statut']==='en_attente'); ?>
                        <div class="mstat"><div class="n"><?php echo count($attente); ?></div><p>En attente</p></div>
                    </div>
                </div>

                <!-- RDV aujourd'hui -->
                <div class="side-card">
                    <h5><i class="bi bi-calendar-day" style="color:var(--primary)"></i> Aujourd'hui (<?php echo date('d/m'); ?>)</h5>
                    <?php if (count($rdv_auj) > 0): foreach ($rdv_auj as $r): ?>
                    <div class="rdv-mini">
                        <div class="time"><?php echo substr($r['heure_debut'],0,5); ?></div>
                        <div class="info">
                            <h6><?php echo htmlspecialchars($r['patient_nom']); ?></h6>
                            <p><?php echo $r['type_consultation']==='teleconsultation'?'Téléconsultation':'Présentiel'; ?></p>
                        </div>
                        <?php $b=['en_attente'=>['warning','Attente'],'confirme'=>['success','Confirmé'],'termine'=>['info','Terminé']]; $bv=$b[$r['statut']]??['info','?']; ?>
                        <span class="badge badge-<?php echo $bv[0]; ?>"><?php echo $bv[1]; ?></span>
                    </div>
                    <?php endforeach; else: ?>
                    <p style="color:#bbb;text-align:center;padding:16px;font-size:13px">Aucun RDV aujourd'hui</p>
                    <?php endif; ?>
                </div>
                <a href="rendez-vous.php" class="btn btn-primary"><i class="bi bi-calendar-check"></i> Gérer les rendez-vous</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>