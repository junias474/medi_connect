<?php
require_once '../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'administrateur') { header("Location: ../../login.php"); exit(); }
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("
        SELECT msg.*, CONCAT(ue.nom,' ',ue.prenom) as expediteur_nom, ue.role as exp_role,
               CONCAT(ud.nom,' ',ud.prenom) as destinataire_nom, ud.role as dest_role
        FROM messages msg
        INNER JOIN utilisateurs ue ON msg.expediteur_id=ue.id
        INNER JOIN utilisateurs ud ON msg.destinataire_id=ud.id
        ORDER BY msg.created_at DESC
    ");
    $messages = $stmt->fetchAll();
    $non_lus = $db->query("SELECT COUNT(*) FROM messages WHERE lu=0")->fetchColumn();
} catch(PDOException $e) { error_log($e->getMessage()); $messages=[]; $non_lus=0; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Messages - Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--sidebar-w:270px;--gradient:linear-gradient(135deg,#667eea,#764ba2)}
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
        .alert-badge{background:#fee2e2;color:#dc2626;border-radius:12px;padding:12px 18px;margin-bottom:20px;font-size:13px;font-weight:600;display:flex;align-items:center;gap:10px}
        .card{background:#fff;border-radius:16px;box-shadow:0 2px 10px rgba(0,0,0,.05);overflow:hidden}
        .msg-item{display:flex;gap:16px;padding:16px 22px;border-bottom:1px solid #f0f2f5;transition:background .2s;cursor:pointer}
        .msg-item:last-child{border-bottom:none}
        .msg-item:hover{background:#fafbff}
        .msg-item.unread{background:#f0f4ff;border-left:3px solid var(--primary)}
        .msg-av{width:40px;height:40px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0}
        .msg-body{flex:1;min-width:0}
        .msg-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px}
        .msg-header h6{font-size:14px;font-weight:700}
        .msg-header span{font-size:11px;color:#888}
        .msg-subject{font-size:13px;color:#333;margin-bottom:3px;font-weight:500}
        .msg-preview{font-size:12px;color:#888;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
        .badge{display:inline-block;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700}
        .badge-purple{background:#ede9fe;color:#7c3aed}.badge-info{background:#dbeafe;color:#2563eb}.badge-success{background:#dcfce7;color:#16a34a}
        .unread-dot{width:8px;height:8px;border-radius:50%;background:var(--primary);flex-shrink:0;margin-top:4px}
        .empty{text-align:center;padding:50px;color:#bbb}.empty i{font-size:40px;display:block;margin-bottom:10px}
        /* Modal */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9000;align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal{background:#fff;border-radius:20px;padding:32px;max-width:560px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.2);max-height:80vh;overflow-y:auto}
        .modal h4{font-size:17px;font-weight:700;margin-bottom:4px}
        .modal .meta{font-size:12px;color:#888;margin-bottom:20px}
        .modal .msg-content{font-size:14px;line-height:1.7;background:#f8f9ff;border-radius:12px;padding:18px;color:#333}
        .btn-close-modal{float:right;background:#f0f2f8;border:none;border-radius:10px;padding:8px 16px;cursor:pointer;font-size:13px;font-weight:600;font-family:'Outfit',sans-serif}
    </style>
</head>
<body>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="logo"><div class="logo-icon"><i class="bi bi-heart-pulse-fill"></i></div><div><h3><?php echo SITE_NAME; ?></h3><p>Administration</p></div></div>
        <div class="admin-chip">
            <div class="av"><?php echo strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)); ?></div>
            <div class="info"><h5><?php echo htmlspecialchars($_SESSION['user_prenom'].' '.$_SESSION['user_nom']); ?></h5><p>Super Admin</p></div>
        </div>
    </div>
    <nav class="sidebar-menu">
        <span class="menu-section">Tableau de bord</span><a href="index.php"><i class="bi bi-speedometer2"></i> Vue d'ensemble</a>
        <span class="menu-section">Gestion</span>
        <a href="medecins.php"><i class="bi bi-person-badge"></i> Médecins</a>
        <a href="patients.php"><i class="bi bi-people"></i> Patients</a>
        <a href="utilisateurs.php"><i class="bi bi-person-gear"></i> Utilisateurs</a>
        <span class="menu-section">Activité</span>
        <a href="rendez-vous.php"><i class="bi bi-calendar-check"></i> Rendez-vous</a>
        <a href="consultations.php"><i class="bi bi-clipboard2-pulse"></i> Consultations</a>
        <a href="paiements.php"><i class="bi bi-credit-card"></i> Paiements</a>
        <a href="messages.php" class="active"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Système</span>
        <a href="logs.php"><i class="bi bi-journal-text"></i> Logs activité</a>
        <a href="parametres.php"><i class="bi bi-gear"></i> Paramètres</a>
    </nav>
    <div class="sidebar-footer"><a href="../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>
<div class="main-content">
    <div class="topbar"><div><h2>Messages</h2><p><?php echo count($messages); ?> message(s) au total</p></div></div>
    <div class="content">
        <?php if ($non_lus > 0): ?>
        <div class="alert-badge"><i class="bi bi-envelope-exclamation"></i><?php echo $non_lus; ?> message(s) non lu(s)</div>
        <?php endif; ?>
        <div class="card">
            <?php if (count($messages) > 0): foreach ($messages as $msg):
                $initials = strtoupper(substr($msg['expediteur_nom'],0,1).substr(strrchr($msg['expediteur_nom'],' '),1,1));
                $role_b = ['patient'=>['info','Patient'],'medecin'=>['purple','Médecin'],'administrateur'=>['success','Admin']];
                $rb = $role_b[$msg['exp_role']] ?? ['gray','?'];
            ?>
            <div class="msg-item <?php echo !$msg['lu']?'unread':''; ?>" onclick="showMsg(<?php echo htmlspecialchars(json_encode($msg)); ?>)">
                <div class="msg-av"><?php echo $initials; ?></div>
                <div class="msg-body">
                    <div class="msg-header">
                        <h6><?php echo htmlspecialchars($msg['expediteur_nom']); ?> <span class="badge badge-<?php echo $rb[0]; ?>"><?php echo $rb[1]; ?></span>
                            → <?php echo htmlspecialchars($msg['destinataire_nom']); ?>
                        </h6>
                        <span><?php echo date('d/m/Y H:i', strtotime($msg['created_at'])); ?></span>
                    </div>
                    <div class="msg-subject"><?php echo htmlspecialchars($msg['sujet']); ?></div>
                    <div class="msg-preview"><?php echo htmlspecialchars(substr($msg['contenu'],0,100)); ?>...</div>
                </div>
                <?php if (!$msg['lu']): ?><div class="unread-dot"></div><?php endif; ?>
            </div>
            <?php endforeach; else: ?>
            <div class="empty"><i class="bi bi-chat-square"></i><p>Aucun message</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="modal-overlay" id="msgModal">
    <div class="modal">
        <button class="btn-close-modal" onclick="document.getElementById('msgModal').classList.remove('open')">✕ Fermer</button>
        <h4 id="msgSubject"></h4>
        <div class="meta" id="msgMeta"></div>
        <div class="msg-content" id="msgContent"></div>
    </div>
</div>
<script>
function showMsg(m) {
    document.getElementById('msgSubject').textContent = m.sujet;
    document.getElementById('msgMeta').textContent = 'De : '+m.expediteur_nom+' → '+m.destinataire_nom+' | '+m.created_at;
    document.getElementById('msgContent').textContent = m.contenu;
    document.getElementById('msgModal').classList.add('open');
}
</script>
</body>
</html>