<?php
/**
 * Patient Appointments Page
 * Medical Consultation Application
 */

require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../auth/login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$user_id    = $_SESSION['user_id'];

// --- Handle cancellation ---
$action_message = '';
$action_type    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_rdv'])) {
    $rdv_id = (int)($_POST['rdv_id'] ?? 0);
    try {
        $db = Database::getInstance()->getConnection();
        // Make sure the appointment belongs to this patient and can be cancelled
        $check = $db->prepare("
            SELECT id FROM rendez_vous
            WHERE id = ? AND patient_id = ? AND statut IN ('en_attente','confirme')
        ");
        $check->execute([$rdv_id, $patient_id]);
        if ($check->fetch()) {
            $upd = $db->prepare("UPDATE rendez_vous SET statut = 'annule' WHERE id = ?");
            $upd->execute([$rdv_id]);
            $action_message = "Appointment successfully cancelled.";
            $action_type    = 'success';
        } else {
            $action_message = "This appointment cannot be cancelled.";
            $action_type    = 'danger';
        }
    } catch (PDOException $e) {
        $action_message = "An error occurred. Please try again.";
        $action_type    = 'danger';
        error_log("Cancel RDV error: " . $e->getMessage());
    }
}

// --- Filters ---
$filtre_statut = $_GET['statut'] ?? 'tous';
$filtre_type   = $_GET['type']   ?? 'tous';
$page          = max(1, (int)($_GET['page'] ?? 1));
$per_page      = 8;
$offset        = ($page - 1) * $per_page;

try {
    $db = Database::getInstance()->getConnection();

    // Build WHERE clause
    $where   = "rv.patient_id = ?";
    $params  = [$patient_id];

    if ($filtre_statut !== 'tous') {
        $where  .= " AND rv.statut = ?";
        $params[] = $filtre_statut;
    }
    if ($filtre_type !== 'tous') {
        $where  .= " AND rv.type_consultation = ?";
        $params[] = $filtre_type;
    }

    // Total count
    $countStmt = $db->prepare("
        SELECT COUNT(*) as total
        FROM rendez_vous rv
        WHERE $where
    ");
    $countStmt->execute($params);
    $total_rdv  = $countStmt->fetch()['total'];
    $total_pages = ceil($total_rdv / $per_page);

    // Fetch appointments
    $paramsPage   = array_merge($params, [$per_page, $offset]);
    $stmt = $db->prepare("
        SELECT rv.*,
               CONCAT(u.nom, ' ', u.prenom) AS medecin_nom,
               m.specialite,
               m.tarif_consultation,
               u.telephone AS medecin_telephone
        FROM rendez_vous rv
        INNER JOIN medecins m   ON rv.medecin_id    = m.id
        INNER JOIN utilisateurs u ON m.utilisateur_id = u.id
        WHERE $where
        ORDER BY rv.date_rendez_vous DESC, rv.heure_debut DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute($paramsPage);
    $rendez_vous = $stmt->fetchAll();

    // Unread notifications
    $notifStmt = $db->prepare("SELECT COUNT(*) as total FROM notifications WHERE utilisateur_id = ? AND lu = 0");
    $notifStmt->execute([$user_id]);
    $notif_non_lues = $notifStmt->fetch();

} catch (PDOException $e) {
    error_log("RDV page error: " . $e->getMessage());
    $rendez_vous    = [];
    $total_rdv      = 0;
    $total_pages    = 1;
    $notif_non_lues = ['total' => 0];
}

// Helper
$months_en = ['','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];

function statusBadge(string $statut): string {
    $map = [
        'en_attente'     => ['warning', 'clock',        'Pending'],
        'confirme'       => ['success', 'check-circle',  'Confirmed'],
        'annule'         => ['danger',  'x-circle',      'Cancelled'],
        'termine'        => ['secondary','check-square', 'Completed'],
        'patient_absent' => ['dark',    'person-x',      'Absent'],
    ];
    [$color, $icon, $label] = $map[$statut] ?? ['secondary','question','Unknown'];
    return "<span class=\"badge bg-{$color}\"><i class=\"fas fa-{$icon}\"></i> {$label}</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - <?php echo SITE_NAME; ?></title>
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

        /* ---- Filter bar ---- */
        .filter-bar {
            background: white; border-radius: 15px;
            padding: 20px 25px; margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            display: flex; align-items: center; gap: 15px; flex-wrap: wrap;
        }
        .filter-bar label { font-weight: 600; color: #555; margin-bottom: 0; font-size: 14px; }
        .filter-bar select {
            border-radius: 8px; border: 2px solid #e0e0e0; padding: 6px 12px;
            font-size: 14px; transition: border-color .3s;
        }
        .filter-bar select:focus { border-color: var(--primary-color); outline: none; }

        /* ---- Appointment card ---- */
        .rdv-card {
            background: white; border-radius: 15px;
            padding: 0; margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform .3s, box-shadow .3s;
            overflow: hidden;
            border-left: 5px solid #e0e0e0;
        }
        .rdv-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.1); }
        .rdv-card.statut-confirme    { border-left-color: #198754; }
        .rdv-card.statut-en_attente  { border-left-color: #ffc107; }
        .rdv-card.statut-annule      { border-left-color: #dc3545; }
        .rdv-card.statut-termine     { border-left-color: #6c757d; }
        .rdv-card.statut-patient_absent { border-left-color: #212529; }

        .rdv-card-body { display: flex; align-items: center; padding: 20px 25px; gap: 20px; flex-wrap: wrap; }

        .rdv-date-box {
            min-width: 75px; text-align: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white; border-radius: 12px; padding: 12px;
            flex-shrink: 0;
        }
        .rdv-date-box .day   { font-size: 30px; font-weight: 700; line-height: 1; }
        .rdv-date-box .month { font-size: 12px; text-transform: uppercase; opacity: .9; }
        .rdv-date-box .year  { font-size: 11px; opacity: .8; }

        .rdv-info { flex: 1; min-width: 200px; }
        .rdv-info h5 { margin: 0 0 4px; font-size: 16px; color: #333; }
        .rdv-info .specialty { font-size: 13px; color: var(--primary-color); font-weight: 600; margin-bottom: 6px; }
        .rdv-info .meta { font-size: 13px; color: #666; display: flex; gap: 15px; flex-wrap: wrap; }
        .rdv-info .meta i { color: var(--primary-color); }

        .rdv-actions { display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }

        /* ---- Empty state ---- */
        .empty-state { text-align: center; padding: 60px 20px; }
        .empty-state i { font-size: 64px; color: #d0d7de; margin-bottom: 20px; }
        .empty-state h4 { color: #555; margin-bottom: 10px; }
        .empty-state p  { color: #888; margin-bottom: 20px; }

        /* ---- Pagination ---- */
        .pagination .page-link {
            border-radius: 8px !important; margin: 0 3px;
            color: var(--primary-color); border-color: #e0e0e0;
        }
        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-color: transparent; color: white;
        }

        /* ---- Summary cards ---- */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .summary-card {
            background: white; border-radius: 12px; padding: 18px 15px;
            text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform .2s;
        }
        .summary-card:hover { transform: translateY(-3px); }
        .summary-card .num { font-size: 28px; font-weight: 700; }
        .summary-card .lbl { font-size: 12px; color: #888; margin-top: 4px; }
        .num-total    { color: var(--primary-color); }
        .num-upcoming { color: #198754; }
        .num-pending  { color: #ffc107; }
        .num-done     { color: #6c757d; }
        .num-cancelled{ color: #dc3545; }

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
        <a href="rendez-vous.php" class="active"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
        <a href="nouveau-rdv.php"><i class="fas fa-calendar-plus"></i><span>New Appointment</span></a>
        <a href="consultations.php"><i class="fas fa-stethoscope"></i><span>My Consultations</span></a>
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
        <h2><i class="fas fa-calendar-check" style="color:var(--primary-color);"></i> My Appointments</h2>
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

        <?php if ($action_message): ?>
            <div class="alert alert-<?php echo $action_type; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $action_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($action_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Summary cards -->
        <?php
        try {
            $db2 = Database::getInstance()->getConnection();
            $s = $db2->prepare("
                SELECT
                    COUNT(*) AS total,
                    SUM(statut IN ('en_attente','confirme') AND date_rendez_vous >= CURDATE()) AS upcoming,
                    SUM(statut = 'en_attente') AS pending,
                    SUM(statut = 'termine') AS done,
                    SUM(statut = 'annule') AS cancelled
                FROM rendez_vous WHERE patient_id = ?
            ");
            $s->execute([$patient_id]);
            $summary = $s->fetch();
        } catch (PDOException $e) { $summary = ['total'=>0,'upcoming'=>0,'pending'=>0,'done'=>0,'cancelled'=>0]; }
        ?>
        <div class="summary-grid">
            <div class="summary-card">
                <div class="num num-total"><?php echo $summary['total']; ?></div>
                <div class="lbl">Total</div>
            </div>
            <div class="summary-card">
                <div class="num num-upcoming"><?php echo $summary['upcoming']; ?></div>
                <div class="lbl">Upcoming</div>
            </div>
            <div class="summary-card">
                <div class="num num-pending"><?php echo $summary['pending']; ?></div>
                <div class="lbl">Pending</div>
            </div>
            <div class="summary-card">
                <div class="num num-done"><?php echo $summary['done']; ?></div>
                <div class="lbl">Completed</div>
            </div>
            <div class="summary-card">
                <div class="num num-cancelled"><?php echo $summary['cancelled']; ?></div>
                <div class="lbl">Cancelled</div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="filter-bar">
            <label><i class="fas fa-filter"></i> Filter:</label>
            <form method="GET" action="" class="d-flex align-items-center gap-3 flex-wrap">
                <div class="d-flex align-items-center gap-2">
                    <label for="statut" class="mb-0">Status</label>
                    <select name="statut" id="statut" onchange="this.form.submit()">
                        <option value="tous"          <?php echo $filtre_statut==='tous'           ?'selected':'';?>>All</option>
                        <option value="en_attente"    <?php echo $filtre_statut==='en_attente'    ?'selected':'';?>>Pending</option>
                        <option value="confirme"      <?php echo $filtre_statut==='confirme'      ?'selected':'';?>>Confirmed</option>
                        <option value="termine"       <?php echo $filtre_statut==='termine'       ?'selected':'';?>>Completed</option>
                        <option value="annule"        <?php echo $filtre_statut==='annule'        ?'selected':'';?>>Cancelled</option>
                        <option value="patient_absent"<?php echo $filtre_statut==='patient_absent'?'selected':'';?>>Absent</option>
                    </select>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label for="type" class="mb-0">Type</label>
                    <select name="type" id="type" onchange="this.form.submit()">
                        <option value="tous"            <?php echo $filtre_type==='tous'           ?'selected':'';?>>All</option>
                        <option value="teleconsultation"<?php echo $filtre_type==='teleconsultation'?'selected':'';?>>Teleconsultation</option>
                        <option value="presentiel"      <?php echo $filtre_type==='presentiel'    ?'selected':'';?>>In-person</option>
                    </select>
                </div>
            </form>
            <div class="ms-auto">
                <a href="nouveau-rdv.php" class="btn btn-sm" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:white;border-radius:8px;">
                    <i class="fas fa-plus"></i> New Appointment
                </a>
            </div>
        </div>

        <!-- Appointment list -->
        <?php if (count($rendez_vous) > 0): ?>
            <?php foreach ($rendez_vous as $rdv):
                $date   = new DateTime($rdv['date_rendez_vous']);
                $canCancel = in_array($rdv['statut'], ['en_attente','confirme'])
                             && $rdv['date_rendez_vous'] >= date('Y-m-d');
            ?>
            <div class="rdv-card statut-<?php echo $rdv['statut']; ?>">
                <div class="rdv-card-body">
                    <!-- Date box -->
                    <div class="rdv-date-box">
                        <div class="day"><?php echo $date->format('d'); ?></div>
                        <div class="month"><?php echo $months_en[(int)$date->format('n')]; ?></div>
                        <div class="year"><?php echo $date->format('Y'); ?></div>
                    </div>

                    <!-- Info -->
                    <div class="rdv-info">
                        <h5>Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                        <div class="specialty"><?php echo htmlspecialchars($rdv['specialite']); ?></div>
                        <div class="meta">
                            <span><i class="fas fa-clock"></i> <?php echo substr($rdv['heure_debut'],0,5); ?> – <?php echo substr($rdv['heure_fin'],0,5); ?></span>
                            <span><i class="fas fa-<?php echo $rdv['type_consultation']==='teleconsultation'?'video':'hospital'; ?>"></i>
                                <?php echo $rdv['type_consultation']==='teleconsultation'?'Teleconsultation':'In-person'; ?>
                            </span>
                            <span><i class="fas fa-money-bill-wave"></i> <?php echo number_format($rdv['tarif_consultation'],0,',',' '); ?> FCFA</span>
                        </div>
                        <?php if (!empty($rdv['motif'])): ?>
                            <div class="mt-1" style="font-size:13px;color:#888;">
                                <i class="fas fa-comment-medical"></i>
                                <?php echo htmlspecialchars(mb_strimwidth($rdv['motif'],0,80,'...')); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Actions -->
                    <div class="rdv-actions">
                        <?php echo statusBadge($rdv['statut']); ?>

                        <button class="btn btn-sm btn-outline-primary"
                                style="border-radius:8px;font-size:12px;"
                                data-bs-toggle="modal"
                                data-bs-target="#modalRdv<?php echo $rdv['id']; ?>">
                            <i class="fas fa-eye"></i> Details
                        </button>

                        <?php if ($canCancel): ?>
                            <button class="btn btn-sm btn-outline-danger"
                                    style="border-radius:8px;font-size:12px;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalCancel<?php echo $rdv['id']; ?>">
                                <i class="fas fa-times"></i> Cancel
                            </button>
                        <?php endif; ?>

                        <?php if ($rdv['statut'] === 'confirme' && $rdv['type_consultation'] === 'teleconsultation'): ?>
                            <a href="teleconsultation.php?rdv=<?php echo $rdv['id']; ?>"
                               class="btn btn-sm btn-success" style="border-radius:8px;font-size:12px;">
                                <i class="fas fa-video"></i> Join
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Detail modal -->
            <div class="modal fade" id="modalRdv<?php echo $rdv['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-calendar-check"></i> Appointment Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="text-muted small">Doctor</div>
                                    <div class="fw-semibold">Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Specialty</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($rdv['specialite']); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Date</div>
                                    <div class="fw-semibold"><?php echo $date->format('d/m/Y'); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Time</div>
                                    <div class="fw-semibold"><?php echo substr($rdv['heure_debut'],0,5); ?> – <?php echo substr($rdv['heure_fin'],0,5); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Type</div>
                                    <div class="fw-semibold"><?php echo $rdv['type_consultation']==='teleconsultation'?'Teleconsultation':'In-person'; ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Status</div>
                                    <div><?php echo statusBadge($rdv['statut']); ?></div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Fee</div>
                                    <div class="fw-semibold"><?php echo number_format($rdv['tarif_consultation'],0,',',' '); ?> FCFA</div>
                                </div>
                                <div class="col-6">
                                    <div class="text-muted small">Doctor phone</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($rdv['medecin_telephone']); ?></div>
                                </div>
                                <?php if (!empty($rdv['motif'])): ?>
                                <div class="col-12">
                                    <div class="text-muted small">Reason</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($rdv['motif']); ?></div>
                                </div>
                                <?php endif; ?>
                                <?php if (!empty($rdv['notes_patient'])): ?>
                                <div class="col-12">
                                    <div class="text-muted small">Notes</div>
                                    <div class="fw-semibold"><?php echo htmlspecialchars($rdv['notes_patient']); ?></div>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cancel confirm modal -->
            <?php if ($canCancel): ?>
            <div class="modal fade" id="modalCancel<?php echo $rdv['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered modal-sm">
                    <div class="modal-content">
                        <div class="modal-header" style="background:linear-gradient(135deg,#dc3545,#c82333);">
                            <h5 class="modal-title text-white"><i class="fas fa-exclamation-triangle"></i> Confirm Cancellation</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center py-4">
                            <p class="mb-1">Cancel appointment with</p>
                            <p class="fw-bold">Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></p>
                            <p class="text-muted small">on <?php echo $date->format('d/m/Y'); ?> at <?php echo substr($rdv['heure_debut'],0,5); ?>?</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Keep it</button>
                            <form method="POST" action="" class="d-inline">
                                <input type="hidden" name="rdv_id" value="<?php echo $rdv['id']; ?>">
                                <button type="submit" name="annuler_rdv" class="btn btn-danger">
                                    <i class="fas fa-times"></i> Yes, cancel
                                </button>
                            </form>
                        </div>
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
                        <a class="page-link" href="?statut=<?php echo urlencode($filtre_statut); ?>&type=<?php echo urlencode($filtre_type); ?>&page=<?php echo $page-1; ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                    <li class="page-item <?php echo $p===$page?'active':''; ?>">
                        <a class="page-link" href="?statut=<?php echo urlencode($filtre_statut); ?>&type=<?php echo urlencode($filtre_type); ?>&page=<?php echo $p; ?>">
                            <?php echo $p; ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    <li class="page-item <?php echo $page>=$total_pages?'disabled':''; ?>">
                        <a class="page-link" href="?statut=<?php echo urlencode($filtre_statut); ?>&type=<?php echo urlencode($filtre_type); ?>&page=<?php echo $page+1; ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                </ul>
            </nav>
            <p class="text-center text-muted small">
                Showing <?php echo count($rendez_vous); ?> of <?php echo $total_rdv; ?> appointment(s)
            </p>
            <?php endif; ?>

        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h4>No appointments found</h4>
                <p><?php echo ($filtre_statut!=='tous'||$filtre_type!=='tous') ? 'No appointments match your filters.' : 'You have no appointments yet.'; ?></p>
                <?php if ($filtre_statut!=='tous'||$filtre_type!=='tous'): ?>
                    <a href="rendez-vous.php" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-times"></i> Clear filters
                    </a>
                <?php endif; ?>
                <a href="nouveau-rdv.php" class="btn" style="background:linear-gradient(135deg,var(--primary-color),var(--secondary-color));color:white;border-radius:10px;">
                    <i class="fas fa-plus"></i> Book an appointment
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>