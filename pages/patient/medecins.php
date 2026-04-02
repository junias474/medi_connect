<?php
require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];

try {
    $db = Database::getInstance()->getConnection();

    /* ── Filters ── */
    $search     = sanitize($_GET['search'] ?? '');
    $specialty  = sanitize($_GET['specialite'] ?? '');
    $ville      = sanitize($_GET['ville'] ?? '');
    $dispo      = isset($_GET['disponible']) ? 1 : null;
    $sort       = sanitize($_GET['sort'] ?? 'note');

    /* ── Build query ── */
    $where = ["u.statut = 'actif'"];
    $params = [];

    if ($search) {
        $where[] = "(u.nom LIKE ? OR u.prenom LIKE ? OR m.specialite LIKE ? OR m.hopital_affiliation LIKE ?)";
        $s = "%$search%";
        $params = array_merge($params, [$s,$s,$s,$s]);
    }
    if ($specialty) {
        $where[] = "m.specialite = ?";
        $params[] = $specialty;
    }
    if ($ville) {
        $where[] = "u.ville = ?";
        $params[] = $ville;
    }
    if ($dispo !== null) {
        $where[] = "m.disponible = 1";
    }

    $orderMap = [
        'note'        => 'm.note_moyenne DESC',
        'experience'  => 'm.annees_experience DESC',
        'consultations'=> 'm.nombre_consultations DESC',
        'tarif_asc'   => 'm.tarif_consultation ASC',
        'tarif_desc'  => 'm.tarif_consultation DESC',
    ];
    $orderBy = $orderMap[$sort] ?? 'm.note_moyenne DESC';

    $whereSQL = implode(' AND ', $where);

    $stmt = $db->prepare("
        SELECT m.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.photo_profil, u.genre
        FROM medecins m
        JOIN utilisateurs u ON m.utilisateur_id = u.id
        WHERE $whereSQL
        ORDER BY $orderBy
    ");
    $stmt->execute($params);
    $medecins = $stmt->fetchAll();

    /* ── Filter options ── */
    $specialites = $db->query("SELECT DISTINCT specialite FROM medecins ORDER BY specialite")->fetchAll(PDO::FETCH_COLUMN);
    $villes      = $db->query("SELECT DISTINCT u.ville FROM utilisateurs u JOIN medecins m ON m.utilisateur_id=u.id WHERE u.ville IS NOT NULL ORDER BY u.ville")->fetchAll(PDO::FETCH_COLUMN);

    /* ── Doctor detail (AJAX or GET) ── */
    if (isset($_GET['detail']) && is_numeric($_GET['detail'])) {
        $dStmt = $db->prepare("
            SELECT m.*, u.nom, u.prenom, u.email, u.telephone, u.ville, u.genre,
                   u.date_naissance, u.adresse
            FROM medecins m JOIN utilisateurs u ON m.utilisateur_id = u.id
            WHERE m.id = ? AND u.statut = 'actif'
        ");
        $dStmt->execute([$_GET['detail']]);
        $detail = $dStmt->fetch();

        /* Schedules */
        $hStmt = $db->prepare("SELECT * FROM horaires_medecin WHERE medecin_id = ? AND disponible = 1 ORDER BY FIELD(jour_semaine,'Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche')");
        $hStmt->execute([$_GET['detail']]);
        $horaires = $hStmt->fetchAll();

        /* Reviews */
        $aStmt = $db->prepare("
            SELECT ae.*, CONCAT(u.prenom,' ',u.nom) as patient_nom, ae.date_avis
            FROM avis_evaluations ae
            JOIN patients p ON ae.patient_id = p.id
            JOIN utilisateurs u ON p.utilisateur_id = u.id
            WHERE ae.medecin_id = ?
            ORDER BY ae.date_avis DESC LIMIT 5
        ");
        $aStmt->execute([$_GET['detail']]);
        $avis = $aStmt->fetchAll();
    }

} catch (PDOException $e) {
    error_log("medecins.php error: " . $e->getMessage());
    $medecins = [];
}

$dayEN = ['Lundi'=>'Monday','Mardi'=>'Tuesday','Mercredi'=>'Wednesday',
           'Jeudi'=>'Thursday','Vendredi'=>'Friday','Samedi'=>'Saturday','Dimanche'=>'Sunday'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Find a Doctor - <?php echo SITE_NAME; ?></title>
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
.card{border:none;border-radius:15px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:20px;}
.card-header{background:#fff;border-bottom:1px solid #f0f0f0;padding:18px 22px;font-weight:600;color:#333;border-radius:15px 15px 0 0 !important;}
.card-body{padding:22px;}
.form-control,.form-select{border-radius:10px;padding:10px 14px;border:2px solid #e0e0e0;transition:.3s;}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(102,126,234,.2);}

/* Doctor card */
.doc-card{background:#fff;border-radius:15px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,.06);
  transition:.3s;border:2px solid transparent;cursor:pointer;height:100%;}
.doc-card:hover{transform:translateY(-4px);box-shadow:0 8px 24px rgba(102,126,234,.2);border-color:var(--primary);}
.doc-avatar{width:70px;height:70px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));
  display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:24px;flex-shrink:0;}
.specialty-tag{display:inline-block;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;
  background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;}
.star-rating{color:#ffc107;}
.info-badge{background:#f0f3ff;color:var(--primary);padding:4px 10px;border-radius:8px;font-size:12px;font-weight:600;}
.btn-book{background:linear-gradient(135deg,var(--primary),var(--secondary));border:none;
  border-radius:10px;padding:9px 18px;font-weight:600;color:#fff;font-size:14px;transition:.2s;}
.btn-book:hover{transform:translateY(-1px);box-shadow:0 4px 14px rgba(102,126,234,.4);color:#fff;}

/* Filter bar */
.filter-bar{background:#fff;border-radius:15px;padding:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:24px;}
.result-count{font-size:14px;color:#666;font-weight:500;}

/* Modal */
.modal-header{background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;border-radius:12px 12px 0 0;}
.schedule-item{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid #f0f0f0;font-size:14px;}
.review-item{padding:12px;background:#f8f9fa;border-radius:10px;margin-bottom:8px;}
.stars-filled{color:#ffc107;}
.stars-empty{color:#ddd;}
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
    <a href="documents.php"><i class="fas fa-file-medical"></i><span>Medical Documents</span></a>
    <a href="medecins.php" class="active"><i class="fas fa-user-md"></i><span>Find a Doctor</span></a>
    <a href="profil.php"><i class="fas fa-user-cog"></i><span>My Profile</span></a>
  </div>
  <div class="sidebar-footer">
    <a href="../../logout.php" style="color:#fff;text-decoration:none;display:flex;align-items:center;gap:10px;">
      <i class="fas fa-sign-out-alt"></i><span>Logout</span>
    </a>
  </div>
</div>

<!-- Main Content -->
<div class="main-content">
  <div class="top-navbar">
    <h2><i class="fas fa-user-md" style="color:var(--primary)"></i> Find a Doctor</h2>
    <a href="index.php" class="btn btn-outline-secondary btn-sm">
      <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
  </div>

  <div class="content-area">

    <!-- Search & Filters -->
    <div class="filter-bar">
      <form method="GET" class="row g-3 align-items-end">
        <div class="col-md-3">
          <label class="form-label fw-semibold mb-1">Search</label>
          <input type="text" name="search" class="form-control"
                 placeholder="Name, specialty..." value="<?php echo htmlspecialchars($search); ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold mb-1">Specialty</label>
          <select name="specialite" class="form-select">
            <option value="">All</option>
            <?php foreach ($specialites as $sp): ?>
            <option value="<?php echo htmlspecialchars($sp); ?>" <?php echo $specialty==$sp?'selected':''; ?>>
              <?php echo htmlspecialchars($sp); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold mb-1">City</label>
          <select name="ville" class="form-select">
            <option value="">All</option>
            <?php foreach ($villes as $v): ?>
            <option value="<?php echo htmlspecialchars($v); ?>" <?php echo $ville==$v?'selected':''; ?>>
              <?php echo htmlspecialchars($v); ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold mb-1">Sort By</label>
          <select name="sort" class="form-select">
            <option value="note" <?php echo $sort=='note'?'selected':''; ?>>Best Rating</option>
            <option value="experience" <?php echo $sort=='experience'?'selected':''; ?>>Most Experience</option>
            <option value="consultations" <?php echo $sort=='consultations'?'selected':''; ?>>Most Consultations</option>
            <option value="tarif_asc" <?php echo $sort=='tarif_asc'?'selected':''; ?>>Fee: Low to High</option>
            <option value="tarif_desc" <?php echo $sort=='tarif_desc'?'selected':''; ?>>Fee: High to Low</option>
          </select>
        </div>
        <div class="col-md-2">
          <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" name="disponible" id="dispCheck"
                   <?php echo isset($_GET['disponible'])?'checked':''; ?>>
            <label class="form-check-label fw-semibold" for="dispCheck">Available Only</label>
          </div>
        </div>
        <div class="col-md-1">
          <button type="submit" class="btn btn-book w-100">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </form>
    </div>

    <div class="result-count mb-3">
      <i class="fas fa-user-md"></i>
      <strong><?php echo count($medecins); ?></strong> doctor(s) found
      <?php if ($search || $specialty || $ville): ?>
      — <a href="medecins.php" class="text-decoration-none" style="color:var(--primary)">Clear filters</a>
      <?php endif; ?>
    </div>

    <!-- Doctor grid -->
    <?php if (empty($medecins)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fas fa-user-md fa-4x mb-3" style="opacity:.2"></i>
      <h5>No doctors found</h5>
      <p>Try adjusting your filters.</p>
    </div>
    <?php else: ?>
    <div class="row g-4">
      <?php foreach ($medecins as $med): ?>
      <div class="col-md-6 col-xl-4">
        <div class="doc-card" onclick="showDetail(<?php echo $med['id']; ?>)">
          <div class="d-flex gap-3 mb-3">
            <div class="doc-avatar">
              <?php echo strtoupper(substr($med['prenom'],0,1).substr($med['nom'],0,1)); ?>
            </div>
            <div class="flex-grow-1">
              <h5 style="margin:0;font-size:16px;font-weight:700">
                Dr. <?php echo htmlspecialchars($med['prenom'].' '.$med['nom']); ?>
              </h5>
              <span class="specialty-tag"><?php echo htmlspecialchars($med['specialite']); ?></span>
              <div class="mt-1">
                <?php if ($med['disponible']): ?>
                <span class="badge bg-success"><i class="fas fa-circle" style="font-size:8px"></i> Available</span>
                <?php else: ?>
                <span class="badge bg-secondary">Unavailable</span>
                <?php endif; ?>
              </div>
            </div>
          </div>

          <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="info-badge"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($med['ville'] ?? 'N/A'); ?></span>
            <span class="info-badge"><i class="fas fa-briefcase"></i> <?php echo $med['annees_experience']; ?> yrs</span>
            <span class="info-badge"><i class="fas fa-stethoscope"></i> <?php echo $med['nombre_consultations']; ?> consults</span>
          </div>

          <!-- Star rating -->
          <div class="d-flex align-items-center gap-2 mb-3">
            <div class="star-rating">
              <?php
              $note = floatval($med['note_moyenne']);
              for ($i=1; $i<=5; $i++) {
                echo $i <= $note
                  ? '<i class="fas fa-star"></i>'
                  : ($i - 0.5 <= $note ? '<i class="fas fa-star-half-alt"></i>' : '<i class="far fa-star"></i>');
              }
              ?>
            </div>
            <span style="font-size:14px;color:#666"><?php echo number_format($note,1); ?>/5</span>
          </div>

          <!-- Languages & fee -->
          <div class="d-flex justify-content-between align-items-center">
            <div style="font-size:13px;color:#666">
              <i class="fas fa-language"></i> <?php echo htmlspecialchars($med['langues_parlees']); ?>
            </div>
            <div style="font-size:15px;color:#28a745;font-weight:700">
              <?php echo number_format($med['tarif_consultation'],0,',',' '); ?> FCFA
            </div>
          </div>

          <div class="d-flex gap-2 mt-3">
            <button class="btn btn-book flex-grow-1"
                    onclick="event.stopPropagation();window.location='nouveau-rdv.php?medecin_id=<?php echo $med['id']; ?>'">
              <i class="fas fa-calendar-plus"></i> Book
            </button>
            <button class="btn btn-outline-secondary"
                    onclick="event.stopPropagation();showDetail(<?php echo $med['id']; ?>)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

  </div>
</div>

<!-- Doctor Detail Modal -->
<div class="modal fade" id="doctorModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content" style="border-radius:15px;border:none">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Doctor Profile</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" id="modalBody">
        <div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-2x" style="color:var(--primary)"></i></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-book" id="modalBookBtn">
          <i class="fas fa-calendar-plus"></i> Book an Appointment
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Doctor data for JS -->
<script>
const doctors = <?php echo json_encode($medecins); ?>;
const dayEN   = <?php echo json_encode($dayEN); ?>;

function showDetail(id) {
  const doc = doctors.find(d => d.id == id);
  if (!doc) return;

  document.getElementById('modalTitle').textContent = `Dr. ${doc.prenom} ${doc.nom}`;
  document.getElementById('modalBookBtn').onclick = () => {
    window.location = `nouveau-rdv.php?medecin_id=${id}`;
  };

  const stars = (n) => {
    let s=''; for(let i=1;i<=5;i++){
      s += i<=n ? '<i class="fas fa-star stars-filled"></i>'
                : '<i class="far fa-star stars-empty"></i>';
    } return s;
  };

  document.getElementById('modalBody').innerHTML = `
    <div class="p-4">
      <div class="d-flex gap-4 mb-4">
        <div style="width:90px;height:90px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);
                    display:flex;align-items:center;justify-content:center;color:#fff;font-size:32px;font-weight:700;flex-shrink:0">
          ${doc.prenom[0]}${doc.nom[0]}
        </div>
        <div>
          <h4 class="mb-1">Dr. ${doc.prenom} ${doc.nom}</h4>
          <span class="specialty-tag">${doc.specialite}</span>
          <div class="mt-2">${stars(doc.note_moyenne)}
            <span class="ms-1 text-muted">(${parseFloat(doc.note_moyenne).toFixed(1)}/5)</span>
          </div>
        </div>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
          <div class="text-center p-3" style="background:#f0f3ff;border-radius:12px">
            <div style="font-size:24px;font-weight:700;color:var(--primary)">${doc.annees_experience}</div>
            <div style="font-size:12px;color:#666">Yrs Experience</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-center p-3" style="background:#f0fff4;border-radius:12px">
            <div style="font-size:24px;font-weight:700;color:#28a745">${doc.nombre_consultations}</div>
            <div style="font-size:12px;color:#666">Consultations</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-center p-3" style="background:#fff9f0;border-radius:12px">
            <div style="font-size:18px;font-weight:700;color:#fd7e14">${parseInt(doc.tarif_consultation).toLocaleString()}</div>
            <div style="font-size:12px;color:#666">FCFA / consult</div>
          </div>
        </div>
        <div class="col-6 col-md-3">
          <div class="text-center p-3" style="background:${doc.disponible?'#f0fff4':'#fff5f5'};border-radius:12px">
            <div style="font-size:20px">${doc.disponible ? '✅' : '❌'}</div>
            <div style="font-size:12px;color:#666">${doc.disponible ? 'Available' : 'Unavailable'}</div>
          </div>
        </div>
      </div>

      <div class="row g-3">
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="fas fa-info-circle" style="color:var(--primary)"></i> Information</h6>
          <table class="table table-sm table-borderless" style="font-size:14px">
            <tr><td class="text-muted">City</td><td>${doc.ville || 'N/A'}</td></tr>
            <tr><td class="text-muted">Order No.</td><td>${doc.numero_medecin}</td></tr>
            <tr><td class="text-muted">Languages</td><td>${doc.langues_parlees}</td></tr>
            <tr><td class="text-muted">Hospital</td><td>${doc.hopital_affiliation || 'N/A'}</td></tr>
          </table>
          ${doc.description ? `<p style="font-size:14px;color:#555">${doc.description}</p>` : ''}
        </div>
        <div class="col-md-6">
          <h6 class="fw-bold"><i class="fas fa-clock" style="color:var(--primary)"></i> Contact</h6>
          <table class="table table-sm table-borderless" style="font-size:14px">
            <tr><td class="text-muted">Phone</td><td>${doc.telephone}</td></tr>
            <tr><td class="text-muted">Email</td><td>${doc.email}</td></tr>
          </table>
        </div>
      </div>
    </div>`;

  new bootstrap.Modal(document.getElementById('doctorModal')).show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>