<?php
require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$error = '';
$success = '';

/* Upload directory */
define('UPLOAD_DIR', '../../uploads/documents/');
define('MAX_SIZE', 10 * 1024 * 1024); // 10 MB
$allowedTypes = ['application/pdf','image/jpeg','image/png','image/jpg'];
$allowedExt   = ['pdf','jpg','jpeg','png'];

try {
    $db = Database::getInstance()->getConnection();

    /* ── Delete document ── */
    if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
        $sel = $db->prepare("SELECT chemin_fichier FROM documents_medicaux WHERE id = ? AND patient_id = ?");
        $sel->execute([$_GET['delete'], $patient_id]);
        $doc = $sel->fetch();
        if ($doc) {
            if (file_exists($doc['chemin_fichier'])) @unlink($doc['chemin_fichier']);
            $db->prepare("DELETE FROM documents_medicaux WHERE id = ? AND patient_id = ?")->execute([$_GET['delete'], $patient_id]);
            $success = "Document deleted successfully.";
        }
    }

    /* ── Upload document ── */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fichier'])) {
        $type_doc     = sanitize($_POST['type_document'] ?? '');
        $description  = sanitize($_POST['description'] ?? '');
        $consult_id   = !empty($_POST['consultation_id']) ? intval($_POST['consultation_id']) : null;
        $file         = $_FILES['fichier'];

        if (!$type_doc) {
            $error = "Please select a document type.";
        } elseif ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "Upload error. Please try again.";
        } elseif ($file['size'] > MAX_SIZE) {
            $error = "File too large. Maximum size: 10 MB.";
        } else {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExt)) {
                $error = "File type not allowed. Accepted: PDF, JPG, PNG.";
            } else {
                if (!is_dir(UPLOAD_DIR)) @mkdir(UPLOAD_DIR, 0755, true);
                $filename = 'doc_'.$patient_id.'_'.time().'_'.uniqid().'.'.$ext;
                $filepath = UPLOAD_DIR . $filename;

                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    $stmt = $db->prepare("
                        INSERT INTO documents_medicaux
                            (patient_id, consultation_id, type_document, nom_fichier, chemin_fichier, taille_fichier, description)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([
                        $patient_id, $consult_id, $type_doc,
                        htmlspecialchars($file['name']), $filepath,
                        $file['size'], $description
                    ]);
                    $success = "Document uploaded successfully!";
                } else {
                    $error = "Failed to save the file. Please check server permissions.";
                }
            }
        }
    }

    /* ── Load documents ── */
    $filterType = sanitize($_GET['type'] ?? '');
    $whereSQL = "WHERE d.patient_id = ?";
    $params = [$patient_id];
    if (in_array($filterType, ['ordonnance','resultat_examen','rapport','autre'])) {
        $whereSQL .= " AND d.type_document = ?";
        $params[] = $filterType;
    }

    $stmt = $db->prepare("
        SELECT d.*, c.date_consultation,
               CONCAT(u.prenom,' ',u.nom) as medecin_nom
        FROM documents_medicaux d
        LEFT JOIN consultations c ON d.consultation_id = c.id
        LEFT JOIN medecins m ON c.medecin_id = m.id
        LEFT JOIN utilisateurs u ON m.utilisateur_id = u.id
        $whereSQL
        ORDER BY d.uploaded_at DESC
    ");
    $stmt->execute($params);
    $documents = $stmt->fetchAll();

    /* ── Stats ── */
    $statsStmt = $db->prepare("
        SELECT
            COUNT(*) as total,
            SUM(type_document='ordonnance') as ordonnances,
            SUM(type_document='resultat_examen') as resultats,
            SUM(type_document='rapport') as rapports,
            SUM(type_document='autre') as autres,
            COALESCE(SUM(taille_fichier),0) as total_size
        FROM documents_medicaux WHERE patient_id = ?
    ");
    $statsStmt->execute([$patient_id]);
    $stats = $statsStmt->fetch();

    /* ── Consultations for dropdown ── */
    $cStmt = $db->prepare("
        SELECT c.id, c.date_consultation, CONCAT(u.prenom,' ',u.nom) as medecin_nom
        FROM consultations c
        JOIN medecins m ON c.medecin_id = m.id
        JOIN utilisateurs u ON m.utilisateur_id = u.id
        WHERE c.patient_id = ? ORDER BY c.date_consultation DESC
    ");
    $cStmt->execute([$patient_id]);
    $consultations = $cStmt->fetchAll();

} catch (PDOException $e) {
    $error = "An error occurred. Please try again.";
    error_log("documents.php error: " . $e->getMessage());
}

$typeLabels = ['ordonnance'=>'Prescription','resultat_examen'=>'Test Results','rapport'=>'Medical Report','autre'=>'Other'];
$typeIcons  = ['ordonnance'=>'fa-file-prescription','resultat_examen'=>'fa-flask','rapport'=>'fa-file-medical-alt','autre'=>'fa-file'];
$typeColors = ['ordonnance'=>'primary','resultat_examen'=>'info','rapport'=>'warning','autre'=>'secondary'];

function formatSize(int $bytes): string {
    if ($bytes >= 1048576) return round($bytes/1048576, 1).' MB';
    if ($bytes >= 1024)    return round($bytes/1024, 1).' KB';
    return $bytes.' B';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Medical Documents - <?php echo SITE_NAME; ?></title>
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
.card-header{background:#fff;border-bottom:1px solid #f0f0f0;padding:18px 22px;font-weight:600;color:#333;border-radius:15px 15px 0 0 !important;}
.card-body{padding:22px;}
.form-label{font-weight:600;color:#444;margin-bottom:6px;}
.form-control,.form-select{border-radius:10px;padding:11px 14px;border:2px solid #e0e0e0;transition:.3s;}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(102,126,234,.2);}
.btn-submit{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;
  border-radius:10px;padding:12px 24px;font-weight:600;color:#fff;transition:.2s;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(102,126,234,.4);color:#fff;}

/* Stat cards */
.stat-card{background:#fff;border-radius:15px;padding:20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06);transition:.3s;}
.stat-card:hover{transform:translateY(-4px);}
.stat-card h3{font-size:28px;font-weight:700;margin:8px 0 4px;}
.stat-card p{margin:0;font-size:13px;color:#666;}
.stat-icon{font-size:26px;margin-bottom:6px;}

/* Document rows */
.doc-item{background:#fff;border-radius:12px;padding:18px;margin-bottom:12px;
  box-shadow:0 2px 6px rgba(0,0,0,.05);display:flex;align-items:center;gap:16px;transition:.3s;}
.doc-item:hover{transform:translateX(4px);box-shadow:0 4px 16px rgba(102,126,234,.15);}
.doc-icon{width:52px;height:52px;border-radius:12px;display:flex;align-items:center;
  justify-content:center;font-size:22px;color:#fff;flex-shrink:0;}
.doc-icon.ordonnance{background:linear-gradient(135deg,#4facfe,#00f2fe);}
.doc-icon.resultat_examen{background:linear-gradient(135deg,#43e97b,#38f9d7);}
.doc-icon.rapport{background:linear-gradient(135deg,#f093fb,#f5576c);}
.doc-icon.autre{background:linear-gradient(135deg,#a8edea,#fed6e3);}

/* Upload zone */
.upload-zone{border:3px dashed #d0d7ff;border-radius:14px;padding:30px;text-align:center;
  cursor:pointer;transition:.3s;background:#fafbff;}
.upload-zone:hover{border-color:var(--primary);background:#f0f3ff;}
.upload-zone.drag-over{border-color:var(--primary);background:#e8edff;}

/* Filter tabs */
.filter-tab{padding:7px 18px;border-radius:20px;border:2px solid #e0e0e0;background:#fff;
  cursor:pointer;transition:.3s;font-size:14px;font-weight:500;text-decoration:none;color:#555;}
.filter-tab:hover,.filter-tab.active{background:linear-gradient(135deg,var(--primary),var(--secondary));
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
    <a href="symptomes.php"><i class="fas fa-notes-medical"></i><span>My Symptoms</span></a>
    <a href="documents.php" class="active"><i class="fas fa-file-medical"></i><span>Medical Documents</span></a>
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
    <h2><i class="fas fa-file-medical" style="color:var(--primary)"></i> Medical Documents</h2>
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
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:var(--primary)"><i class="fas fa-folder-open"></i></div>
          <h3><?php echo $stats['total']; ?></h3>
          <p>Total</p>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:#4facfe"><i class="fas fa-file-prescription"></i></div>
          <h3><?php echo $stats['ordonnances']; ?></h3>
          <p>Prescriptions</p>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:#43e97b"><i class="fas fa-flask"></i></div>
          <h3><?php echo $stats['resultats']; ?></h3>
          <p>Test Results</p>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:#f093fb"><i class="fas fa-file-medical-alt"></i></div>
          <h3><?php echo $stats['rapports']; ?></h3>
          <p>Reports</p>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:#adb5bd"><i class="fas fa-file"></i></div>
          <h3><?php echo $stats['autres']; ?></h3>
          <p>Other</p>
        </div>
      </div>
      <div class="col-6 col-md-2">
        <div class="stat-card">
          <div class="stat-icon" style="color:#fd7e14"><i class="fas fa-hdd"></i></div>
          <h3 style="font-size:18px"><?php echo formatSize((int)$stats['total_size']); ?></h3>
          <p>Storage Used</p>
        </div>
      </div>
    </div>

    <div class="row">
      <!-- Upload form -->
      <div class="col-lg-4">
        <div class="card" style="position:sticky;top:20px">
          <div class="card-header">
            <i class="fas fa-cloud-upload-alt" style="color:var(--primary)"></i> Upload Document
          </div>
          <div class="card-body">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">

              <!-- Drag & drop zone -->
              <div class="upload-zone mb-3" id="uploadZone" onclick="document.getElementById('fileInput').click()">
                <i class="fas fa-cloud-upload-alt fa-2x mb-2" style="color:var(--primary)"></i>
                <div id="uploadZoneText">
                  <strong>Click or drag & drop</strong><br>
                  <small class="text-muted">PDF, JPG, PNG — max 10 MB</small>
                </div>
              </div>
              <input type="file" id="fileInput" name="fichier" accept=".pdf,.jpg,.jpeg,.png" style="display:none" required>

              <!-- File preview -->
              <div id="filePreview" style="display:none;margin-bottom:12px">
                <div class="d-flex align-items-center gap-2 p-2" style="background:#f0f3ff;border-radius:8px">
                  <i class="fas fa-file fa-lg" style="color:var(--primary)"></i>
                  <div class="flex-grow-1">
                    <div id="fileName" style="font-size:13px;font-weight:600"></div>
                    <div id="fileSize" style="font-size:11px;color:#666"></div>
                  </div>
                  <button type="button" class="btn-close btn-sm" onclick="clearFile()"></button>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label">Document Type <span class="text-danger">*</span></label>
                <select name="type_document" class="form-select" required>
                  <option value="">-- Select --</option>
                  <option value="ordonnance">📋 Prescription</option>
                  <option value="resultat_examen">🧪 Test Results</option>
                  <option value="rapport">📄 Medical Report</option>
                  <option value="autre">📁 Other</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Linked Consultation</label>
                <select name="consultation_id" class="form-select">
                  <option value="">-- Not linked --</option>
                  <?php foreach ($consultations as $c): ?>
                  <option value="<?php echo $c['id']; ?>">
                    <?php echo (new DateTime($c['date_consultation']))->format('m/d/Y'); ?>
                    — Dr. <?php echo htmlspecialchars($c['medecin_nom']); ?>
                  </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="mb-4">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2"
                  placeholder="Brief description of the document..."></textarea>
              </div>

              <button type="submit" class="btn btn-submit w-100" id="uploadBtn">
                <i class="fas fa-upload"></i> Upload Document
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Documents list -->
      <div class="col-lg-8">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span><i class="fas fa-folder" style="color:var(--primary)"></i>
              My Documents
              <span class="badge" style="background:var(--primary)"><?php echo count($documents); ?></span>
            </span>
            <div class="d-flex gap-2 flex-wrap">
              <a href="documents.php" class="filter-tab <?php echo !$filterType?'active':''; ?>">All</a>
              <a href="?type=ordonnance" class="filter-tab <?php echo $filterType=='ordonnance'?'active':''; ?>">Prescriptions</a>
              <a href="?type=resultat_examen" class="filter-tab <?php echo $filterType=='resultat_examen'?'active':''; ?>">Results</a>
              <a href="?type=rapport" class="filter-tab <?php echo $filterType=='rapport'?'active':''; ?>">Reports</a>
              <a href="?type=autre" class="filter-tab <?php echo $filterType=='autre'?'active':''; ?>">Other</a>
            </div>
          </div>
          <div class="card-body">
            <?php if (empty($documents)): ?>
            <div class="text-center py-5 text-muted">
              <i class="fas fa-folder-open fa-4x mb-3" style="opacity:.2"></i>
              <h5>No documents<?php echo $filterType ? ' in this category' : ''; ?></h5>
              <p>Upload your first medical document using the form on the left.</p>
            </div>
            <?php else: ?>
            <?php foreach ($documents as $doc):
              $type = $doc['type_document'];
              $ext  = strtolower(pathinfo($doc['nom_fichier'], PATHINFO_EXTENSION));
              $uploadDate = new DateTime($doc['uploaded_at']);
            ?>
            <div class="doc-item">
              <!-- Icon -->
              <div class="doc-icon <?php echo $type; ?>">
                <i class="fas <?php echo $typeIcons[$type]; ?>"></i>
              </div>

              <!-- Info -->
              <div class="flex-grow-1">
                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                  <strong style="font-size:15px"><?php echo htmlspecialchars($doc['nom_fichier']); ?></strong>
                  <span class="badge bg-<?php echo $typeColors[$type]; ?>">
                    <?php echo $typeLabels[$type]; ?>
                  </span>
                  <span class="badge bg-light text-dark"><?php echo strtoupper($ext); ?></span>
                </div>

                <?php if ($doc['description']): ?>
                <p style="margin:2px 0;font-size:13px;color:#666"><?php echo htmlspecialchars($doc['description']); ?></p>
                <?php endif; ?>

                <div style="font-size:12px;color:#999;margin-top:4px">
                  <i class="fas fa-calendar"></i> <?php echo $uploadDate->format('m/d/Y H:i'); ?>
                  <?php if ($doc['taille_fichier']): ?>
                  &nbsp;<i class="fas fa-weight"></i> <?php echo formatSize((int)$doc['taille_fichier']); ?>
                  <?php endif; ?>
                  <?php if ($doc['medecin_nom']): ?>
                  &nbsp;<i class="fas fa-user-md"></i> Dr. <?php echo htmlspecialchars($doc['medecin_nom']); ?>
                  <?php endif; ?>
                </div>
              </div>

              <!-- Actions -->
              <div class="d-flex gap-2">
                <?php if (in_array($ext, ['jpg','jpeg','png','pdf']) && file_exists($doc['chemin_fichier'])): ?>
                <button class="btn btn-sm btn-outline-primary"
                        onclick="previewDoc('<?php echo htmlspecialchars($doc['chemin_fichier']); ?>','<?php echo $ext; ?>','<?php echo htmlspecialchars($doc['nom_fichier']); ?>')">
                  <i class="fas fa-eye"></i>
                </button>
                <a href="<?php echo htmlspecialchars($doc['chemin_fichier']); ?>" download
                   class="btn btn-sm btn-outline-success">
                  <i class="fas fa-download"></i>
                </a>
                <?php endif; ?>
                <a href="?delete=<?php echo $doc['id']; ?>"
                   class="btn btn-sm btn-outline-danger"
                   onclick="return confirm('Delete this document? This action cannot be undone.')">
                  <i class="fas fa-trash"></i>
                </a>
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

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:15px;border:none">
      <div class="modal-header" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:14px 14px 0 0">
        <h5 class="modal-title" id="previewTitle">Document Preview</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-2 text-center" id="previewBody" style="min-height:400px"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a id="previewDownload" href="#" download class="btn" style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff">
          <i class="fas fa-download"></i> Download
        </a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ── File input handling ── */
const fileInput = document.getElementById('fileInput');
const zone = document.getElementById('uploadZone');

fileInput.addEventListener('change', () => showFile(fileInput.files[0]));

zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
zone.addEventListener('drop', e => {
  e.preventDefault(); zone.classList.remove('drag-over');
  const f = e.dataTransfer.files[0];
  if (f) { fileInput.files = e.dataTransfer.files; showFile(f); }
});

function showFile(f) {
  if (!f) return;
  const allowed = ['application/pdf','image/jpeg','image/png','image/jpg'];
  if (!allowed.includes(f.type) && !['pdf','jpg','jpeg','png'].some(e => f.name.toLowerCase().endsWith(e))) {
    alert('File type not allowed. Please use PDF, JPG, or PNG.'); return;
  }
  document.getElementById('fileName').textContent = f.name;
  document.getElementById('fileSize').textContent = formatBytes(f.size);
  document.getElementById('filePreview').style.display = 'block';
  document.getElementById('uploadZoneText').innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> File ready</small>';
}

function clearFile() {
  fileInput.value = '';
  document.getElementById('filePreview').style.display = 'none';
  document.getElementById('uploadZoneText').innerHTML = '<strong>Click or drag & drop</strong><br><small class="text-muted">PDF, JPG, PNG — max 10 MB</small>';
}

function formatBytes(b) {
  if (b >= 1048576) return (b/1048576).toFixed(1)+' MB';
  if (b >= 1024) return (b/1024).toFixed(1)+' KB';
  return b+' B';
}

/* ── Preview modal ── */
function previewDoc(path, ext, name) {
  document.getElementById('previewTitle').textContent = name;
  document.getElementById('previewDownload').href = path;

  let content = '';
  if (ext === 'pdf') {
    content = `<iframe src="${path}" style="width:100%;height:70vh;border:none"></iframe>`;
  } else {
    content = `<img src="${path}" style="max-width:100%;max-height:70vh;object-fit:contain;border-radius:8px" alt="${name}">`;
  }
  document.getElementById('previewBody').innerHTML = content;
  new bootstrap.Modal(document.getElementById('previewModal')).show();
}

/* ── Upload progress feedback ── */
document.getElementById('uploadForm').addEventListener('submit', function() {
  const btn = document.getElementById('uploadBtn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
  btn.disabled = true;
});
</script>
</body>
</html>