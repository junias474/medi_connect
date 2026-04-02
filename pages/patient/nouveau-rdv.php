<?php
require_once '../../auth/config.php';

if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$user_id    = $_SESSION['user_id'];
$error      = '';
$success    = '';

try {
    $db = Database::getInstance()->getConnection();

    /* ── Handle form submission ── */
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $medecin_id       = intval($_POST['medecin_id'] ?? 0);
        $date_rdv         = sanitize($_POST['date_rendez_vous'] ?? '');
        $heure_debut      = sanitize($_POST['heure_debut'] ?? '');
        $type_consultation = sanitize($_POST['type_consultation'] ?? 'teleconsultation');
        $motif            = sanitize($_POST['motif'] ?? '');
        $notes_patient    = sanitize($_POST['notes_patient'] ?? '');

        if (!$medecin_id || !$date_rdv || !$heure_debut || !$motif) {
            $error = "Please fill in all required fields.";
        } elseif (strtotime($date_rdv) < strtotime(date('Y-m-d'))) {
            $error = "The appointment date cannot be in the past.";
        } else {
            /* Calculate end time (+30 min default) */
            $heure_fin = date('H:i:s', strtotime($heure_debut) + 1800);

            /* Check for conflicts */
            $conflict = $db->prepare("
                SELECT id FROM rendez_vous
                WHERE medecin_id = ? AND date_rendez_vous = ?
                  AND statut NOT IN ('annule','patient_absent')
                  AND (
                        (heure_debut <= ? AND heure_fin > ?)
                     OR (heure_debut < ? AND heure_fin >= ?)
                  )
                LIMIT 1
            ");
            $conflict->execute([$medecin_id, $date_rdv,
                                $heure_debut, $heure_debut,
                                $heure_fin,   $heure_fin]);

            if ($conflict->fetch()) {
                $error = "This time slot is already booked. Please choose another.";
            } else {
                $stmt = $db->prepare("
                    INSERT INTO rendez_vous
                        (patient_id, medecin_id, date_rendez_vous, heure_debut, heure_fin,
                         type_consultation, motif, notes_patient, statut)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente')
                ");
                $stmt->execute([$patient_id, $medecin_id, $date_rdv,
                                $heure_debut, $heure_fin,
                                $type_consultation, $motif, $notes_patient]);

                $rdv_id = $db->lastInsertId();

                /* Notification to doctor */
                $notifStmt = $db->prepare("
                    INSERT INTO notifications (utilisateur_id, type, titre, message, lien)
                    SELECT u.id, 'rendez_vous',
                           'New appointment request',
                           CONCAT('Patient ', ?, ' has booked an appointment on ', ?),
                           CONCAT('/pages/medecin/rendez-vous.php?id=', ?)
                    FROM medecins m JOIN utilisateurs u ON m.utilisateur_id = u.id
                    WHERE m.id = ?
                ");
                $notifStmt->execute([
                    $_SESSION['user_prenom'].' '.$_SESSION['user_nom'],
                    $date_rdv.' at '.$heure_debut,
                    $rdv_id, $medecin_id
                ]);

                $success = "Your appointment has been successfully booked! It is pending confirmation by the doctor.";
            }
        }
    }

    /* ── Load doctors ── */
    $specialites = $db->query("SELECT DISTINCT specialite FROM medecins WHERE disponible = 1 ORDER BY specialite")->fetchAll();
    $medecins    = $db->query("SELECT * FROM vue_medecins_complets WHERE disponible = 1 ORDER BY specialite, nom")->fetchAll();

} catch (PDOException $e) {
    $error = "An error occurred. Please try again.";
    error_log("nouveau-rdv.php error: " . $e->getMessage());
}

$min_date = date('Y-m-d');
$max_date = date('Y-m-d', strtotime('+3 months'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>New Appointment - <?php echo SITE_NAME; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
:root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:260px;}
body{font-family:'Segoe UI',sans-serif;background:#f5f7fa;}

/* ── Sidebar ── */
.sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);
  background:linear-gradient(180deg,var(--primary),var(--secondary));color:#fff;z-index:1000;
  box-shadow:2px 0 10px rgba(0,0,0,.1);}
.sidebar-header{padding:30px 20px;text-align:center;border-bottom:1px solid rgba(255,255,255,.1);}
.sidebar-header h4{margin:10px 0 5px;font-size:18px;}
.sidebar-header p{margin:0;font-size:13px;opacity:.8;}
.sidebar-menu{padding:20px 0;}
.sidebar-menu a{display:flex;align-items:center;padding:15px 25px;color:#fff;text-decoration:none;
  transition:.3s;border-left:4px solid transparent;}
.sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.1);border-left-color:#fff;}
.sidebar-menu a i{width:30px;font-size:18px;}
.sidebar-footer{position:absolute;bottom:0;width:100%;padding:20px;border-top:1px solid rgba(255,255,255,.1);}

/* ── Main ── */
.main-content{margin-left:var(--sidebar-w);}
.top-navbar{background:#fff;padding:20px 30px;box-shadow:0 2px 4px rgba(0,0,0,.05);
  display:flex;justify-content:space-between;align-items:center;}
.top-navbar h2{margin:0;font-size:24px;color:#333;}
.content-area{padding:30px;}

/* ── Cards ── */
.card{border:none;border-radius:15px;box-shadow:0 2px 8px rgba(0,0,0,.06);margin-bottom:24px;}
.card-header{background:#fff;border-bottom:1px solid #f0f0f0;padding:20px 25px;font-weight:600;color:#333;border-radius:15px 15px 0 0 !important;}
.card-body{padding:25px;}

/* ── Form ── */
.form-label{font-weight:600;color:#444;margin-bottom:6px;}
.form-control,.form-select{border-radius:10px;padding:11px 14px;border:2px solid #e0e0e0;transition:.3s;}
.form-control:focus,.form-select:focus{border-color:var(--primary);box-shadow:0 0 0 .2rem rgba(102,126,234,.2);}
.btn-submit{background:linear-gradient(135deg,var(--primary),var(--secondary));
  border:none;border-radius:10px;padding:13px 30px;font-size:16px;font-weight:600;
  color:#fff;transition:.2s;}
.btn-submit:hover{transform:translateY(-2px);box-shadow:0 5px 20px rgba(102,126,234,.4);color:#fff;}

/* ── Doctor cards ── */
.doctor-card{border:2px solid #e0e0e0;border-radius:12px;padding:18px;cursor:pointer;transition:.3s;margin-bottom:12px;}
.doctor-card:hover{border-color:var(--primary);background:#f8f9ff;}
.doctor-card.selected{border-color:var(--primary);background:#f0f3ff;}
.doctor-badge{display:inline-block;padding:4px 10px;border-radius:20px;font-size:12px;
  background:linear-gradient(135deg,var(--primary),var(--secondary));color:#fff;}

/* ── Time slots ── */
.time-slot{display:inline-block;padding:8px 16px;border:2px solid #e0e0e0;border-radius:8px;
  cursor:pointer;margin:4px;transition:.3s;font-size:14px;}
.time-slot:hover{border-color:var(--primary);color:var(--primary);}
.time-slot.selected{background:linear-gradient(135deg,var(--primary),var(--secondary));
  border-color:var(--primary);color:#fff;}
.time-slot.booked{background:#f8d7da;border-color:#f5c6cb;color:#721c24;cursor:not-allowed;}

/* ── Steps indicator ── */
.steps{display:flex;justify-content:center;gap:0;margin-bottom:30px;}
.step{display:flex;align-items:center;gap:8px;padding:10px 20px;
  border-bottom:3px solid #e0e0e0;color:#999;font-weight:500;transition:.3s;}
.step.active{border-bottom-color:var(--primary);color:var(--primary);}
.step.done{border-bottom-color:#28a745;color:#28a745;}
.step-num{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;
  justify-content:center;font-size:13px;font-weight:700;border:2px solid currentColor;}
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
    <a href="nouveau-rdv.php" class="active"><i class="fas fa-calendar-plus"></i><span>New Appointment</span></a>
    <a href="consultations.php"><i class="fas fa-stethoscope"></i><span>My Consultations</span></a>
    <a href="symptomes.php"><i class="fas fa-notes-medical"></i><span>My Symptoms</span></a>
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
    <h2><i class="fas fa-calendar-plus" style="color:var(--primary)"></i> New Appointment</h2>
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

    <form method="POST" id="appointmentForm">
      <div class="row">

        <!-- LEFT: Doctor selection + Date/Time -->
        <div class="col-lg-7">

          <!-- Step 1: Choose specialty -->
          <div class="card">
            <div class="card-header">
              <i class="fas fa-stethoscope" style="color:var(--primary)"></i>
              Step 1 — Choose a Specialty
            </div>
            <div class="card-body">
              <div class="row g-2" id="specialtyGrid">
                <?php
                $icons = [
                  'Médecine Générale'=>'fa-user-md','Pédiatrie'=>'fa-baby',
                  'Cardiologie'=>'fa-heart','Dermatologie'=>'fa-allergies',
                  'Gynécologie'=>'fa-venus','Neurologie'=>'fa-brain',
                  'Ophtalmologie'=>'fa-eye','default'=>'fa-stethoscope'
                ];
                foreach ($specialites as $sp):
                  $icon = $icons[$sp['specialite']] ?? $icons['default'];
                ?>
                <div class="col-6 col-md-4">
                  <div class="doctor-card text-center specialty-btn" data-specialty="<?php echo htmlspecialchars($sp['specialite']); ?>">
                    <i class="fas <?php echo $icon; ?> fa-2x mb-2" style="color:var(--primary)"></i>
                    <div style="font-weight:600;font-size:14px"><?php echo htmlspecialchars($sp['specialite']); ?></div>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>

          <!-- Step 2: Choose doctor -->
          <div class="card" id="doctorSection" style="display:none">
            <div class="card-header">
              <i class="fas fa-user-md" style="color:var(--primary)"></i>
              Step 2 — Choose a Doctor
            </div>
            <div class="card-body" id="doctorList">
              <?php foreach ($medecins as $med): ?>
              <div class="doctor-card doctor-option"
                   data-specialty="<?php echo htmlspecialchars($med['specialite']); ?>"
                   data-id="<?php echo $med['id']; ?>"
                   style="display:none">
                <div class="d-flex align-items-center gap-3">
                  <div style="width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--secondary));
                              display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:18px;flex-shrink:0">
                    <?php echo strtoupper(substr($med['prenom'],0,1).substr($med['nom'],0,1)); ?>
                  </div>
                  <div class="flex-grow-1">
                    <div style="font-weight:700">Dr. <?php echo htmlspecialchars($med['prenom'].' '.$med['nom']); ?></div>
                    <span class="doctor-badge"><?php echo htmlspecialchars($med['specialite']); ?></span>
                    <div class="mt-1" style="font-size:13px;color:#666">
                      <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($med['ville'] ?? 'N/A'); ?> &nbsp;
                      <i class="fas fa-star" style="color:#ffc107"></i> <?php echo number_format($med['note_moyenne'],1); ?> &nbsp;
                      <i class="fas fa-briefcase"></i> <?php echo $med['annees_experience']; ?> yrs exp.
                    </div>
                    <div style="font-size:13px;color:#28a745;font-weight:600">
                      <i class="fas fa-money-bill-wave"></i>
                      <?php echo number_format($med['tarif_consultation'],0,',',' '); ?> FCFA / consultation
                    </div>
                  </div>
                  <div>
                    <?php if ($med['disponible']): ?>
                      <span class="badge bg-success">Available</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Unavailable</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Step 3: Date & Time -->
          <div class="card" id="dateSection" style="display:none">
            <div class="card-header">
              <i class="fas fa-calendar-alt" style="color:var(--primary)"></i>
              Step 3 — Choose Date & Time
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label class="form-label">Appointment Date <span class="text-danger">*</span></label>
                <input type="date" class="form-control" name="date_rendez_vous" id="dateInput"
                       min="<?php echo $min_date; ?>" max="<?php echo $max_date; ?>"
                       value="<?php echo $_POST['date_rendez_vous'] ?? ''; ?>">
              </div>
              <div id="timeSlotsWrap" style="display:none">
                <label class="form-label">Available Time Slots <span class="text-danger">*</span></label>
                <input type="hidden" name="heure_debut" id="heureInput">
                <div id="timeSlots"></div>
              </div>
            </div>
          </div>

        </div><!-- /col-lg-7 -->

        <!-- RIGHT: Summary & details -->
        <div class="col-lg-5">
          <div class="card" style="position:sticky;top:20px">
            <div class="card-header">
              <i class="fas fa-clipboard-list" style="color:var(--primary)"></i>
              Appointment Details
            </div>
            <div class="card-body">
              <input type="hidden" name="medecin_id" id="medecinIdInput">

              <!-- Summary box -->
              <div id="summaryBox" class="p-3 mb-4" style="background:#f8f9fa;border-radius:10px;display:none">
                <h6 style="color:var(--primary);font-weight:700">Summary</h6>
                <div id="summaryContent"></div>
              </div>

              <div class="mb-3">
                <label class="form-label">Consultation Type <span class="text-danger">*</span></label>
                <select name="type_consultation" class="form-select">
                  <option value="teleconsultation" <?php echo (($_POST['type_consultation']??'')=='teleconsultation')?'selected':''; ?>>
                    📹 Video Consultation (Online)
                  </option>
                  <option value="presentiel" <?php echo (($_POST['type_consultation']??'')=='presentiel')?'selected':''; ?>>
                    🏥 In-Person Consultation
                  </option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Reason for Visit <span class="text-danger">*</span></label>
                <textarea name="motif" class="form-control" rows="3"
                  placeholder="Briefly describe the reason for your appointment..."><?php echo $_POST['motif'] ?? ''; ?></textarea>
              </div>

              <div class="mb-4">
                <label class="form-label">Additional Notes</label>
                <textarea name="notes_patient" class="form-control" rows="2"
                  placeholder="Symptoms, allergies, important information..."><?php echo $_POST['notes_patient'] ?? ''; ?></textarea>
              </div>

              <button type="submit" class="btn btn-submit w-100" id="submitBtn" disabled>
                <i class="fas fa-calendar-check"></i> Confirm Appointment
              </button>

              <div class="mt-3 p-3" style="background:#fff3cd;border-radius:10px;font-size:13px">
                <i class="fas fa-info-circle" style="color:#856404"></i>
                <strong>Important:</strong> Your appointment will be pending until the doctor confirms it.
                You will receive a notification once confirmed.
              </div>
            </div>
          </div>
        </div>

      </div><!-- /row -->
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
const allDoctors = <?php echo json_encode($medecins); ?>;
let selectedDoctor = null, selectedDate = null, selectedTime = null;

/* ── Specialty click ── */
document.querySelectorAll('.specialty-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.specialty-btn').forEach(b => b.classList.remove('selected'));
    btn.classList.add('selected');
    const sp = btn.dataset.specialty;
    showDoctors(sp);
    document.getElementById('doctorSection').style.display = 'block';
    document.getElementById('dateSection').style.display = 'none';
    resetSlots(); resetSummary();
    document.getElementById('submitBtn').disabled = true;
  });
});

function showDoctors(specialty) {
  document.querySelectorAll('.doctor-option').forEach(d => {
    d.style.display = (d.dataset.specialty === specialty) ? 'block' : 'none';
    d.classList.remove('selected');
  });
  selectedDoctor = null;
}

/* ── Doctor click ── */
document.querySelectorAll('.doctor-option').forEach(card => {
  card.addEventListener('click', () => {
    document.querySelectorAll('.doctor-option').forEach(c => c.classList.remove('selected'));
    card.classList.add('selected');
    selectedDoctor = allDoctors.find(d => d.id == card.dataset.id);
    document.getElementById('medecinIdInput').value = card.dataset.id;
    document.getElementById('dateSection').style.display = 'block';
    resetSlots(); updateSummary();
    document.getElementById('submitBtn').disabled = true;
  });
});

/* ── Date input ── */
document.getElementById('dateInput').addEventListener('change', function() {
  selectedDate = this.value;
  if (selectedDate && selectedDoctor) loadSlots(selectedDoctor.id, selectedDate);
  updateSummary();
});

function loadSlots(doctorId, date) {
  const wrap = document.getElementById('timeSlotsWrap');
  const container = document.getElementById('timeSlots');
  wrap.style.display = 'block';
  container.innerHTML = '<div class="text-muted"><i class="fas fa-spinner fa-spin"></i> Loading slots...</div>';

  fetch(`get_slots.php?medecin_id=${doctorId}&date=${date}`)
    .then(r => r.json())
    .then(data => renderSlots(data.booked || []))
    .catch(() => renderSlots([]));
}

function renderSlots(bookedSlots) {
  const container = document.getElementById('timeSlots');
  container.innerHTML = '';
  const slots = ['08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
                  '14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30'];
  slots.forEach(t => {
    const el = document.createElement('span');
    el.className = 'time-slot' + (bookedSlots.includes(t) ? ' booked' : '');
    el.textContent = t;
    if (!bookedSlots.includes(t)) {
      el.addEventListener('click', () => {
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        el.classList.add('selected');
        selectedTime = t;
        document.getElementById('heureInput').value = t + ':00';
        updateSummary();
        document.getElementById('submitBtn').disabled = false;
      });
    }
    container.appendChild(el);
  });
}

function resetSlots() {
  selectedTime = null;
  document.getElementById('heureInput').value = '';
  document.getElementById('timeSlotsWrap').style.display = 'none';
  document.getElementById('timeSlots').innerHTML = '';
}

function updateSummary() {
  const box = document.getElementById('summaryBox');
  const content = document.getElementById('summaryContent');
  if (!selectedDoctor) { box.style.display = 'none'; return; }
  box.style.display = 'block';
  content.innerHTML = `
    <div style="font-size:14px;line-height:2">
      <div><i class="fas fa-user-md" style="color:var(--primary);width:20px"></i>
        <strong>Doctor:</strong> Dr. ${selectedDoctor.prenom} ${selectedDoctor.nom}</div>
      <div><i class="fas fa-stethoscope" style="color:var(--primary);width:20px"></i>
        <strong>Specialty:</strong> ${selectedDoctor.specialite}</div>
      ${selectedDate ? `<div><i class="fas fa-calendar" style="color:var(--primary);width:20px"></i>
        <strong>Date:</strong> ${new Date(selectedDate).toLocaleDateString('en-US',{weekday:'long',year:'numeric',month:'long',day:'numeric'})}</div>` : ''}
      ${selectedTime ? `<div><i class="fas fa-clock" style="color:var(--primary);width:20px"></i>
        <strong>Time:</strong> ${selectedTime}</div>` : ''}
      <div><i class="fas fa-money-bill" style="color:#28a745;width:20px"></i>
        <strong>Fee:</strong> ${parseInt(selectedDoctor.tarif_consultation).toLocaleString()} FCFA</div>
    </div>`;
}

function resetSummary() {
  selectedDoctor = null;
  document.getElementById('summaryBox').style.display = 'none';
  document.getElementById('medecinIdInput').value = '';
}

/* ── Form validation ── */
document.getElementById('appointmentForm').addEventListener('submit', function(e) {
  const motif = document.querySelector('[name="motif"]').value.trim();
  if (!document.getElementById('medecinIdInput').value) {
    e.preventDefault(); alert('Please select a doctor.'); return;
  }
  if (!document.getElementById('dateInput').value) {
    e.preventDefault(); alert('Please choose a date.'); return;
  }
  if (!document.getElementById('heureInput').value) {
    e.preventDefault(); alert('Please choose a time slot.'); return;
  }
  if (!motif) {
    e.preventDefault(); alert('Please enter the reason for your visit.'); return;
  }
});
</script>
</body>
</html>