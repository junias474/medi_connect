<?php
require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$error = '';
$success = '';

try {
    $db = Database::getInstance()->getConnection();

    /* ── Delete symptom ── */
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $del = $db->prepare("DELETE FROM symptomes WHERE id = ? AND patient_id = ?");
        $del->execute([$_GET['delete'], $patient_id]);
        $success = "Symptom deleted successfully.";
    }

    /* ── Update status ── */
    if (isset($_GET['status']) && isset($_GET['id']) && is_numeric($_GET['id'])) {
        $allowed = ['nouveau','en_cours','traite'];
        $newStatus = $_GET['status'];
        if (in_array($newStatus, $allowed)) {
            $upd = $db->prepare("UPDATE symptomes SET statut = ? WHERE id = ? AND patient_id = ?");
            $upd->execute([$newStatus, $_GET['id'], $patient_id]);
            $success = "Status updated successfully.";
        }
    }

    /* ── Add symptom ── */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $description  = sanitize($_POST['description'] ?? '');
        $severite     = sanitize($_POST['severite'] ?? '');
        $date_debut   = sanitize($_POST['date_debut'] ?? '');
        $temperature  = $_POST['temperature'] ?? null;
        $autres       = sanitize($_POST['autres_details'] ?? '');

        if (!$description || !$severite || !$date_debut) {
            $error = "Please fill in all required fields.";
        } else {
            $stmt = $db->prepare("
                INSERT INTO symptomes (patient_id, description, severite, date_debut, temperature, autres_details, statut)
                VALUES (?, ?, ?, ?, ?, ?, 'nouveau')
            ");
            $stmt->execute([
                $patient_id, $description, $severite, $date_debut,
                ($temperature !== '' && $temperature !== null) ? floatval($temperature) : null,
                $autres
            ]);
            $success = "Symptom added successfully.";
        }
    }

    /* ── Load symptoms ── */
    $filter = sanitize($_GET['filter'] ?? '');
    $whereClause = "WHERE patient_id = ?";
    $params = [$patient_id];
    if (in_array($filter, ['nouveau','en_cours','traite'])) {
        $whereClause .= " AND statut = ?";
        $params[] = $filter;
    }

    $stmt = $db->prepare("SELECT * FROM symptomes $whereClause ORDER BY created_at DESC");
    $stmt->execute($params);
    $symptomes = $stmt->fetchAll();

    /* ── Stats ── */
    $stats = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(statut='nouveau') as nouveaux,
            SUM(statut='en_cours') as en_cours,
            SUM(statut='traite') as traites,
            SUM(severite='urgent') as urgents
        FROM symptomes WHERE patient_id = ?
    ");
    $stats->execute([$patient_id]);
    $stats = $stats->fetch();

} catch (PDOException $e) {
    $error = "An error occurred. Please try again.";
    error_log("symptomes.php error: " . $e->getMessage());
}

$severityColors = ['leger'=>'success','moyen'=>'warning','grave'=>'danger','urgent'=>'dark'];
$severityLabels = ['leger'=>'Mild','moyen'=>'Moderate','grave'=>'Severe','urgent'=>'Urgent'];
$statusColors   = ['nouveau'=>'primary','en_cours'=>'warning','traite'=>'success'];
$statusLabels   = ['nouveau'=>'New','en_cours'=>'Ongoing','traite'=>'Treated'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Symptoms - <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:260px;}
body{font-family:'Segoe UI',sans-serif;background:#f5f7fa;}
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);
  background:linear-gradient(180deg,var(--primary),var(--secondary));color:#fff;z-index:1000;box-shadow:2px 0 10px rgba(0,0,0,.1);}
.sidebar-header{padding:30px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.1);}
.sidebar-header h4{margin:10px 0 5px;font-size:18px;}
.sidebar-header p{margin:0;font-size:13px;opacity:.8;}
.sidebar-menu{padding:20px 0;}
.sidebar-menu a{display:flex;align-items:center;padding:15px 25px;color:#fff;text-decoration:none;transition:.3s;border-left:4px solid transparent;}
.sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.1);border-left-color:#fff;}
.sidebar-menu a i{width:30px;font-size:18px;}
.sidebar-footer{position:absolute;bottom:0;width:100%;padding:20px;border-top:1px solid rgba(255,255,255,.1);}
.main-content{margin-left:var(--sidebar-w);}
.top-navbar{background:#fff;padding:20px 30px;box-shadow:0 2px 4px rgba(0,0,0,.05);display:flex;justify-content:space-between;align-items:center;}
.top-navbar h2{margin:0;font-size:24px;color:#333;}
.content-area{padding:30px;}
.card{border:none;border-radius:15px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:24px;}
.card-header{background:#fff;border-bottom:1px solid #f0f0f0;padding:20px 25px;font-weight:600;color:#333;border-radius:15px 15px 0 0 !important;}
.card-body{padding:25px;}
.form-label{font-weight:600;color:#444;margin-bottom:6px;}
.form-control,.form-select{border-radius:10px;padding:11px 14px;border:2px solid #e0e0e0;transition:.3s;}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(102,126,234,.2);}
.btn-submit{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;border-radius:10px;
  padding:12px 24px;font-weight:600;color:#fff;transition:.2s;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(102,126,234,.4);color:#fff;}
.symptom-card{background:#fff;border-radius:12px;padding:20px;margin-bottom:14px;
  box-shadow:0 2px 8px rgba(0,0,0,.05);border-left:5px solid #e0e0e0;transition:.3s;}
.symptom-card:hover{transform:translateX(4px);}
.symptom-card.urgent{border-left-color:#343a40;}
.symptom-card.grave{border-left-color:#dc3545;}
.symptom-card.moyen{border-left-color:#ffc107;}
.symptom-card.leger{border-left-color:#28a745;}
.stat-card{background:#fff;border-radius:15px;padding:22px;text-align:center;
  box-shadow:0 2px 8px rgba(0,0,0,.06);transition:.3s;}
.stat-card:hover{transform:translateY(-4px);}
.stat-card h3{font-size:30px;font-weight:700;margin:8px 0 4px;}
.stat-card p{margin:0;font-size:13px;color:#666;}
.filter-btn{padding:6px 18px;border-radius:20px;border:2px solid #e0e0e0;background:#fff;
  cursor:pointer;transition:.3s;font-size:14px;font-weight:500;}
.filter-btn:hover,.filter-btn.active{background:linear-gradient(135deg,var(--primary),var(--secondary));
  border-color:var(--primary);color:#fff;}
</style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
  <div class="sidebar-header">
    <i class="fas fa-user-circle" style="font-size:60px"></i>
    <h4><?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h4>
    <p><i class="fas fa-circle" style="font-size:8px;color:#4ade80"></i> Patient</p>
  </div>
  <div class="sidebar-menu">
    <a href="index.php"><i class="fas fa-home"></i><span>Home</span></a>
    <a href="rendez-vous.php"><i class="fas fa-calendar-check"></i><span>My Appointments</span></a>
    <a href="nouveau-rdv.php"><i class="fas fa-calendar-plus"></i><span>New Appointment</span></a>
    <a href="consultations.php"><i class="fas fa-stethoscope"></i><span>My Consultations</span></a>
    <a href="symptomes.php" class="active"><i class="fas fa-notes-medical"></i><span>My Symptoms</span></a>
    <a href="documents.php"><i class="fas fa-file-medical"></i><span>Medical Documents</span></a>
    <a href="medecins.php"><i class="fas fa-user-md"></i><span>Find a Doctor</span></a>
    <a href="profil.php"><i class="fas fa-user-cog"></i><span>My Profile</span></a>
  </div>
  <div class="sidebar-footer">
    <a href="../../auth/logout.php" style="color:#fff;text-decoration:none;display:flex;align-items:center;gap:10px;">
      <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="top-navbar">
    <h2><i class="fas fa-notes-medical" style="color:var(--primary)"></i> My Symptoms</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="content-area">

    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" style="border-radius:12px">
      <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show" style="border-radius:12px">
      <i class="fas fa-check-circle"></i> <?php echo $success; ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div style="font-size:28px;color:var(--primary)"><i class="fas fa-list-ul"></i></div>
          <h3><?php echo $stats['total']; ?></h3>
          <p>Total Symptoms</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div style="font-size:28px;color:#0d6efd"><i class="fas fa-exclamation-circle"></i></div>
          <h3><?php echo $stats['nouveaux']; ?></h3>
          <p>New</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div style="font-size:28px;color:#ffc107"><i class="fas fa-hourglass-half"></i></div>
          <h3><?php echo $stats['en_cours']; ?></h3>
          <p>Ongoing</p>
        </div>
      </div>
      <div class="col-6 col-md-3">
        <div class="stat-card">
          <div style="font-size:28px;color:#28a745"><i class="fas fa-check-circle"></i></div>
          <h3><?php echo $stats['traites']; ?></h3>
          <p>Treated</p>
        </div>
      </div>
    </div>

    <?php if ($stats['urgents'] > 0): ?>
    <div class="alert alert-danger" style="border-radius:12px">
      <i class="fas fa-exclamation-triangle"></i>
      <strong>Attention!</strong> You have <?php echo $stats['urgents']; ?> urgent symptom(s).
      Please consult a doctor as soon as possible.
      <a href="nouveau-rdv.php" class="btn btn-danger btn-sm ms-2">Book Appointment Now</a>
    </div>
    <?php endif; ?>

    <div class="row">
      <!-- Add symptom form -->
      <div class="col-lg-4">
        <div class="card" style="position:sticky;top:20px">
          <div class="card-header">
            <i class="fas fa-plus-circle" style="color:var(--primary)"></i> Log a Symptom
          </div>
          <div class="card-body">
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Description <span class="text-danger">*</span></label>
                <textarea name="description" class="form-control" rows="3"
                  placeholder="Describe what you are experiencing..."
                  required><?php echo $_POST['description'] ?? ''; ?></textarea>
              </div>
              <div class="mb-3">
                <label class="form-label">Severity <span class="text-danger">*</span></label>
                <select name="severite" class="form-select" required>
                  <option value="">-- Select --</option>
                  <option value="leger" <?php echo (($_POST['severite']??'')=='leger')?'selected':''; ?>>🟢 Mild</option>
                  <option value="moyen" <?php echo (($_POST['severite']??'')=='moyen')?'selected':''; ?>>🟡 Moderate</option>
                  <option value="grave" <?php echo (($_POST['severite']??'')=='grave')?'selected':''; ?>>🔴 Severe</option>
                  <option value="urgent" <?php echo (($_POST['severite']??'')=='urgent')?'selected':''; ?>>⚫ Urgent</option>
                </select>
              </div>
              <div class="mb-3">
                <label class="form-label">Onset Date <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" class="form-control" required
                  max="<?php echo date('Y-m-d'); ?>"
                  value="<?php echo $_POST['date_debut'] ?? date('Y-m-d'); ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Temperature (°C)</label>
                <input type="number" name="temperature" class="form-control"
                  step="0.1" min="35" max="42" placeholder="e.g. 38.5"
                  value="<?php echo $_POST['temperature'] ?? ''; ?>">
              </div>
              <div class="mb-3">
                <label class="form-label">Other Details</label>
                <textarea name="autres_details" class="form-control" rows="2"
                  placeholder="Additional information..."><?php echo $_POST['autres_details'] ?? ''; ?></textarea>
              </div>
              <button type="submit" class="btn btn-submit w-100">
                <i class="fas fa-save"></i> Save Symptom
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Symptoms list -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fas fa-list" style="color:var(--primary)"></i> Symptom History</span>
            <div class="d-flex gap-2 flex-wrap">
              <a href="symptomes.php" class="filter-btn <?php echo !$filter?'active':''; ?>">All</a>
              <a href="?filter=nouveau" class="filter-btn <?php echo $filter=='nouveau'?'active':''; ?>">New</a>
              <a href="?filter=en_cours" class="filter-btn <?php echo $filter=='en_cours'?'active':''; ?>">Ongoing</a>
              <a href="?filter=traite" class="filter-btn <?php echo $filter=='traite'?'active':''; ?>">Treated</a>
            </div>
          </div>
          <div class="card-body">
            <?php if (empty($symptomes)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fas fa-notes-medical fa-3x mb-3" style="opacity:.3"></i>
              <p>No symptoms recorded<?php echo $filter ? ' with this filter' : ''; ?>.</p>
              <?php if (!$filter): ?>
              <small>Use the form on the left to log a symptom.</small>
              <?php endif; ?>
            </div>
            <?php else: ?>
            <?php foreach ($symptomes as $s):
              $sev = $s['severite'];
              $dateOnset = new DateTime($s['date_debut']);
              $dateAdded = new DateTime($s['created_at']);
            ?>
            <div class="symptom-card <?php echo $sev; ?>">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div class="flex-grow-1">
                  <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-<?php echo $severityColors[$sev]; ?>">
                      <?php echo $severityLabels[$sev]; ?>
                    </span>
                    <span class="badge bg-<?php echo $statusColors[$s['statut']]; ?>">
                      <?php echo $statusLabels[$s['statut']]; ?>
                    </span>
                    <?php if ($s['temperature']): ?>
                    <span class="badge bg-info text-dark">🌡 <?php echo $s['temperature']; ?>°C</span>
                    <?php endif; ?>
                  </div>
                  <p style="margin:4px 0;font-size:15px"><?php echo htmlspecialchars($s['description']); ?></p>
                  <?php if ($s['autres_details']): ?>
                  <p style="margin:0;font-size:13px;color:#666;font-style:italic">
                    <?php echo htmlspecialchars($s['autres_details']); ?>
                  </p>
                  <?php endif; ?>
                  <div style="font-size:12px;color:#999;margin-top:6px">
                    <i class="fas fa-calendar-day"></i> Onset: <?php echo $dateOnset->format('m/d/Y'); ?> &nbsp;
                    <i class="fas fa-clock"></i> Added: <?php echo $dateAdded->format('m/d/Y H:i'); ?>
                  </div>
                </div>
                <div class="d-flex flex-column gap-1 align-items-end">
                  <!-- Status change -->
                  <div class="dropdown">
                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                      <i class="fas fa-sync-alt"></i> Status
                    </button>
                    <ul class="dropdown-menu">
                      <li><a class="dropdown-item" href="?status=nouveau&id=<?php echo $s['id']; ?>">
                        🔵 New</a></li>
                      <li><a class="dropdown-item" href="?status=en_cours&id=<?php echo $s['id']; ?>">
                        🟡 Ongoing</a></li>
                      <li><a class="dropdown-item" href="?status=traite&id=<?php echo $s['id']; ?>">
                        🟢 Treated</a></li>
                    </ul>
                  </div>
                  <a href="nouveau-rdv.php" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-calendar-plus"></i> Book Appt
                  </a>
                  <a href="?delete=<?php echo $s['id']; ?>"
                     class="btn btn-sm btn-outline-danger"
                     onclick="return confirm('Delete this symptom?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>