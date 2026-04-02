<?php
/**
 * Évaluations - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

// Stats évaluations
$stmt = $db->prepare("SELECT AVG(note) as moy, COUNT(*) as total, SUM(note=5) as cinq, SUM(note=4) as quatre, SUM(note=3) as trois, SUM(note=2) as deux, SUM(note=1) as un FROM avis_evaluations WHERE medecin_id=?");
$stmt->execute([$medecin_id]);
$stats = $stmt->fetch();

// Liste avis
$stmt = $db->prepare("SELECT av.*, CONCAT(u.nom,' ',u.prenom) as patient_nom FROM avis_evaluations av INNER JOIN patients p ON av.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE av.medecin_id=? ORDER BY av.date_avis DESC");
$stmt->execute([$medecin_id]);
$avis = $stmt->fetchAll();

function stars($n) {
    $s = '';
    for ($i=1;$i<=5;$i++) $s .= '<i class="bi bi-star'.($i<=$n?'-fill':'').'" style="color:'.($i<=$n?'#f59e0b':'#d1d5db').'"></i>';
    return $s;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Évaluations - <?php echo SITE_NAME; ?></title>
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
        .overview{display:grid;grid-template-columns:auto 1fr;gap:30px;background:#fff;border-radius:16px;padding:28px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:24px;align-items:center}
        @media(max-width:700px){.overview{grid-template-columns:1fr;text-align:center}}
        .score-big{text-align:center;padding-right:30px;border-right:1px solid #f0f2f5}
        .score-big .num{font-size:64px;font-weight:800;color:var(--primary);line-height:1}
        .score-big .stars{font-size:22px;margin:6px 0}
        .score-big p{font-size:13px;color:#888}
        .bars{flex:1;padding-left:20px}
        .bar-row{display:flex;align-items:center;gap:12px;margin-bottom:10px}
        .bar-row .label{font-size:13px;font-weight:600;width:50px;text-align:right;color:#555}
        .bar-track{flex:1;height:10px;background:#f0f2f5;border-radius:10px;overflow:hidden}
        .bar-fill{height:100%;background:var(--gradient);border-radius:10px;transition:width .6s}
        .bar-row .count{font-size:12px;color:#888;width:30px}
        .avis-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px}
        .avis-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:transform .3s}
        .avis-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(0,0,0,.1)}
        .avis-head{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px}
        .avis-author{display:flex;align-items:center;gap:10px}
        .avis-author .av{width:38px;height:38px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px}
        .avis-author h6{font-size:13px;font-weight:700;margin-bottom:2px}
        .avis-author p{font-size:11px;color:#888}
        .avis-text{font-size:13px;color:#555;line-height:1.6;font-style:italic;background:#f8f9ff;border-radius:10px;padding:12px;margin-top:10px}
        .empty-state{text-align:center;padding:60px;background:#fff;border-radius:16px;color:#bbb;box-shadow:0 2px 8px rgba(0,0,0,.05)}
        .empty-state i{font-size:50px;display:block;margin-bottom:14px;opacity:.4}
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
        <a href="profil.php"><i class="bi bi-person-gear"></i> Mon profil</a>
        <a href="evaluations.php" class="active"><i class="bi bi-star"></i> Évaluations</a>
    </nav>
    <div class="sidebar-footer"><a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <h2><i class="bi bi-star-fill" style="color:#f59e0b"></i> Mes évaluations</h2>
        <span style="font-size:13px;color:#888"><?php echo $stats['total'] ?? 0; ?> avis</span>
    </div>
    <div class="content">
        <!-- Overview -->
        <div class="overview">
            <div class="score-big">
                <div class="num"><?php echo $stats['total'] > 0 ? number_format($stats['moy'],1) : '—'; ?></div>
                <div class="stars"><?php echo stars(round($stats['moy']??0)); ?></div>
                <p><?php echo $stats['total']; ?> évaluation(s)</p>
            </div>
            <div class="bars">
                <?php $total = max($stats['total'],1); ?>
                <?php foreach ([5=>'cinq',4=>'quatre',3=>'trois',2=>'deux',1=>'un'] as $n => $key): $v = $stats[$key]??0; ?>
                <div class="bar-row">
                    <span class="label"><?php echo $n; ?> <i class="bi bi-star-fill" style="color:#f59e0b;font-size:10px"></i></span>
                    <div class="bar-track"><div class="bar-fill" style="width:<?php echo round($v/$total*100); ?>%"></div></div>
                    <span class="count"><?php echo $v; ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Avis -->
        <?php if (count($avis) > 0): ?>
        <div class="avis-grid">
            <?php foreach ($avis as $av): ?>
            <div class="avis-card">
                <div class="avis-head">
                    <div class="avis-author">
                        <div class="av"><?php echo strtoupper(substr($av['patient_nom'],0,1)); ?></div>
                        <div>
                            <h6><?php echo htmlspecialchars($av['patient_nom']); ?></h6>
                            <p><?php echo date('d/m/Y',strtotime($av['date_avis'])); ?></p>
                        </div>
                    </div>
                    <div><?php echo stars($av['note']); ?></div>
                </div>
                <?php if ($av['commentaire']): ?><div class="avis-text">"<?php echo htmlspecialchars($av['commentaire']); ?>"</div><?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="bi bi-star"></i>
            <p style="font-size:15px;font-weight:600">Aucune évaluation reçue</p>
            <p style="font-size:13px;margin-top:6px">Les patients pourront vous évaluer après une consultation</p>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>