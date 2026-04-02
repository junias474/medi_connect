<?php
/**
 * Patient Consultations History Page
 * Medical Consultation Application
 */

require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../auth/login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$user_id    = $_SESSION['user_id'];

// --- Filters & pagination ---
$filtre_specialite = $_GET['specialite'] ?? 'toutes';
$recherche         = trim($_GET['recherche'] ?? '');
$page              = max(1, (int)($_GET['page'] ?? 1));
$per_page          = 6;
$offset            = ($page - 1) * $per_page;

try {
    $db = Database::getInstance()->getConnection();

    // Get list of specialties for the filter
    $specStmt = $db->prepare("
        SELECT DISTINCT m.specialite
        FROM consultations c
        INNER JOIN medecins m ON c.medecin_id = m.id
        WHERE c.patient_id = ?
        ORDER BY m.specialite
    ");
    $specStmt->execute([$patient_id]);
    $specialites = $specStmt->fetchAll(PDO::FETCH_COLUMN);

    // Build WHERE
    $where  = "c.patient_id = ?";
    $params = [$patient_id];

    if ($filtre_specialite !== 'toutes') {
        $where   .= " AND m.specialite = ?";
        $params[] = $filtre_specialite;
    }
    if ($recherche !== '') {
        $where   .= " AND (CONCAT(u.nom,' ',u.prenom) LIKE ? OR c.diagnostic LIKE ? OR c.prescription LIKE ?)";
        $like     = "%$recherche%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    // Total count
    $countStmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM consultations c
        INNER JOIN medecins m     ON c.medecin_id    = m.id
        INNER JOIN utilisateurs u ON m.utilisateur_id = u.id
        WHERE $where
    ");
    $countStmt->execute($params);
    $total_consultations = $countStmt->fetch()['total'];
    $total_pages         = ceil($total_consultations / $per_page);

    // Fetch consultations
    $paramsPage = array_merge($params, [$per_page, $offset]);
    $stmt = $db->prepare("
        SELECT c.*,
               rv.date_rendez_vous,
               rv.type_consultation,
               rv.motif,
               CONCAT(u.nom, ' ', u.prenom) AS medecin_nom,
               m.specialite,
               m.tarif_consultation
        FROM consultations c
        INNER JOIN rendez_vous rv ON c.rendez_vous_id  = rv.id
        INNER JOIN medecins m     ON c.medecin_id      = m.id
        INNER JOIN utilisateurs u ON m.utilisateur_id  = u.id
        WHERE $where
        ORDER BY c.date_consultation DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($paramsPage);
    $consultations = $stmt->fetchAll();

    // Fetch prescriptions for each consultation
    $prescStmt = $db->prepare("SELECT * FROM prescriptions WHERE consultation_id = ? ORDER BY id");

    // Global stats
    $statsStmt = $db->prepare("
        SELECT
            COUNT(*) AS total,
            SUM(statut = 'termine') AS terminees,
            COUNT(DISTINCT medecin_id) AS medecins_consultes
        FROM consultations WHERE patient_id = ?
    ");
    $statsStmt->execute([$patient_id]);
    $stats = $statsStmt->fetch();

    // Unread notifications
    $notifStmt = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE utilisateur_id = ? AND lu = 0");
    $notifStmt->execute([$user_id]);
    $notif_non_lues = $notifStmt->fetch();

} catch (PDOException $e) {
    error_log("Consultations page error: " . $e->getMessage());
    $consultations       = [];
    $specialites         = [];
    $total_consultations = 0;
    $total_pages         = 1;
    $stats               = ['total'=>0,'terminees'=>0,'medecins_consultes'=>0];
    $notif_non_lues      = ['total'=>0];
}

$months_en = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function severityColor(string $s): string {
    return match($s) {
        'termine'  => 'success',
        'en_cours' => 'warning',
        default    => 'secondary'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Consultations - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
        }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f5f7fa; }

        /* ---- Sidebar ---- */
        .sidebar {
            position: fixed; top: 0; left: 0;
            height: 100vh; width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white; z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        .sidebar-header { padding: 30px 20px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-header h4 { margin: 10px 0 5px; font-size: 18px; }
        .sidebar-header p  { margin: 0; font-size: 13px; opacity: .8; }
        .sidebar-menu { padding: 20px 0; }
        .sidebar-menu a {
            display: flex; align-items: center; padding: 15px 25px;
            color: white; text-decoration: none;
            transition: all .3s; border-left: 4px solid transparent;
        }
        .sidebar-menu a:hover, .sidebar-menu a.active {
            background: rgba(255,255,255,0.1); border-left-color: white;
        }
        .sidebar-menu a i { width: 30px; font-size: 18px; }
        .sidebar-footer {
            position: absolute; bottom: 0; width: 100%;
            padding: 20px; border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* ---- Layout ---- */
        .main-content { margin-left: var(--sidebar-width); }
        .top-navbar {
            background: white; padding: 20px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex; justify-content: space-between; align-items: center;
        }
        .top-navbar h2 { margin: 0; font-size: 24px; color: #333; }
        .user-info { display: flex; align-items: center; gap: 15px; }
        .notification-badge { position: relative; font-size: 20px; color: #666; cursor: pointer; }
        .notification-badge .badge { position: absolute; top: -8px; right: -8px; font-size: 10px; }
        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: bold;
        }
        .content-area { padding: 30px; }

        /* ---- Stats ---- */
        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px,1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card {
            background: white; border-radius: 15px; padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            text-align: center; transition: transform .2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .icon {
            width: 50px; height: 50px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px; color: white; margin: 0 auto 12px;
        }
        .stat-card h3 { font-size: 28px; font-weight: 700; margin: 0 0 4px; }
        .stat-card p  { font-size: 13px; color: #888; margin: 0; }

        /* ---- Search/filter bar ---- */
        .filter-bar {
            background: white; border-radius: 15px; padding: 20px 25px;
            margin-bottom: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex; align-items: center; gap: 15px; flex-wrap: wrap;
        }
        .filter-bar input, .filter-bar select {
            border-radius: 8px; border: 2px solid #e0e0e0;
            padding: 7px 14px; font-size: 14px; transition: border-color .3s;
        }
        .filter-bar input:focus, .filter-bar select:focus { border-color: var(--primary-color); outline: none; }

        /* ---- Consultation card ---- */
        .consult-card {
            background: white; border-radius: 15px; margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            overflow: hidden; transition: transform .3s, box-shadow .3s;
        }
        .consult-card:hover { transform: translateY(-4px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }

        .consult-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white; padding: 18px 25px;
            display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;
        }
        .consult-header .doctor { display: flex; align-items: center; gap: 12px; }
        .consult-header .avatar {
            width: 46px; height: 46px; border-radius: 50%;
            background: rgba(255,255,255,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px;
        }
        .consult-header h5 { margin: 0; font-size: 16px; font-weight: 600; }
        .consult-header .specialty { font-size: 13px; opacity: .85; }
        .consult-header .date-badge {
            background: rgba(255,255,255,0.2); border-radius: 8px;
            padding: 6px 14px; font-size: 13px; font-weight: 600;
        }

        .consult-body { padding: 20px 25px; }

        .info-section { margin-bottom: 16px; }
        .info-section-title {
            font-size: 12px; font-weight: 700; text-transform: uppercase;
            letter-spacing: .8px; color: var(--primary-color);
            margin-bottom: 6px; display: flex; align-items: center; gap: 6px;
        }
        .info-section-content {
            font-size: 14px; color: #444; background: #f8f9fa;
            border-radius: 8px; padding: 10px 14px; line-height: 1.6;
        }

        /* Prescriptions table */
        .presc-table { width: 100%; font-size: 13px; border-collapse: collapse; }
        .presc-table th { background: #f0f4ff; color: var(--primary-color); font-weight: 600; padding: 8px 12px; text-align: left; }
        .presc-table td { padding: 8px 12px; border-bottom: 1px solid #f0f0f0; color: #555; }
        .presc-table tr:last-child td { border-bottom: none; }

        /* Meta row */
        .meta-row { display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 16px; }
        .meta-item { display: flex; align-items: center; gap: 6px; font-size: 13px; color: #666; }
        .meta-item i { color: var(--primary-color); width: 14px; }

        /* ---- Empty state ---- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 64px; color: #d0d7de; margin-bottom: 20px; }
        .empty-state h4 { color: #555; margin-bottom: 10px; }
        .empty-state p  { color: #888; }

        /* ---- Pagination ---- */
        .pagination .page-link {
            border-radius: 8px !important; margin: 0 3px;
            color: var(--primary-color); border-color: #e0e0e0;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: transparent; color: white;
        }

        /* Modal */
        .modal-header { background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; border-radius: 15px 15px 0 0; }
        .modal-content { border-radius: 15px; border: none; }
        .modal-header .btn-close { filter: invert(1); }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-user-circle" style="font-size:60px;"></i>
        <h4><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></h4>
        <p><i class="fas fa-circle" style="font-size:8px;color:#4ade80;"></i> Patient</p>
    </div>
    <div class="sidebar-menu">
        <a href="index.php"><i class="fas fa-home"></i><span>Home</span></a>
        <a href="rendez-vous.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
        <a href="nouveau-rdv.php"><i class="fas fa-calendar-plus"></i><span>New Appointment</span></a>
        <a href="consultations.php" class="active"><i class="fas fa-stethoscope"></i><span>My Consultations</span></a>
        <a href="symptomes.php"><i class="fas fa-notes-medical"></i><span>My Symptoms</span></a>
        <a href="documents.php"><i class="fas fa-file-medical"></i><span>Medical Documents</span></a>
        <a href="profil.php"><i class="fas fa-user-cog"></i><span>My Profile</span></a>
    </div>
    <div class="sidebar-footer">
        <a href="../../logout.php" style="color:white;text-decoration:none;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-sign-out-alt"></i><span>Logout</span>
        </a>
    </div>
</div>

<!-- Main Content -->
<div class="main-content">
    <!-- Top Navbar -->
    <div class="top-navbar">
        <h2><i class="fas fa-stethoscope" style="color:var(--primary-color);"></i> My Consultations</h2>
        <div class="user-info">
            <div class="notification-badge">
                <i class="fas fa-bell"></i>
                <?php if ($notif_non_lues['total'] > 0): ?>
                    <span class="badge bg-danger"><?php echo $notif_non_lues['total']; ?></span>
                <?php endif; ?>
            </div>
            <div class="user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?>
            </div>
        </div>
    </div>

    <div class="content-area">

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="icon" style="background:linear-gradient(135deg,#667eea,#764ba2);">
                    <i class="fas fa-stethoscope"></i>
                </div>
                <h3><?php echo $stats['total']; ?></h3>
                <p>Total Consultations</p>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:linear-gradient(135deg,#11998e,#38ef7d);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3><?php echo $stats['terminees']; ?></h3>
                <p>Completed</p>
            </div>
            <div class="stat-card">
                <div class="icon" style="background:linear-gradient(135deg,#f093fb,#f5576c);">
                    <i class="fas fa-user-md"></i>
                </div>
                <h3><?php echo $stats['medecins_consultes']; ?></h3>
                <p>Doctors Consulted</p>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar">
            <i class="fas fa-filter" style="color:var(--primary-color);"></i>
            <form method="GET" action="" class="d-flex align-items-center gap-3 flex-wrap w-100">
                <!-- Search -->
                <div class="d-flex align-items-center gap-2 flex-grow-1">
                    <i class="fas fa-search" style="color:#aaa;"></i>
                    <input type="text" name="recherche" placeholder="Search by doctor or diagnosis..."
                           value="<?php echo htmlspecialchars($recherche); ?>" style="flex:1;max-width:350px;">
                </div>
                <!-- Specialty filter -->
                <?php if (count($specialites) > 1): ?>
                <div class="d-flex align-items-center gap-2">
                    <label style="font-weight:600;color:#555;font-size:14px;margin:0;">Specialty</label>
                    <select name="specialite" onchange="this.form.submit()">
                        <option value="toutes" <?php echo $filtre_specialite==='toutes'?'selected':''; ?>>All</option>
                        <?php foreach ($specialites as $sp): ?>
                            <option value="<?php echo htmlspecialchars($sp); ?>"
                                    <?php echo $filtre_specialite===$sp?'selected':''; ?>>
                                <?php echo htmlspecialchars($sp); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:white;border-radius:8px;">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($recherche || $filtre_specialite !== 'toutes'): ?>
                    <a href="consultations.php" class="btn btn-sm btn-outline-secondary" style="border-radius:8px;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Consultation cards -->
        <?php if (count($consultations) > 0): ?>

            <?php foreach ($consultations as $c):
                $dateC = new DateTime($c['date_consultation']);
                // Fetch prescriptions for this consultation
                $prescStmt->execute([$c['id']]);
                $prescriptions = $prescStmt->fetchAll();

                // Doctor initials
                $parts   = explode(' ', $c['medecin_nom']);
                $initials = strtoupper(substr($parts[1]??'',0,1).substr($parts[2]??'',0,1));
            ?>
            <div class="consult-card">
                <!-- Header -->
                <div class="consult-header">
                    <div class="doctor">
                        <div class="avatar"><i class="fas fa-user-md"></i></div>
                        <div>
                            <h5>Dr. <?php echo htmlspecialchars($c['medecin_nom']); ?></h5>
                            <div class="specialty"><?php echo htmlspecialchars($c['specialite']); ?></div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-10 flex-wrap" style="gap:10px;">
                        <div class="date-badge">
                            <i class="fas fa-calendar"></i>
                            <?php echo $dateC->format('d/m/Y'); ?>
                        </div>
                        <span class="badge bg-<?php echo severityColor($c['statut']); ?> fs-6" style="font-size:12px!important;">
                            <?php echo $c['statut'] === 'termine' ? 'Completed' : 'In progress'; ?>
                        </span>
                    </div>
                </div>

                <!-- Body -->
                <div class="consult-body">
                    <!-- Meta -->
                    <div class="meta-row">
                        <div class="meta-item">
                            <i class="fas fa-<?php echo $c['type_consultation']==='teleconsultation'?'video':'hospital'; ?>"></i>
                            <?php echo $c['type_consultation']==='teleconsultation'?'Teleconsultation':'In-person'; ?>
                        </div>
                        <?php if (!empty($c['duree_consultation'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-hourglass-half"></i>
                            Duration: <?php echo $c['duree_consultation']; ?> min
                        </div>
                        <?php endif; ?>
                        <div class="meta-item">
                            <i class="fas fa-money-bill-wave"></i>
                            <?php echo number_format($c['tarif_consultation'],0,',',' '); ?> FCFA
                        </div>
                        <?php if (!empty($c['motif'])): ?>
                        <div class="meta-item">
                            <i class="fas fa-comment-medical"></i>
                            Reason: <?php echo htmlspecialchars(mb_strimwidth($c['motif'],0,60,'...')); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Diagnosis -->
                    <?php if (!empty($c['diagnostic'])): ?>
                    <div class="info-section">
                        <div class="info-section-title"><i class="fas fa-diagnoses"></i> Diagnosis</div>
                        <div class="info-section-content"><?php echo nl2br(htmlspecialchars($c['diagnostic'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Prescriptions -->
                    <?php if (count($prescriptions) > 0): ?>
                    <div class="info-section">
                        <div class="info-section-title"><i class="fas fa-pills"></i> Prescriptions (<?php echo count($prescriptions); ?>)</div>
                        <div style="overflow-x:auto;">
                            <table class="presc-table">
                                <thead>
                                    <tr>
                                        <th>Medication</th>
                                        <th>Dosage</th>
                                        <th>Frequency</th>
                                        <th>Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($prescriptions as $presc): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($presc['medicament']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($presc['dosage']); ?></td>
                                        <td><?php echo htmlspecialchars($presc['frequence']); ?></td>
                                        <td><?php echo htmlspecialchars($presc['duree']); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php elseif (!empty($c['prescription'])): ?>
                    <div class="info-section">
                        <div class="info-section-title"><i class="fas fa-prescription-bottle-alt"></i> Prescription</div>
                        <div class="info-section-content"><?php echo nl2br(htmlspecialchars($c['prescription'])); ?></div>
                    </div>
                    <?php endif; ?>

                    <!-- Recommendations & exams — collapsible -->
                    <?php if (!empty($c['recommandations']) || !empty($c['examens_demandes']) || !empty($c['notes_medicales'])): ?>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-outline-secondary" style="border-radius:8px;font-size:13px;"
                                data-bs-toggle="collapse" data-bs-target="#more<?php echo $c['id']; ?>">
                            <i class="fas fa-chevron-down"></i> More details
                        </button>
                        <div class="collapse mt-3" id="more<?php echo $c['id']; ?>">
                            <?php if (!empty($c['examens_demandes'])): ?>
                            <div class="info-section">
                                <div class="info-section-title"><i class="fas fa-microscope"></i> Requested Exams</div>
                                <div class="info-section-content"><?php echo nl2br(htmlspecialchars($c['examens_demandes'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($c['recommandations'])): ?>
                            <div class="info-section">
                                <div class="info-section-title"><i class="fas fa-lightbulb"></i> Recommendations</div>
                                <div class="info-section-content"><?php echo nl2br(htmlspecialchars($c['recommandations'])); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($c['notes_medicales'])): ?>
                            <div class="info-section">
                                <div class="info-section-title"><i class="fas fa-notes-medical"></i> Medical Notes</div>
                                <div class="info-section-content"><?php echo nl2br(htmlspecialchars($c['notes_medicales'])); ?></div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Footer actions -->
                    <div class="d-flex justify-content-end gap-2 mt-3 pt-3" style="border-top:1px solid #f0f0f0;">
                        <a href="rendez-vous.php?statut=termine" class="btn btn-sm btn-outline-primary" style="border-radius:8px;font-size:13px;">
                            <i class="fas fa-calendar-check"></i> View appointment
                        </a>
                        <?php if ($c['statut'] === 'termine'): ?>
                        <button class="btn btn-sm btn-outline-warning" style="border-radius:8px;font-size:13px;"
                                data-bs-toggle="modal" data-bs-target="#modalAvis<?php echo $c['id']; ?>">
                            <i class="fas fa-star"></i> Rate
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Rating modal -->
            <?php if ($c['statut'] === 'termine'): ?>
            <div class="modal fade" id="modalAvis<?php echo $c['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-star"></i> Rate Dr. <?php echo htmlspecialchars($c['medecin_nom']); ?></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST" action="avis.php">
                            <div class="modal-body p-4">
                                <input type="hidden" name="consultation_id" value="<?php echo $c['id']; ?>">
                                <input type="hidden" name="medecin_id"      value="<?php echo $c['medecin_id']; ?>">
                                <div class="mb-3 text-center">
                                    <label class="form-label fw-semibold">Your rating</label>
                                    <div class="star-rating d-flex justify-content-center gap-2 my-2" style="font-size:28px;">
                                        <?php for ($i=1;$i<=5;$i++): ?>
                                        <label style="cursor:pointer;color:#ffc107;">
                                            <input type="radio" name="note" value="<?php echo $i; ?>" style="display:none;" required>
                                            <i class="fas fa-star"></i>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Comment (optional)</label>
                                    <textarea name="commentaire" class="form-control" rows="3"
                                              style="border-radius:10px;border:2px solid #e0e0e0;"
                                              placeholder="Share your experience..."></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:white;border-radius:8px;">
                                    <i class="fas fa-paper-plane"></i> Submit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?php echo $page<=1?'disabled':''; ?>">
                        <a class="page-link" href="?specialite=<?php echo urlencode($filtre_specialite); ?>&recherche=<?php echo urlencode($recherche); ?>&page=<?php echo $page-1; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($p=1; $p<=$total_pages; $p++): ?>
                    <li class="page-item <?php echo $p===$page?'active':''; ?>">
                        <a class="page-link" href="?specialite=<?php echo urlencode($filtre_specialite); ?>&recherche=<?php echo urlencode($recherche); ?>&page=<?php echo $p; ?>">
                            <?php echo $p; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page>=$total_pages?'disabled':''; ?>">
                        <a class="page-link" href="?specialite=<?php echo urlencode($filtre_specialite); ?>&recherche=<?php echo urlencode($recherche); ?>&page=<?php echo $page+1; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <p class="text-center text-muted small">
                Showing <?php echo count($consultations); ?> of <?php echo $total_consultations; ?> consultation(s)
            </p>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-folder-open"></i>
                <h4>No consultations found</h4>
                <p>
                    <?php echo ($recherche || $filtre_specialite!=='toutes')
                        ? 'No consultations match your search criteria.'
                        : 'Your medical consultation history will appear here.'; ?>
                </p>
                <?php if ($recherche || $filtre_specialite!=='toutes'): ?>
                    <a href="consultations.php" class="btn btn-outline-secondary me-2" style="border-radius:10px;">
                        <i class="fas fa-times"></i> Clear filters
                    </a>
                <?php endif; ?>
                <a href="nouveau-rdv.php" class="btn" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:white;border-radius:10px;">
                    <i class="fas fa-calendar-plus"></i> Book an appointment
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Highlight active star on hover
document.querySelectorAll('.star-rating label').forEach(function(label, idx, all) {
    label.addEventListener('mouseenter', function() {
        all.forEach(function(l, i) {
            l.querySelector('i').style.color = i <= idx ? '#ffb700' : '#ccc';
        });
    });
    label.addEventListener('mouseleave', function() {
        all.forEach(function(l) { l.querySelector('i').style.color = '#ffc107'; });
    });
    label.addEventListener('click', function() {
        label.querySelector('input').checked = true;
    });
});
</script>
</body>
</html>