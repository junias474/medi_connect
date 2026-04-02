<?php
/**
 * Mes Patients - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

$search = $_GET['search'] ?? '';
$mode   = $_GET['mode'] ?? 'liste';
$pat_id = intval($_GET['id'] ?? 0);

// Détail patient
$patient_detail = null;
$patient_rdv    = [];
$patient_consult = [];
if ($mode === 'detail' && $pat_id) {
    $stmt = $db->prepare("SELECT p.*, u.nom, u.prenom, u.email, u.telephone, u.date_naissance, u.ville, u.adresse, u.genre FROM patients p INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE p.id=?");
    $stmt->execute([$pat_id]);
    $patient_detail = $stmt->fetch();

    $stmt = $db->prepare("SELECT * FROM rendez_vous WHERE patient_id=? AND medecin_id=? ORDER BY date_rendez_vous DESC LIMIT 10");
    $stmt->execute([$pat_id, $medecin_id]);
    $patient_rdv = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT c.*, rv.date_rendez_vous FROM consultations c INNER JOIN rendez_vous rv ON c.rendez_vous_id=rv.id WHERE c.patient_id=? AND c.medecin_id=? ORDER BY c.date_consultation DESC LIMIT 10");
    $stmt->execute([$pat_id, $medecin_id]);
    $patient_consult = $stmt->fetchAll();
}

// Liste des patients ayant eu un RDV ou consultation avec ce médecin
$where = "WHERE (rv.medecin_id = ? OR c.medecin_id = ?)";
$params = [$medecin_id, $medecin_id];
if ($search) { $where .= " AND (CONCAT(u.nom,' ',u.prenom) LIKE ? OR u.email LIKE ? OR u.telephone LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; $params[] = "%$search%"; }

$stmt = $db->prepare("
    SELECT DISTINCT p.id, p.numero_patient, p.groupe_sanguin, u.nom, u.prenom, u.email, u.telephone, u.ville,
           COUNT(DISTINCT rv2.id) as nb_rdv, COUNT(DISTINCT c2.id) as nb_consult,
           MAX(rv2.date_rendez_vous) as dernier_rdv
    FROM patients p
    INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
    LEFT JOIN rendez_vous rv ON p.id = rv.patient_id AND rv.medecin_id = ?
    LEFT JOIN consultations c ON p.id = c.patient_id AND c.medecin_id = ?
    LEFT JOIN rendez_vous rv2 ON p.id = rv2.patient_id AND rv2.medecin_id = ?
    LEFT JOIN consultations c2 ON p.id = c2.patient_id AND c2.medecin_id = ?
    WHERE (rv.id IS NOT NULL OR c.id IS NOT NULL)
    GROUP BY p.id, p.numero_patient, p.groupe_sanguin, u.nom, u.prenom, u.email, u.telephone, u.ville
    " . ($search ? "HAVING (CONCAT(u.nom,' ',u.prenom) LIKE ? OR u.email LIKE ? OR u.telephone LIKE ?)" : "") . "
    ORDER BY dernier_rdv DESC
");
$sparams = [$medecin_id, $medecin_id, $medecin_id, $medecin_id];
if ($search) { $sparams[] = "%$search%"; $sparams[] = "%$search%"; $sparams[] = "%$search%"; }
$stmt->execute($sparams);
$patients = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mes patients - <?php echo SITE_NAME; ?></title>
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
        .search-bar{display:flex;gap:12px;margin-bottom:20px}
        .search-bar input{flex:1;padding:11px 16px;border:2px solid #e5e7eb;border-radius:12px;font-family:'Outfit',sans-serif;font-size:14px;outline:none;transition:border-color .2s}
        .search-bar input:focus{border-color:var(--primary)}
        .btn{padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-outline{background:#fff;border:2px solid #e5e7eb;color:#555}.btn-sm{padding:7px 14px;font-size:12px;border-radius:8px}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .patients-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;margin-bottom:24px}
        .patient-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.05);transition:all .3s;border-top:3px solid transparent}
        .patient-card:hover{transform:translateY(-4px);box-shadow:0 8px 20px rgba(102,126,234,.15);border-top-color:var(--primary)}
        .patient-card .head{display:flex;align-items:center;gap:14px;margin-bottom:14px}
        .patient-card .av{width:50px;height:50px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;flex-shrink:0}
        .patient-card .name h5{font-size:15px;font-weight:700;margin-bottom:2px}
        .patient-card .name p{font-size:12px;color:#888}
        .patient-card .meta{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:14px}
        .patient-card .meta-item{font-size:12px;color:#666;display:flex;align-items:center;gap:5px}
        .patient-card .meta-item i{color:var(--primary)}
        .patient-card .stats{display:flex;gap:10px;margin-bottom:14px}
        .patient-card .stat{flex:1;background:#f8f9ff;border-radius:10px;padding:8px;text-align:center}
        .patient-card .stat .n{font-size:18px;font-weight:700;color:var(--primary)}
        .patient-card .stat .l{font-size:11px;color:#888}
        .badge-blood{display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:700;background:#fee2e2;color:#dc2626}
        /* DETAIL */
        .detail-grid{display:grid;grid-template-columns:1fr 2fr;gap:20px}
        @media(max-width:800px){.detail-grid{grid-template-columns:1fr}}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden;margin-bottom:16px}
        .card-header{padding:16px 20px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:14px;font-weight:700;display:flex;align-items:center;gap:8px}
        .card-body{padding:20px}
        .info-row{display:flex;justify-content:space-between;padding:9px 0;border-bottom:1px solid #f5f5f5;font-size:13px}
        .info-row:last-child{border:none}
        .info-row .label{color:#888;font-weight:600}.info-row .val{color:#333;font-weight:500}
        .timeline-item{display:flex;gap:14px;margin-bottom:14px}
        .timeline-item .dot{width:10px;height:10px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px}
        .timeline-item .text h6{font-size:13px;font-weight:600;margin-bottom:2px}
        .timeline-item .text p{font-size:12px;color:#888}
        .badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-info{background:#dbeafe;color:#2563eb}.badge-danger{background:#fee2e2;color:#dc2626}
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
        <a href="patients.php" class="active"><i class="bi bi-people"></i> Mes patients</a>
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
        <h2><i class="bi bi-people" style="color:var(--primary)"></i>
            <?php echo $mode==='detail'?'Fiche patient':'Mes patients'; ?>
        </h2>
        <?php if ($mode==='detail'): ?><a href="patients.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Retour</a><?php else: ?>
        <span style="font-size:13px;color:#888"><?php echo count($patients); ?> patient(s)</span><?php endif; ?>
    </div>
    <div class="content">

    <?php if ($mode === 'detail' && $patient_detail): ?>
        <div class="detail-grid">
            <div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-person-vcard" style="color:var(--primary)"></i> Informations</h5></div>
                    <div class="card-body">
                        <div style="text-align:center;margin-bottom:16px">
                            <div style="width:70px;height:70px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-size:26px;font-weight:700;margin:0 auto 10px"><?php echo strtoupper(substr($patient_detail['prenom'],0,1).substr($patient_detail['nom'],0,1)); ?></div>
                            <h5><?php echo htmlspecialchars($patient_detail['prenom'].' '.$patient_detail['nom']); ?></h5>
                            <p style="font-size:12px;color:#888"><?php echo htmlspecialchars($patient_detail['numero_patient']); ?></p>
                            <?php if ($patient_detail['groupe_sanguin']): ?><span class="badge-blood"><?php echo $patient_detail['groupe_sanguin']; ?></span><?php endif; ?>
                        </div>
                        <div class="info-row"><span class="label">Email</span><span class="val"><?php echo htmlspecialchars($patient_detail['email']); ?></span></div>
                        <div class="info-row"><span class="label">Téléphone</span><span class="val"><?php echo htmlspecialchars($patient_detail['telephone']); ?></span></div>
                        <div class="info-row"><span class="label">Genre</span><span class="val"><?php echo htmlspecialchars($patient_detail['genre']); ?></span></div>
                        <?php if ($patient_detail['date_naissance']): ?><div class="info-row"><span class="label">Né(e) le</span><span class="val"><?php echo date('d/m/Y',strtotime($patient_detail['date_naissance'])); ?></span></div><?php endif; ?>
                        <?php if ($patient_detail['ville']): ?><div class="info-row"><span class="label">Ville</span><span class="val"><?php echo htmlspecialchars($patient_detail['ville']); ?></span></div><?php endif; ?>
                        <?php if ($patient_detail['allergies']): ?><div class="info-row"><span class="label" style="color:#dc2626">Allergies</span><span class="val" style="color:#dc2626"><?php echo htmlspecialchars($patient_detail['allergies']); ?></span></div><?php endif; ?>
                        <?php if ($patient_detail['maladies_chroniques']): ?><div class="info-row"><span class="label">Maladies chroniques</span><span class="val"><?php echo htmlspecialchars($patient_detail['maladies_chroniques']); ?></span></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-calendar-check" style="color:var(--primary)"></i> Historique RDV</h5></div>
                    <div class="card-body">
                        <?php if ($patient_rdv): foreach ($patient_rdv as $rv): ?>
                        <div class="timeline-item">
                            <div class="dot"></div>
                            <div class="text">
                                <h6><?php echo date('d/m/Y',strtotime($rv['date_rendez_vous'])); ?> à <?php echo substr($rv['heure_debut'],0,5); ?>
                                <?php $badges=['en_attente'=>['warning','Attente'],'confirme'=>['success','Confirmé'],'termine'=>['info','Terminé'],'annule'=>['danger','Annulé']]; $b=$badges[$rv['statut']]??['info',$rv['statut']]; ?>
                                <span class="badge badge-<?php echo $b[0]; ?>"><?php echo $b[1]; ?></span></h6>
                                <p><?php echo htmlspecialchars(substr($rv['motif'],0,80)); ?></p>
                            </div>
                        </div>
                        <?php endforeach; else: ?><p style="color:#bbb;text-align:center;padding:20px">Aucun rendez-vous</p><?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-clipboard2-pulse" style="color:var(--primary)"></i> Historique consultations</h5></div>
                    <div class="card-body">
                        <?php if ($patient_consult): foreach ($patient_consult as $c): ?>
                        <div class="timeline-item">
                            <div class="dot"></div>
                            <div class="text">
                                <h6><?php echo date('d/m/Y H:i',strtotime($c['date_consultation'])); ?> <a href="consultations.php?mode=detail&id=<?php echo $c['id']; ?>" style="font-size:11px;color:var(--primary)">Voir</a></h6>
                                <p><?php echo htmlspecialchars(substr($c['diagnostic']??'—',0,80)); ?></p>
                            </div>
                        </div>
                        <?php endforeach; else: ?><p style="color:#bbb;text-align:center;padding:20px">Aucune consultation</p><?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Rechercher par nom, email, téléphone..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
            <?php if ($search): ?><a href="patients.php" class="btn btn-outline"><i class="bi bi-x"></i></a><?php endif; ?>
        </form>
        <?php if (count($patients) > 0): ?>
        <div class="patients-grid">
            <?php foreach ($patients as $p): ?>
            <div class="patient-card">
                <div class="head">
                    <div class="av"><?php echo strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1)); ?></div>
                    <div class="name">
                        <h5><?php echo htmlspecialchars($p['prenom'].' '.$p['nom']); ?></h5>
                        <p><?php echo htmlspecialchars($p['numero_patient']); ?> <?php if ($p['groupe_sanguin']): ?> · <span class="badge-blood"><?php echo $p['groupe_sanguin']; ?></span><?php endif; ?></p>
                    </div>
                </div>
                <div class="meta">
                    <div class="meta-item"><i class="bi bi-telephone"></i><?php echo htmlspecialchars($p['telephone']); ?></div>
                    <div class="meta-item"><i class="bi bi-geo-alt"></i><?php echo htmlspecialchars($p['ville']??'—'); ?></div>
                    <div class="meta-item"><i class="bi bi-envelope"></i><?php echo htmlspecialchars($p['email']); ?></div>
                    <?php if ($p['dernier_rdv']): ?><div class="meta-item"><i class="bi bi-clock"></i>Dernier : <?php echo date('d/m/Y',strtotime($p['dernier_rdv'])); ?></div><?php endif; ?>
                </div>
                <div class="stats">
                    <div class="stat"><div class="n"><?php echo $p['nb_rdv']; ?></div><div class="l">RDV</div></div>
                    <div class="stat"><div class="n"><?php echo $p['nb_consult']; ?></div><div class="l">Consultations</div></div>
                </div>
                <a href="patients.php?mode=detail&id=<?php echo $p['id']; ?>" class="btn btn-primary" style="width:100%;justify-content:center"><i class="bi bi-eye"></i> Voir la fiche</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div style="background:#fff;border-radius:16px;padding:60px;text-align:center;color:#bbb;box-shadow:0 2px 8px rgba(0,0,0,.05)">
            <i class="bi bi-people" style="font-size:50px;display:block;margin-bottom:14px;opacity:.4"></i>
            <p style="font-size:15px">Aucun patient trouvé</p>
        </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
</div>
</body>
</html>