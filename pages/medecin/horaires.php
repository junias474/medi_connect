<?php
/**
 * Mes horaires - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$medecin_id = $_SESSION['medecin_id'];
$db = Database::getInstance()->getConnection();

$message = ''; $error = '';
$jours = ['Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];

// Sauvegarde des horaires
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_horaires'])) {
    try {
        $db->prepare("DELETE FROM horaires_medecin WHERE medecin_id=?")->execute([$medecin_id]);
        foreach ($jours as $jour) {
            if (isset($_POST['disponible_'.$jour])) {
                $debut = $_POST['debut_'.$jour] ?? '08:00';
                $fin   = $_POST['fin_'.$jour]   ?? '17:00';
                $db->prepare("INSERT INTO horaires_medecin (medecin_id,jour_semaine,heure_debut,heure_fin,disponible) VALUES (?,?,?,?,1)")->execute([$medecin_id,$jour,$debut,$fin]);
            }
        }
        // Indisponibilité
        if (!empty($_POST['indispo_debut']) && !empty($_POST['indispo_fin'])) {
            $db->prepare("INSERT INTO indisponibilites_medecin (medecin_id,date_debut,date_fin,motif) VALUES (?,?,?,?)")->execute([$medecin_id,$_POST['indispo_debut'],$_POST['indispo_fin'],sanitize($_POST['indispo_motif']??'')]);
        }
        $message = "Horaires enregistrés avec succès.";
    } catch(PDOException $e) { $error = "Erreur lors de l'enregistrement."; }
}

// Supprimer indisponibilité
if (isset($_GET['delete_indispo'])) {
    $db->prepare("DELETE FROM indisponibilites_medecin WHERE id=? AND medecin_id=?")->execute([intval($_GET['delete_indispo']), $medecin_id]);
    header("Location: horaires.php?msg=deleted"); exit();
}

// Charger horaires
$stmt = $db->prepare("SELECT * FROM horaires_medecin WHERE medecin_id=? ORDER BY FIELD(jour_semaine,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche')");
$stmt->execute([$medecin_id]);
$horaires_raw = $stmt->fetchAll();
$horaires = [];
foreach ($horaires_raw as $h) $horaires[$h['jour_semaine']] = $h;

// Indisponibilités
$stmt = $db->prepare("SELECT * FROM indisponibilites_medecin WHERE medecin_id=? AND date_fin >= CURDATE() ORDER BY date_debut ASC");
$stmt->execute([$medecin_id]);
$indispos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Mes horaires - <?php echo SITE_NAME; ?></title>
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
        .layout{display:grid;grid-template-columns:2fr 1fr;gap:20px}
        @media(max-width:900px){.layout{grid-template-columns:1fr}}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.05);overflow:hidden;margin-bottom:20px}
        .card-header{padding:18px 22px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
        .card-body{padding:22px}
        .jour-row{display:flex;align-items:center;gap:14px;padding:14px;border-radius:12px;margin-bottom:10px;background:#f8f9ff;transition:background .2s}
        .jour-row.active{background:#eef0ff;border:1px solid #c7d2fe}
        .jour-row.inactive{background:#fafafa;opacity:.7}
        .jour-label{width:90px;font-weight:700;font-size:14px}
        .toggle-switch{position:relative;width:44px;height:24px;flex-shrink:0}
        .toggle-switch input{opacity:0;width:0;height:0}
        .slider{position:absolute;top:0;left:0;right:0;bottom:0;background:#d1d5db;border-radius:24px;cursor:pointer;transition:.3s}
        .slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.3s}
        input:checked+.slider{background:var(--primary)}
        input:checked+.slider:before{transform:translateX(20px)}
        .time-inputs{display:flex;align-items:center;gap:8px;flex:1}
        .time-inputs input{padding:8px 12px;border:2px solid #e5e7eb;border-radius:8px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;width:100px}
        .time-inputs input:focus{border-color:var(--primary)}
        .time-inputs span{color:#888;font-size:13px}
        .btn{padding:10px 22px;border-radius:10px;border:none;cursor:pointer;font-weight:700;font-size:14px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:7px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-danger{background:#fee2e2;color:#dc2626;border:none}
        .btn:hover{opacity:.88;transform:translateY(-1px)}
        .form-group{margin-bottom:16px}
        .form-group label{display:block;font-size:13px;font-weight:600;color:#555;margin-bottom:6px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;transition:border-color .2s}
        .form-group input:focus{border-color:var(--primary)}
        .indispo-item{display:flex;align-items:center;gap:12px;padding:13px;background:#fff9f0;border-radius:10px;margin-bottom:10px;border-left:3px solid #f59e0b}
        .indispo-item .info{flex:1}
        .indispo-item .info h6{font-size:13px;font-weight:700;margin-bottom:3px}
        .indispo-item .info p{font-size:12px;color:#888}
        .btn-xs{padding:5px 10px;border-radius:7px;border:none;cursor:pointer;font-size:11px;font-weight:600;font-family:'Outfit',sans-serif;text-decoration:none;transition:all .2s}
        .btn-xs.del{background:#fee2e2;color:#dc2626}
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
        <a href="horaires.php" class="active"><i class="bi bi-clock"></i> Mes horaires</a>
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
        <h2><i class="bi bi-clock" style="color:var(--primary)"></i> Mes horaires de disponibilité</h2>
    </div>
    <div class="content">
        <?php if ($message): ?><div class="alert alert-success"><i class="bi bi-check-circle"></i><?php echo $message; ?></div><?php endif; ?>
        <?php if ($error):   ?><div class="alert alert-danger"><i class="bi bi-exclamation-circle"></i><?php echo $error; ?></div><?php endif; ?>

        <div class="layout">
            <div>
                <!-- Horaires hebdomadaires -->
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-calendar-week" style="color:var(--primary)"></i> Plages horaires hebdomadaires</h5></div>
                    <div class="card-body">
                        <form method="POST">
                            <?php foreach ($jours as $jour):
                                $h = $horaires[$jour] ?? null;
                                $actif = $h && $h['disponible'];
                            ?>
                            <div class="jour-row <?php echo $actif?'active':'inactive'; ?>" id="row_<?php echo $jour; ?>">
                                <span class="jour-label"><?php echo $jour; ?></span>
                                <label class="toggle-switch">
                                    <input type="checkbox" name="disponible_<?php echo $jour; ?>" id="check_<?php echo $jour; ?>" <?php echo $actif?'checked':''; ?> onchange="toggleJour('<?php echo $jour; ?>')">
                                    <span class="slider"></span>
                                </label>
                                <div class="time-inputs" id="times_<?php echo $jour; ?>" style="<?php echo $actif?'':'opacity:.4;pointer-events:none'; ?>">
                                    <input type="time" name="debut_<?php echo $jour; ?>" value="<?php echo $h?substr($h['heure_debut'],0,5):'08:00'; ?>">
                                    <span>→</span>
                                    <input type="time" name="fin_<?php echo $jour; ?>"   value="<?php echo $h?substr($h['heure_fin'],0,5):'17:00'; ?>">
                                </div>
                            </div>
                            <?php endforeach; ?>
                            <div style="margin-top:20px">
                                <button type="submit" name="save_horaires" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer mes horaires</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div>
                <!-- Indisponibilités -->
                <div class="card">
                    <div class="card-header"><h5><i class="bi bi-calendar-x" style="color:#f59e0b"></i> Indisponibilités</h5></div>
                    <div class="card-body">
                        <?php if (count($indispos) > 0): foreach ($indispos as $ind): ?>
                        <div class="indispo-item">
                            <i class="bi bi-ban" style="color:#f59e0b;font-size:20px"></i>
                            <div class="info">
                                <h6><?php echo date('d/m/Y',strtotime($ind['date_debut'])); ?> → <?php echo date('d/m/Y',strtotime($ind['date_fin'])); ?></h6>
                                <p><?php echo htmlspecialchars($ind['motif']??'Aucun motif'); ?></p>
                            </div>
                            <a href="horaires.php?delete_indispo=<?php echo $ind['id']; ?>" class="btn-xs del" onclick="return confirm('Supprimer cette indisponibilité ?')"><i class="bi bi-trash"></i></a>
                        </div>
                        <?php endforeach; else: ?>
                        <p style="color:#bbb;text-align:center;padding:20px">Aucune indisponibilité planifiée</p>
                        <?php endif; ?>

                        <hr style="margin:20px 0;border:none;border-top:1px solid #f0f2f5">
                        <h6 style="font-size:13px;font-weight:700;margin-bottom:14px;color:#555"><i class="bi bi-plus-circle"></i> Ajouter une indisponibilité</h6>
                        <form method="POST">
                            <input type="hidden" name="save_horaires" value="1">
                            <div class="form-group"><label>Date début</label><input type="date" name="indispo_debut" min="<?php echo date('Y-m-d'); ?>"></div>
                            <div class="form-group"><label>Date fin</label><input type="date" name="indispo_fin" min="<?php echo date('Y-m-d'); ?>"></div>
                            <div class="form-group"><label>Motif (optionnel)</label><input type="text" name="indispo_motif" placeholder="Congé, formation..."></div>
                            <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center"><i class="bi bi-plus-lg"></i> Ajouter</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleJour(jour) {
    const cb = document.getElementById('check_'+jour);
    const row = document.getElementById('row_'+jour);
    const times = document.getElementById('times_'+jour);
    if (cb.checked) {
        row.classList.add('active'); row.classList.remove('inactive');
        times.style.opacity = '1'; times.style.pointerEvents = 'auto';
    } else {
        row.classList.remove('active'); row.classList.add('inactive');
        times.style.opacity = '.4'; times.style.pointerEvents = 'none';
    }
}
</script>
</body>
</html>