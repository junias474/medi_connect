<?php
/**
 * Gestion des Patients - Administrateur
 */
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') {
    header("Location: ../login.php"); exit();
}

$message = ''; $message_type = '';

try {
    $db = Database::getInstance()->getConnection();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = $_POST['action'] ?? '';
        $uid = (int)($_POST['utilisateur_id'] ?? 0);
        if ($action === 'suspendre' && $uid) {
            $db->prepare("UPDATE utilisateurs SET statut='suspendu' WHERE id=? AND role='patient'")->execute([$uid]);
            $message = "Patient suspendu."; $message_type = 'warning';
        } elseif ($action === 'activer' && $uid) {
            $db->prepare("UPDATE utilisateurs SET statut='actif' WHERE id=? AND role='patient'")->execute([$uid]);
            $message = "Patient activé."; $message_type = 'success';
        } elseif ($action === 'supprimer' && $uid) {
            $db->prepare("DELETE FROM utilisateurs WHERE id=? AND role='patient'")->execute([$uid]);
            $message = "Patient supprimé."; $message_type = 'danger';
        }
    }

    $search = trim($_GET['q'] ?? '');
    $filtre = $_GET['filtre'] ?? 'tous';
    $where = "WHERE u.role='patient'";
    $params = [];
    if ($filtre === 'actif')    $where .= " AND u.statut='actif'";
    if ($filtre === 'suspendu') $where .= " AND u.statut='suspendu'";
    if ($search) {
        $where .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR p.numero_patient LIKE ?)";
        $s = "%$search%"; $params = [$s,$s,$s,$s];
    }

    $stmt = $db->prepare("
        SELECT u.id as utilisateur_id, u.nom, u.prenom, u.email, u.telephone,
               u.statut, u.date_inscription, u.ville, u.date_naissance,
               p.id as patient_id, p.numero_patient, p.groupe_sanguin,
               p.allergies, p.maladies_chroniques, p.assurance_medicale,
               (SELECT COUNT(*) FROM rendez_vous rv WHERE rv.patient_id=p.id) as nb_rdv,
               (SELECT COUNT(*) FROM consultations c WHERE c.patient_id=p.id) as nb_consultations
        FROM utilisateurs u
        INNER JOIN patients p ON p.utilisateur_id=u.id
        $where
        ORDER BY u.date_inscription DESC
    ");
    $stmt->execute($params);
    $patients = $stmt->fetchAll();

    $c_total    = $db->query("SELECT COUNT(*) FROM patients")->fetchColumn();
    $c_actif    = $db->query("SELECT COUNT(*) FROM utilisateurs u INNER JOIN patients p ON p.utilisateur_id=u.id WHERE u.statut='actif'")->fetchColumn();
    $c_suspendu = $db->query("SELECT COUNT(*) FROM utilisateurs u INNER JOIN patients p ON p.utilisateur_id=u.id WHERE u.statut='suspendu'")->fetchColumn();
    $c_rdv      = $db->query("SELECT COUNT(*) FROM rendez_vous")->fetchColumn();

} catch(PDOException $e) { error_log($e->getMessage()); $patients = []; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Patients - Admin - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:270px;--gradient:linear-gradient(135deg,#667eea,#764ba2)}
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:'Outfit',sans-serif;background:#f0f2f8;color:#1a1a2e}
        .sidebar{position:fixed;top:0;left:0;height:100vh;width:var(--sidebar-w);background:var(--gradient);color:#fff;z-index:1000;display:flex;flex-direction:column;box-shadow:4px 0 20px rgba(102,126,234,.25)}
        .sidebar-brand{padding:28px 22px 20px;border-bottom:1px solid rgba(255,255,255,.15)}
        .logo{display:flex;align-items:center;gap:12px}.logo-icon{width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;font-size:20px}
        .logo h3{font-size:17px;font-weight:700}.logo p{font-size:11px;opacity:.7;margin-top:2px}
        .admin-chip{margin-top:14px;background:rgba(255,255,255,.15);border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:10px}
        .admin-chip .av{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,.25);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:700}
        .admin-chip .info h5{font-size:13px;font-weight:600}.admin-chip .info p{font-size:11px;opacity:.75}
        .sidebar-menu{flex:1;padding:18px 0;overflow-y:auto}.menu-section{padding:12px 22px 5px;font-size:10px;text-transform:uppercase;letter-spacing:1.5px;opacity:.5;font-weight:600}
        .sidebar-menu a{display:flex;align-items:center;gap:12px;padding:12px 22px;color:rgba(255,255,255,.82);text-decoration:none;font-size:13.5px;font-weight:500;border-left:3px solid transparent;transition:all .22s}
        .sidebar-menu a:hover,.sidebar-menu a.active{background:rgba(255,255,255,.13);color:#fff;border-left-color:rgba(255,255,255,.9)}
        .sidebar-menu a i{font-size:17px;width:20px}
        .sidebar-footer{padding:18px 22px;border-top:1px solid rgba(255,255,255,.15)}
        .sidebar-footer a{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,.8);text-decoration:none;font-size:13px}
        .main-content{margin-left:var(--sidebar-w)}
        .topbar{background:#fff;padding:16px 30px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 10px rgba(0,0,0,.06);position:sticky;top:0;z-index:100}
        .topbar h2{font-size:21px;font-weight:800}.topbar p{font-size:13px;color:#888;margin-top:2px}
        .content{padding:26px 30px}
        .mini-stats{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:22px}
        .mini-card{background:#fff;border-radius:14px;padding:18px;box-shadow:0 2px 10px rgba(0,0,0,.05);text-align:center;cursor:pointer;text-decoration:none;color:inherit;transition:all .25s;border:2px solid transparent}
        .mini-card:hover,.mini-card.active{border-color:var(--primary);transform:translateY(-2px)}
        .mini-card .n{font-size:26px;font-weight:800;color:var(--primary)}.mini-card p{font-size:12px;color:#888;margin-top:3px}
        .toolbar{display:flex;gap:12px;margin-bottom:18px;align-items:center;flex-wrap:wrap}
        .search-box{display:flex;align-items:center;gap:10px;background:#fff;border-radius:12px;padding:10px 16px;border:2px solid #e8eaf5;flex:1;min-width:220px;box-shadow:0 2px 8px rgba(0,0,0,.04)}
        .search-box input{border:none;outline:none;font-size:14px;font-family:'Outfit',sans-serif;width:100%;background:transparent}
        .search-box i{color:#888;font-size:16px}
        .btn{padding:10px 20px;border-radius:12px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:8px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-primary:hover{opacity:.88}
        .btn-outline{background:#fff;color:#555;border:2px solid #e8eaf5}.btn-outline:hover{border-color:var(--primary);color:var(--primary)}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        .card-header{padding:18px 24px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .card-header h5{font-size:15px;font-weight:700;display:flex;align-items:center;gap:8px}
        table{width:100%;border-collapse:collapse}
        th{font-size:11px;text-transform:uppercase;letter-spacing:.8px;color:#888;font-weight:700;padding:14px 20px;text-align:left;background:#fafbff}
        td{padding:13px 20px;font-size:13px;border-bottom:1px solid #f0f2f5;vertical-align:middle}
        tr:last-child td{border-bottom:none}tr:hover td{background:#fafbff}
        .badge{display:inline-block;padding:4px 11px;border-radius:20px;font-size:11px;font-weight:700}
        .badge-success{background:#dcfce7;color:#16a34a}.badge-warning{background:#fef9c3;color:#ca8a04}
        .badge-danger{background:#fee2e2;color:#dc2626}.badge-info{background:#dbeafe;color:#2563eb}
        .badge-gray{background:#f3f4f6;color:#6b7280}
        .user-av{width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#11998e,#38ef7d);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px}
        .user-cell{display:flex;align-items:center;gap:10px}
        .user-cell .uinfo h6{font-size:13px;font-weight:600;margin-bottom:1px}
        .user-cell .uinfo p{font-size:11px;color:#888}
        .actions{display:flex;gap:6px}
        .btn-xs{padding:5px 12px;border-radius:8px;font-size:11px;font-weight:700;border:none;cursor:pointer;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:4px;transition:all .2s}
        .btn-xs-success{background:#dcfce7;color:#16a34a}.btn-xs-danger{background:#fee2e2;color:#dc2626}
        .btn-xs-warning{background:#fef9c3;color:#ca8a04}.btn-xs-info{background:#dbeafe;color:#2563eb}
        .btn-xs:hover{opacity:.8}
        .alert{padding:12px 18px;border-radius:12px;margin-bottom:18px;font-size:13px;font-weight:500;display:flex;align-items:center;gap:10px}
        .alert-success{background:#dcfce7;color:#16a34a}.alert-warning{background:#fef9c3;color:#ca8a04}.alert-danger{background:#fee2e2;color:#dc2626}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
        .blood{display:inline-block;width:28px;height:28px;border-radius:50%;background:#fee2e2;color:#dc2626;font-size:10px;font-weight:800;display:inline-flex;align-items:center;justify-content:center}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo">
            <div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div>
            <div><h3><?php echo SITE_NAME; ?></h3><p>Administration</p></div>
        </div>
        <div class="admin-chip">
            <div class="av"><?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?></div>
            <div class="info"><h5><?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h5><p>Super Admin</p></div>
        </div>
    </div>
    <nav class="sidebar-menu">
        <span class="menu-section">Tableau de bord</span>
        <a href="index.php"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a>
        <span class="menu-section">Gestion</span>
        <a href="medecins.php"><i class="bi bi-person-badge"></i> Médecins</a>
        <a href="patients.php" class="active"><i class="bi bi-people"></i> Patients</a>
        <a href="utilisateurs.php"><i class="bi bi-person-gear"></i> Utilisateurs</a>
        <span class="menu-section">Activité</span>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <div><h2>Gestion des Patients</h2><p><?php echo count($patients); ?> patient(s) trouvé(s)</p></div>
    </div>
    <div class="content">
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>"><i class="bi bi-info-circle"></i><?php echo $message; ?></div>
        <?php endif; ?>

        <div class="mini-stats">
            <a href="?filtre=tous" class="mini-card <?php echo $filtre==='tous'?'active':''; ?>">
                <div class="n"><?php echo $c_total; ?></div><p>Total patients</p>
            </a>
            <a href="?filtre=actif" class="mini-card <?php echo $filtre==='actif'?'active':''; ?>">
                <div class="n" style="color:#22c55e"><?php echo $c_actif; ?></div><p>Actifs</p>
            </a>
            <a href="?filtre=suspendu" class="mini-card <?php echo $filtre==='suspendu'?'active':''; ?>">
                <div class="n" style="color:#ef4444"><?php echo $c_suspendu; ?></div><p>Suspendus</p>
            </a>
            <a href="rendez-vous.php" class="mini-card">
                <div class="n"><?php echo $c_rdv; ?></div><p>Total RDV</p>
            </a>
        </div>

        <div class="toolbar">
            <form method="GET" style="display:flex;gap:12px;flex:1;flex-wrap:wrap">
                <input type="hidden" name="filtre" value="<?php echo htmlspecialchars($filtre); ?>">
                <div class="search-box">
                    <i class="bi bi-search"></i>
                    <input type="text" name="q" placeholder="Rechercher un patient..." value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Rechercher</button>
                <?php if ($search): ?><a href="patients.php" class="btn btn-outline"><i class="bi bi-x"></i> Effacer</a><?php endif; ?>
            </form>
        </div>

        <div class="card">
            <div class="card-header">
                <h5><i class="bi bi-people" style="color:var(--primary)"></i> Liste des patients</h5>
            </div>
            <?php if (count($patients) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>N° Patient</th>
                        <th>Groupe</th>
                        <th>Ville</th>
                        <th>RDV</th>
                        <th>Consultations</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($patients as $p):
                    $initials = strtoupper(substr($p['prenom'],0,1).substr($p['nom'],0,1));
                ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-av"><?php echo $initials; ?></div>
                            <div class="uinfo">
                                <h6><?php echo htmlspecialchars($p['prenom'].' '.$p['nom']); ?></h6>
                                <p><?php echo htmlspecialchars($p['email']); ?></p>
                                <p><?php echo htmlspecialchars($p['telephone']); ?></p>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:12px;font-weight:700;color:var(--primary)"><?php echo htmlspecialchars($p['numero_patient']); ?></td>
                    <td>
                        <?php if ($p['groupe_sanguin']): ?>
                        <span class="blood"><?php echo $p['groupe_sanguin']; ?></span>
                        <?php else: ?><span style="color:#bbb">—</span><?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($p['ville'] ?? '—'); ?></td>
                    <td style="text-align:center;font-weight:700"><?php echo $p['nb_rdv']; ?></td>
                    <td style="text-align:center;font-weight:700;color:var(--primary)"><?php echo $p['nb_consultations']; ?></td>
                    <td>
                        <span class="badge <?php echo $p['statut']==='actif'?'badge-success':($p['statut']==='suspendu'?'badge-danger':'badge-warning'); ?>">
                            <?php echo ucfirst($p['statut']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <form method="POST" style="display:inline" onsubmit="return confirm('<?php echo $p['statut']==='actif'?'Suspendre':'Activer'; ?> ce patient ?')">
                                <input type="hidden" name="action" value="<?php echo $p['statut']==='actif'?'suspendre':'activer'; ?>">
                                <input type="hidden" name="utilisateur_id" value="<?php echo $p['utilisateur_id']; ?>">
                                <button type="submit" class="btn-xs <?php echo $p['statut']==='actif'?'btn-xs-warning':'btn-xs-success'; ?>">
                                    <i class="bi bi-<?php echo $p['statut']==='actif'?'pause-circle':'play-circle'; ?>"></i>
                                    <?php echo $p['statut']==='actif'?'Suspendre':'Activer'; ?>
                                </button>
                            </form>
                            <form method="POST" style="display:inline" onsubmit="return confirm('Supprimer définitivement ce patient ?')">
                                <input type="hidden" name="action" value="supprimer">
                                <input type="hidden" name="utilisateur_id" value="<?php echo $p['utilisateur_id']; ?>">
                                <button type="submit" class="btn-xs btn-xs-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div class="empty"><i class="bi bi-people"></i><p>Aucun patient trouvé</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>