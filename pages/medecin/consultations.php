<?php
/**
 * Consultations - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';
$mode = $_GET['mode'] ?? 'liste'; // liste | nouvelle | detail
$rdv_id_param = intval($_GET['rdv_id'] ?? 0);
$consult_id   = intval($_GET['id'] ?? 0);

// Traitement formulaire nouvelle consultation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_consultation') {
    $rdv_id      = intval($_POST['rdv_id']);
    $patient_id  = intval($_POST['patient_id']);
    $diagnostic  = sanitize($_POST['diagnostic'] ?? '');
    $prescription = $_POST['prescription'] ?? '';
    $examens     = $_POST['examens_demandes'] ?? '';
    $recommandations = $_POST['recommandations'] ?? '';
    $notes       = $_POST['notes_medicales'] ?? '';
    $duree       = intval($_POST['duree_consultation'] ?? 30);

    try {
        // Vérifier si consultation existe déjà
        $existing = $db->prepare("SELECT id FROM consultations WHERE rendez_vous_id = ?");
        $existing->execute([$rdv_id]);
        if ($existing->fetch()) {
            $stmt = $db->prepare("UPDATE consultations SET diagnostic=?,prescription=?,examens_demandes=?,recommandations=?,notes_medicales=?,duree_consultation=?,statut='termine' WHERE rendez_vous_id=?");
            $stmt->execute([$diagnostic,$prescription,$examens,$recommandations,$notes,$duree,$rdv_id]);
        } else {
            $stmt = $db->prepare("INSERT INTO consultations (rendez_vous_id,patient_id,medecin_id,diagnostic,prescription,examens_demandes,recommandations,notes_medicales,duree_consultation,date_consultation,statut) VALUES (?,?,?,?,?,?,?,?,?,NOW(),'termine')");
            $stmt->execute([$rdv_id,$patient_id,$medecin_id,$diagnostic,$prescription,$examens,$recommandations,$notes,$duree]);
        }
        $db->prepare("UPDATE rendez_vous SET statut='termine' WHERE id=?")->execute([$rdv_id]);
        $message = "Consultation enregistrée avec succès.";
        $mode = 'liste';
    } catch(PDOException $e) {
        $error = "Erreur lors de l'enregistrement.";
        error_log($e->getMessage());
    }
}

// RDV pour nouvelle consultation
$rdv_info = null;
if ($mode === 'nouvelle' && $rdv_id_param) {
    $stmt = $db->prepare("SELECT rv.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, p.id as patient_id FROM rendez_vous rv INNER JOIN patients p ON rv.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE rv.id=? AND rv.medecin_id=?");
    $stmt->execute([$rdv_id_param, $medecin_id]);
    $rdv_info = $stmt->fetch();
    if (!$rdv_info) { $mode = 'liste'; }
}

// Détail consultation
$consult_detail = null;
if ($mode === 'detail' && $consult_id) {
    $stmt = $db->prepare("SELECT c.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, u.telephone as patient_tel FROM consultations c INNER JOIN patients p ON c.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE c.id=? AND c.medecin_id=?");
    $stmt->execute([$consult_id, $medecin_id]);
    $consult_detail = $stmt->fetch();
    // Prescriptions
    $pstmt = $db->prepare("SELECT * FROM prescriptions WHERE consultation_id=?");
    $pstmt->execute([$consult_id]);
    $prescriptions = $pstmt->fetchAll();
}

// Liste consultations
$search = $_GET['search'] ?? '';
$where = "WHERE c.medecin_id = ?"; $params = [$medecin_id];
if ($search) { $where .= " AND CONCAT(u.nom,' ',u.prenom) LIKE ?"; $params[] = "%$search%"; }
$stmt = $db->prepare("SELECT c.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, rv.date_rendez_vous FROM consultations c INNER JOIN patients p ON c.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id INNER JOIN rendez_vous rv ON c.rendez_vous_id=rv.id $where ORDER BY c.date_consultation DESC");
$stmt->execute($params);
$consultations = $stmt->fetchAll();

// RDV confirmés sans consultation (pour pouvoir démarrer)
$stmt2 = $db->prepare("SELECT rv.*, CONCAT(u.nom,' ',u.prenom) as patient_nom FROM rendez_vous rv INNER JOIN patients p ON rv.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE rv.medecin_id=? AND rv.statut='confirme' AND rv.id NOT IN (SELECT rendez_vous_id FROM consultations) ORDER BY rv.date_rendez_vous ASC");
$stmt2->execute([$medecin_id]);
$rdv_a_consulter = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Consultations - <?php echo SITE_NAME; ?></title>
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
        .topbar h2{font-size:22px;font-weight:700}
        .content{padding:28px 30px}
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}.alert-danger{background:#fee2e2;color:#dc2626}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
        .card-header{padding:18px 22px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
        .card-body{padding:22px}
        .btn{padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-outline{background:#fff;border:2px solid #e5e7eb;color:#555}
        .btn-sm{padding:6px 14px;font-size:12px;border-radius:8px}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .form-group{margin-bottom:18px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#444;margin-bottom:6px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:14px;outline:none;transition:border-color .2s;resize:vertical}
        .form-group input:focus,.form-group textarea:focus{border-color:var(--primary)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:18px}
        .consult-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px}
        .rdv-card{background:#fff;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;gap:14px;border-left:4px solid var(--primary)}
        .rdv-card .icon{width:44px;height:44px;border-radius:12px;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-size:20px;flex-shrink:0}
        .rdv-card .info h6{font-size:14px;font-weight:600}.rdv-card .info p{font-size:12px;color:#888}
        .rdv-card a{margin-left:auto}.badge{display:inline-block;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600}
        .badge-success{background:#dcfce7;color:#16a34a}
        table{width:100%;border-collapse:collapse}
        th{background:#f8f9ff;padding:13px 16px;text-align:left;font-size:12px;font-weight:700;color:#888;text-transform:uppercase}
        td{padding:13px 16px;border-top:1px solid #f0f2f5;font-size:13px;vertical-align:middle}
        tr:hover td{background:#fafbff}
        .btn-xs{padding:5px 10px;border-radius:7px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:3px;text-decoration:none;transition:all .2s}
        .btn-xs.view{background:#f3f4f6;color:#374151}.btn-xs:hover{opacity:.8}
        .search-bar{display:flex;gap:12px;margin-bottom:18px}
        .search-bar input{flex:1;padding:10px 16px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none}
        .search-bar input:focus{border-color:var(--primary)}
        .detail-section{margin-bottom:20px}
        .detail-section h6{font-size:13px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px}
        .detail-section p{font-size:14px;color:#333;background:#f8f9ff;padding:14px;border-radius:10px;white-space:pre-wrap}
        .prescription-item{background:#f0f7ff;border-radius:10px;padding:12px 16px;margin-bottom:8px;border-left:3px solid var(--primary)}
        .prescription-item h6{font-size:13px;font-weight:700;margin-bottom:4px}
        .prescription-item p{font-size:12px;color:#666}
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
        <a href="consultations.php" class="active"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
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
        <h2><i class="bi bi-clipboard2-pulse" style="color:var(--primary)"></i>
            <?php echo $mode==='nouvelle'?'Nouvelle consultation':($mode==='detail'?'Détail consultation':'Mes consultations'); ?>
        </h2>
        <?php if ($mode === 'liste'): ?>
            <span style="font-size:13px;color:#888"><?php echo count($consultations); ?> consultation(s)</span>
        <?php else: ?>
            <a href="consultations.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
        <?php endif; ?>
    </div>

    <div class="content">
        <?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div><?php endif; ?>

        <?php if ($mode === 'liste'): ?>
            <!-- RDV en attente de consultation -->
            <?php if (count($rdv_a_consulter) > 0): ?>
            <div class="card">
                <div class="card-header"><h5><i class="bi bi-hourglass-split" style="color:#f59e0b"></i> RDV confirmés — à consulter (<?php echo count($rdv_a_consulter); ?>)</h5></div>
                <div class="card-body">
                    <div class="consult-grid">
                    <?php foreach ($rdv_a_consulter as $rv): ?>
                        <div class="rdv-card">
                            <div class="icon"><i class="bi bi-person-check"></i></div>
                            <div class="info">
                                <h6><?php echo htmlspecialchars($rv['patient_nom']); ?></h6>
                                <p><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y',strtotime($rv['date_rendez_vous'])); ?> à <?php echo substr($rv['heure_debut'],0,5); ?></p>
                            </div>
                            <a href="consultations.php?mode=nouvelle&rdv_id=<?php echo $rv['id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-clipboard2-plus"></i> Démarrer</a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Historique -->
            <div class="card">
                <div class="card-header"><h5><i class="bi bi-clock-history" style="color:var(--primary)"></i> Historique des consultations</h5></div>
                <div class="card-body">
                    <form method="GET" class="search-bar">
                        <input type="text" name="search" placeholder="Rechercher un patient..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
                        <?php if ($search): ?><a href="consultations.php" class="btn btn-outline btn-sm"><i class="bi bi-x"></i></a><?php endif; ?>
                    </form>
                    <table>
                        <thead><tr><th>Patient</th><th>Date consultation</th><th>Diagnostic</th><th>Durée</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php if (count($consultations) > 0): foreach ($consultations as $c): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($c['patient_nom']); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i',strtotime($c['date_consultation'])); ?></td>
                            <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?php echo htmlspecialchars(substr($c['diagnostic']??'—',0,60)); ?></td>
                            <td><?php echo $c['duree_consultation']?$c['duree_consultation'].' min':'—'; ?></td>
                            <td><a href="consultations.php?mode=detail&id=<?php echo $c['id']; ?>" class="btn-xs view"><i class="bi bi-eye"></i> Détail</a></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:#bbb"><i class="bi bi-folder2-open" style="font-size:36px;display:block;margin-bottom:10px"></i>Aucune consultation enregistrée</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($mode === 'nouvelle' && $rdv_info): ?>
            <div class="card">
                <div class="card-header"><h5><i class="bi bi-clipboard2-plus" style="color:var(--primary)"></i> Consultation — <?php echo htmlspecialchars($rdv_info['patient_nom']); ?></h5></div>
                <div class="card-body">
                    <div style="background:#f0f7ff;border-radius:12px;padding:14px 18px;margin-bottom:22px;font-size:13px;color:#2563eb">
                        <i class="bi bi-info-circle"></i> RDV du <?php echo date('d/m/Y',strtotime($rdv_info['date_rendez_vous'])); ?> à <?php echo substr($rdv_info['heure_debut'],0,5); ?>
                        — Motif : <?php echo htmlspecialchars($rdv_info['motif']); ?>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="action" value="save_consultation">
                        <input type="hidden" name="rdv_id" value="<?php echo $rdv_info['id']; ?>">
                        <input type="hidden" name="patient_id" value="<?php echo $rdv_info['patient_id']; ?>">
                        <div class="form-group"><label><i class="bi bi-clipboard2-pulse"></i> Diagnostic *</label><textarea name="diagnostic" rows="4" placeholder="Diagnostic clinique..." required></textarea></div>
                        <div class="form-group"><label><i class="bi bi-capsule"></i> Prescription / Traitement</label><textarea name="prescription" rows="4" placeholder="Médicaments, dosages..."></textarea></div>
                        <div class="form-group"><label><i class="bi bi-journal-medical"></i> Examens demandés</label><textarea name="examens_demandes" rows="3" placeholder="Analyses, imageries..."></textarea></div>
                        <div class="form-group"><label><i class="bi bi-chat-text"></i> Recommandations au patient</label><textarea name="recommandations" rows="3" placeholder="Conseils, mode de vie..."></textarea></div>
                        <div class="form-group"><label><i class="bi bi-lock"></i> Notes médicales (confidentielles)</label><textarea name="notes_medicales" rows="3" placeholder="Notes internes..."></textarea></div>
                        <div class="form-group" style="max-width:200px"><label><i class="bi bi-stopwatch"></i> Durée (minutes)</label><input type="number" name="duree_consultation" value="30" min="5" max="240"></div>
                        <div style="display:flex;gap:12px;margin-top:8px">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer la consultation</button>
                            <a href="consultations.php" class="btn btn-outline"><i class="bi bi-x-lg"></i> Annuler</a>
                        </div>
                    </form>
                </div>
            </div>

        <?php elseif ($mode === 'detail' && $consult_detail): ?>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-person" style="color:var(--primary)"></i> Patient</h5></div>
                    <div class="card-body">
                        <p><strong><?php echo htmlspecialchars($consult_detail['patient_nom']); ?></strong></p>
                        <p style="color:#888;font-size:13px;margin-top:6px"><i class="bi bi-telephone"></i> <?php echo htmlspecialchars($consult_detail['patient_tel']); ?></p>
                        <p style="color:#888;font-size:13px;margin-top:4px"><i class="bi bi-calendar3"></i> Consultation le <?php echo date('d/m/Y H:i',strtotime($consult_detail['date_consultation'])); ?></p>
                        <?php if ($consult_detail['duree_consultation']): ?><p style="color:#888;font-size:13px;margin-top:4px"><i class="bi bi-stopwatch"></i> Durée : <?php echo $consult_detail['duree_consultation']; ?> minutes</p><?php endif; ?>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-clipboard2-pulse" style="color:var(--primary)"></i> Diagnostic</h5></div>
                    <div class="card-body">
                        <div class="detail-section"><p><?php echo htmlspecialchars($consult_detail['diagnostic'] ?? '—'); ?></p></div>
                    </div>
                </div>
                <?php if ($consult_detail['prescription']): ?>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-capsule" style="color:var(--primary)"></i> Prescription</h5></div>
                    <div class="card-body"><div class="detail-section"><p><?php echo nl2br(htmlspecialchars($consult_detail['prescription'])); ?></p></div></div>
                </div>
                <?php endif; ?>
                <?php if ($consult_detail['recommandations']): ?>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-chat-text" style="color:var(--primary)"></i> Recommandations</h5></div>
                    <div class="card-body"><div class="detail-section"><p><?php echo nl2br(htmlspecialchars($consult_detail['recommandations'])); ?></p></div></div>
                </div>
                <?php endif; ?>
                <?php if ($consult_detail['examens_demandes']): ?>
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-journal-medical" style="color:var(--primary)"></i> Examens demandés</h5></div>
                    <div class="card-body"><div class="detail-section"><p><?php echo nl2br(htmlspecialchars($consult_detail['examens_demandes'])); ?></p></div></div>
                </div>
                <?php endif; ?>
            </div>
            <div style="margin-top:10px"><a href="ordonnances.php?consultation_id=<?php echo $consult_detail['id']; ?>" class="btn btn-primary"><i class="bi bi-file-earmark-plus"></i> Générer une ordonnance</a></div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>