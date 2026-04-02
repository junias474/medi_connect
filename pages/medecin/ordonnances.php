<?php
/**
 * Ordonnances - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';
$mode = $_GET['mode'] ?? 'liste';
$consult_id = intval($_GET['consultation_id'] ?? 0);

// Traitement : Ajouter prescription
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_prescription'])) {
    $cid = intval($_POST['consultation_id']);
    $medicament  = sanitize($_POST['medicament'] ?? '');
    $dosage      = sanitize($_POST['dosage'] ?? '');
    $frequence   = sanitize($_POST['frequence'] ?? '');
    $duree       = sanitize($_POST['duree'] ?? '');
    $instructions = $_POST['instructions'] ?? '';
    if ($medicament && $dosage && $frequence && $duree) {
        $db->prepare("INSERT INTO prescriptions (consultation_id,medicament,dosage,frequence,duree,instructions) VALUES (?,?,?,?,?,?)")->execute([$cid,$medicament,$dosage,$frequence,$duree,$instructions]);
        $message = "Médicament ajouté à l'ordonnance.";
    } else { $error = "Veuillez remplir tous les champs obligatoires."; }
}

// Supprimer prescription
if (isset($_GET['delete_presc'])) {
    $db->prepare("DELETE FROM prescriptions WHERE id=?")->execute([intval($_GET['delete_presc'])]);
    $message = "Médicament supprimé.";
}

// Consultation de référence
$consult_ref = null;
if ($consult_id) {
    $stmt = $db->prepare("SELECT c.*, CONCAT(u.nom,' ',u.prenom) as patient_nom, u.email, u.telephone FROM consultations c INNER JOIN patients p ON c.patient_id=p.id INNER JOIN utilisateurs u ON p.utilisateur_id=u.id WHERE c.id=? AND c.medecin_id=?");
    $stmt->execute([$consult_id, $medecin_id]);
    $consult_ref = $stmt->fetch();
    if ($consult_ref) {
        $stmt2 = $db->prepare("SELECT * FROM prescriptions WHERE consultation_id=? ORDER BY id ASC");
        $stmt2->execute([$consult_id]);
        $prescriptions_actuelles = $stmt2->fetchAll();
        $mode = 'creer';
    }
}

// Liste toutes les ordonnances (consultations avec prescriptions)
$stmt = $db->prepare("
    SELECT c.id as consult_id, c.date_consultation, CONCAT(u.nom,' ',u.prenom) as patient_nom, COUNT(pr.id) as nb_medicaments
    FROM consultations c
    INNER JOIN patients p ON c.patient_id = p.id
    INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
    INNER JOIN prescriptions pr ON c.id = pr.consultation_id
    WHERE c.medecin_id = ?
    GROUP BY c.id, c.date_consultation, patient_nom
    ORDER BY c.date_consultation DESC
");
$stmt->execute([$medecin_id]);
$ordonnances = $stmt->fetchAll();

// Infos médecin pour l'impression
$stmt = $db->prepare("SELECT m.specialite, m.numero_ordre, m.hopital_affiliation, u.nom, u.prenom, u.telephone FROM medecins m INNER JOIN utilisateurs u ON m.utilisateur_id=u.id WHERE m.id=?");
$stmt->execute([$medecin_id]);
$med_info = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Ordonnances - <?php echo SITE_NAME; ?></title>
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
        .alert{padding:14px 18px;border-radius:12px;margin-bottom:20px;font-size:14px;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}.alert-danger{background:#fee2e2;color:#dc2626}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
        .card-header{padding:18px 22px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
        .card-body{padding:22px}
        .btn{padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-outline{background:#fff;border:2px solid #e5e7eb;color:#555}.btn-sm{padding:7px 14px;font-size:12px;border-radius:8px}
        .btn-print{background:#f0f7ff;color:#2563eb;border:2px solid #bfdbfe}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;transition:border-color .2s}
        .form-group input:focus{border-color:var(--primary)}
        .form-row{display:grid;grid-template-columns:1fr 1fr;gap:14px}
        .layout{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        @media(max-width:900px){.layout{grid-template-columns:1fr}}
        .med-item{background:#f0f7ff;border-radius:10px;padding:14px;margin-bottom:10px;border-left:3px solid var(--primary);display:flex;gap:12px;align-items:flex-start}
        .med-item .icon{width:38px;height:38px;border-radius:10px;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-size:16px;flex-shrink:0}
        .med-item .info h6{font-size:13px;font-weight:700;margin-bottom:4px}
        .med-item .info p{font-size:12px;color:#555}
        .med-item .actions{margin-left:auto}
        .btn-xs{padding:4px 9px;border-radius:7px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:'Outfit',sans-serif;text-decoration:none;transition:all .2s}
        .btn-xs.del{background:#fee2e2;color:#dc2626}
        table{width:100%;border-collapse:collapse}
        th{background:#f8f9ff;padding:13px 16px;text-align:left;font-size:12px;font-weight:700;color:#888;text-transform:uppercase}
        td{padding:13px 16px;border-top:1px solid #f0f2f5;font-size:13px}
        tr:hover td{background:#fafbff}
        .btn-link{background:none;border:none;color:var(--primary);cursor:pointer;font-weight:600;font-size:13px;text-decoration:none;display:inline-flex;align-items:center;gap:4px}
        /* PRINT */
        @media print {
            .sidebar,.topbar,.no-print{display:none!important}
            .main-content{margin-left:0}
            .content{padding:0}
            .ordonnance-print{display:block!important}
        }
        .ordonnance-print{display:none}
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
        <a href="ordonnances.php" class="active"><i class="bi bi-file-medical"></i> Ordonnances</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Compte</span>
        <a href="profil.php"><i class="bi bi-person-gear"></i> Mon profil</a>
        <a href="evaluations.php"><i class="bi bi-star"></i> Évaluations</a>
    </nav>
    <div class="sidebar-footer"><a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar no-print">
        <h2><i class="bi bi-file-medical" style="color:var(--primary)"></i> Ordonnances</h2>
        <?php if ($mode === 'creer'): ?>
            <div style="display:flex;gap:10px">
                <button onclick="window.print()" class="btn btn-print btn-sm"><i class="bi bi-printer"></i> Imprimer</button>
                <a href="ordonnances.php" class="btn btn-outline btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
            </div>
        <?php endif; ?>
    </div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-success no-print"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger no-print"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div><?php endif; ?>

        <?php if ($mode === 'creer' && $consult_ref): ?>
        <!-- Zone d'impression -->
        <div style="background:#fff;border-radius:16px;padding:30px;box-shadow:0 2px 8px rgba(0,0,0,.05);margin-bottom:20px">
            <!-- En-tête ordonnance -->
            <div style="display:flex;justify-content:space-between;align-items:flex-start;padding-bottom:20px;border-bottom:2px solid var(--primary);margin-bottom:20px">
                <div>
                    <h3 style="color:var(--primary);font-size:20px">Dr. <?php echo htmlspecialchars($med_info['prenom'].' '.$med_info['nom']); ?></h3>
                    <p style="font-size:13px;color:#555;margin-top:4px"><?php echo htmlspecialchars($med_info['specialite']); ?></p>
                    <p style="font-size:12px;color:#888">N° Ordre : <?php echo htmlspecialchars($med_info['numero_ordre']); ?></p>
                    <?php if ($med_info['hopital_affiliation']): ?><p style="font-size:12px;color:#888"><?php echo htmlspecialchars($med_info['hopital_affiliation']); ?></p><?php endif; ?>
                </div>
                <div style="text-align:right">
                    <div style="font-size:22px;font-weight:700;color:var(--primary)">ORDONNANCE</div>
                    <p style="font-size:13px;color:#888;margin-top:4px"><?php echo date('d/m/Y',strtotime($consult_ref['date_consultation'])); ?></p>
                </div>
            </div>
            <div style="margin-bottom:18px;background:#f8f9ff;padding:12px 16px;border-radius:10px">
                <strong style="font-size:14px">Patient : <?php echo htmlspecialchars($consult_ref['patient_nom']); ?></strong>
            </div>
            <?php if (isset($prescriptions_actuelles) && count($prescriptions_actuelles) > 0): ?>
            <div style="margin-bottom:20px">
                <?php foreach ($prescriptions_actuelles as $i => $pr): ?>
                <div style="margin-bottom:14px;padding:12px 0;<?php echo $i>0?'border-top:1px dashed #e5e7eb':''; ?>">
                    <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
                        <span style="width:26px;height:26px;border-radius:50%;background:var(--gradient);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700"><?php echo $i+1; ?></span>
                        <strong style="font-size:15px"><?php echo htmlspecialchars($pr['medicament']); ?></strong>
                        <span style="font-size:13px;color:#555">— <?php echo htmlspecialchars($pr['dosage']); ?></span>
                    </div>
                    <p style="font-size:13px;color:#555;margin-left:34px"><i class="bi bi-clock"></i> <?php echo htmlspecialchars($pr['frequence']); ?> pendant <?php echo htmlspecialchars($pr['duree']); ?></p>
                    <?php if ($pr['instructions']): ?><p style="font-size:12px;color:#888;margin-left:34px;font-style:italic"><?php echo htmlspecialchars($pr['instructions']); ?></p><?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?><p style="color:#bbb;text-align:center;padding:20px">Aucun médicament ajouté</p><?php endif; ?>
            <div style="margin-top:40px;text-align:right">
                <p style="font-size:12px;color:#888;margin-bottom:50px">Signature et cachet du médecin</p>
                <div style="width:150px;border-top:1px solid #333;margin-left:auto"></div>
            </div>
        </div>

        <!-- Formulaire ajout médicament -->
        <div class="card no-print">
            <div class="card-header"><h5><i class="bi bi-plus-circle" style="color:var(--primary)"></i> Ajouter un médicament</h5></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="save_prescription" value="1">
                    <input type="hidden" name="consultation_id" value="<?php echo $consult_id; ?>">
                    <div class="form-row">
                        <div class="form-group"><label>Médicament *</label><input type="text" name="medicament" placeholder="Ex: Amoxicilline" required></div>
                        <div class="form-group"><label>Dosage *</label><input type="text" name="dosage" placeholder="Ex: 500 mg" required></div>
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Fréquence *</label><input type="text" name="frequence" placeholder="Ex: 3 fois par jour" required></div>
                        <div class="form-group"><label>Durée *</label><input type="text" name="duree" placeholder="Ex: 7 jours" required></div>
                    </div>
                    <div class="form-group"><label>Instructions supplémentaires</label><input type="text" name="instructions" placeholder="Ex: À prendre pendant les repas"></div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Ajouter</button>
                </form>
                <?php if (isset($prescriptions_actuelles) && count($prescriptions_actuelles) > 0): ?>
                <div style="margin-top:20px">
                    <h6 style="font-size:13px;font-weight:700;color:#555;margin-bottom:12px">Médicaments ajoutés :</h6>
                    <?php foreach ($prescriptions_actuelles as $pr): ?>
                    <div class="med-item">
                        <div class="icon"><i class="bi bi-capsule"></i></div>
                        <div class="info">
                            <h6><?php echo htmlspecialchars($pr['medicament']); ?> — <?php echo htmlspecialchars($pr['dosage']); ?></h6>
                            <p><?php echo htmlspecialchars($pr['frequence']); ?> · <?php echo htmlspecialchars($pr['duree']); ?></p>
                        </div>
                        <div class="actions"><a href="ordonnances.php?consultation_id=<?php echo $consult_id; ?>&delete_presc=<?php echo $pr['id']; ?>" class="btn-xs del"><i class="bi bi-trash"></i></a></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php else: ?>
        <!-- Liste ordonnances -->
        <div class="card">
            <div class="card-header"><h5><i class="bi bi-archive" style="color:var(--primary)"></i> Historique des ordonnances</h5></div>
            <div class="card-body">
                <table>
                    <thead><tr><th>Patient</th><th>Date consultation</th><th>Médicaments</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (count($ordonnances) > 0): foreach ($ordonnances as $ord): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($ord['patient_nom']); ?></strong></td>
                        <td><?php echo date('d/m/Y',strtotime($ord['date_consultation'])); ?></td>
                        <td><span style="background:#f0f7ff;color:var(--primary);padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600"><?php echo $ord['nb_medicaments']; ?> médicament(s)</span></td>
                        <td>
                            <a href="ordonnances.php?consultation_id=<?php echo $ord['consult_id']; ?>" class="btn btn-primary btn-sm"><i class="bi bi-eye"></i> Voir / Modifier</a>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="4" style="text-align:center;padding:40px;color:#bbb"><i class="bi bi-file-earmark-x" style="font-size:36px;display:block;margin-bottom:10px"></i>Aucune ordonnance rédigée</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>