<?php
/**
 * Messages - Médecin
 */
require_once '../../auth/config.php';
if (!isLoggedIn() || $_SESSION['user_role'] !== 'medecin') { header("Location: ../../login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

$message_ok = ''; $error = '';
$selected_conv = intval($_GET['conv'] ?? 0); // utilisateur_id de l'interlocuteur

// Envoyer un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['envoyer'])) {
    $dest_id = intval($_POST['destinataire_id']);
    $sujet   = sanitize($_POST['sujet'] ?? 'Message');
    $contenu = trim($_POST['contenu'] ?? '');
    if ($dest_id && $contenu) {
        $db->prepare("INSERT INTO messages (expediteur_id,destinataire_id,sujet,contenu) VALUES (?,?,?,?)")->execute([$user_id,$dest_id,$sujet,$contenu]);
        $message_ok = "Message envoyé.";
        $selected_conv = $dest_id;
    }
}

// Marquer comme lu les messages reçus de la conversation sélectionnée
if ($selected_conv) {
    $db->prepare("UPDATE messages SET lu=1, date_lecture=NOW() WHERE expediteur_id=? AND destinataire_id=? AND lu=0")->execute([$selected_conv, $user_id]);
}

// Liste des conversations (personnes avec qui on a échangé)
$stmt = $db->prepare("
    SELECT DISTINCT u.id, u.nom, u.prenom, u.role,
           (SELECT COUNT(*) FROM messages WHERE expediteur_id=u.id AND destinataire_id=? AND lu=0) as non_lus,
           (SELECT created_at FROM messages WHERE (expediteur_id=u.id AND destinataire_id=?) OR (expediteur_id=? AND destinataire_id=u.id) ORDER BY created_at DESC LIMIT 1) as dernier_msg
    FROM utilisateurs u
    INNER JOIN messages m ON (m.expediteur_id=u.id AND m.destinataire_id=?) OR (m.expediteur_id=? AND m.destinataire_id=u.id)
    WHERE u.id != ?
    ORDER BY dernier_msg DESC
");
$stmt->execute([$user_id,$user_id,$user_id,$user_id,$user_id,$user_id]);
$conversations = $stmt->fetchAll();

// Messages de la conversation sélectionnée
$msgs_conv = [];
$interlocuteur = null;
if ($selected_conv) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE (expediteur_id=? AND destinataire_id=?) OR (expediteur_id=? AND destinataire_id=?) ORDER BY created_at ASC");
    $stmt->execute([$user_id,$selected_conv,$selected_conv,$user_id]);
    $msgs_conv = $stmt->fetchAll();
    $stmt2 = $db->prepare("SELECT id, nom, prenom, role FROM utilisateurs WHERE id=?");
    $stmt2->execute([$selected_conv]);
    $interlocuteur = $stmt2->fetch();
}

// Patients pour nouveau message
$stmt = $db->prepare("SELECT u.id, u.nom, u.prenom, u.role FROM utilisateurs u WHERE u.statut='actif' AND u.id != ? ORDER BY u.nom ASC");
$stmt->execute([$user_id]);
$all_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Messages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--primary:#667eea;--secondary:#764ba2;--sidebar-w:260px;--gradient:linear-gradient(135deg,#667eea 0%,#764ba2 100%)}
        *{box-sizing:border-box;margin:0;padding:0}body{font-family:'Outfit',sans-serif;background:#f5f7fa;color:#333;height:100vh;overflow:hidden}
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
        .main-content{margin-left:var(--sidebar-w);height:100vh;display:flex;flex-direction:column}
        .topbar{background:#fff;padding:16px 28px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 8px rgba(0,0,0,.06);flex-shrink:0}
        .topbar h2{font-size:20px;font-weight:700}
        .chat-layout{display:grid;grid-template-columns:300px 1fr;flex:1;overflow:hidden}
        /* Conversations */
        .conv-panel{background:#fff;border-right:1px solid #f0f2f5;overflow-y:auto}
        .conv-header{padding:16px;border-bottom:1px solid #f0f2f5;display:flex;justify-content:space-between;align-items:center}
        .conv-header h5{font-size:14px;font-weight:700}
        .conv-item{display:flex;align-items:center;gap:12px;padding:14px 16px;cursor:pointer;border-bottom:1px solid #f9f9f9;transition:background .2s}
        .conv-item:hover,.conv-item.active{background:#f0f2ff}
        .conv-item .av{width:42px;height:42px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:15px;flex-shrink:0}
        .conv-item .info{flex:1;overflow:hidden}
        .conv-item .info h6{font-size:13px;font-weight:700;margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
        .conv-item .info p{font-size:11px;color:#888}
        .conv-item .badge-dot{width:20px;height:20px;background:#ef4444;border-radius:50%;color:#fff;font-size:10px;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0}
        /* Chat area */
        .chat-area{display:flex;flex-direction:column;overflow:hidden}
        .chat-header{padding:16px 22px;background:#fff;border-bottom:1px solid #f0f2f5;display:flex;align-items:center;gap:14px;flex-shrink:0}
        .chat-header .av{width:40px;height:40px;border-radius:50%;background:var(--gradient);display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:16px}
        .chat-header .info h5{font-size:14px;font-weight:700;margin-bottom:2px}
        .chat-header .info p{font-size:12px;color:#888}
        .chat-messages{flex:1;overflow-y:auto;padding:20px;background:#f8f9ff}
        .msg{display:flex;gap:10px;margin-bottom:16px;max-width:70%;animation:fadeIn .25s}
        @keyframes fadeIn{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:translateY(0)}}
        .msg.sent{margin-left:auto;flex-direction:row-reverse}
        .msg .av{width:34px;height:34px;border-radius:50%;background:#e5e7eb;display:flex;align-items:center;justify-content:center;font-size:13px;color:#555;font-weight:700;flex-shrink:0;align-self:flex-end}
        .msg.sent .av{background:var(--gradient);color:#fff}
        .msg .bubble{background:#fff;border-radius:16px 16px 16px 4px;padding:10px 14px;box-shadow:0 2px 6px rgba(0,0,0,.06);font-size:13px;line-height:1.5}
        .msg.sent .bubble{background:var(--gradient);color:#fff;border-radius:16px 16px 4px 16px}
        .msg .time{font-size:10px;color:#bbb;margin-top:4px}
        .msg.sent .time{text-align:right}
        .chat-input{padding:16px;background:#fff;border-top:1px solid #f0f2f5;flex-shrink:0}
        .chat-input form{display:flex;gap:10px;align-items:flex-end}
        .chat-input textarea{flex:1;padding:11px 14px;border:2px solid #e5e7eb;border-radius:12px;font-family:'Outfit',sans-serif;font-size:13px;outline:none;resize:none;transition:border-color .2s;max-height:120px}
        .chat-input textarea:focus{border-color:var(--primary)}
        .btn-send{padding:11px 20px;background:var(--gradient);color:#fff;border:none;border-radius:12px;cursor:pointer;font-size:18px;transition:all .25s;flex-shrink:0}
        .btn-send:hover{opacity:.88;transform:scale(1.05)}
        .no-conv{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:#bbb}
        .no-conv i{font-size:60px;margin-bottom:14px;opacity:.4}
        .new-msg-btn{padding:7px 14px;background:var(--gradient);color:#fff;border:none;border-radius:8px;cursor:pointer;font-size:12px;font-weight:600;font-family:'Outfit',sans-serif;display:flex;align-items:center;gap:5px}
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:9999;align-items:center;justify-content:center}
        .modal-overlay.show{display:flex}
        .modal{background:#fff;border-radius:16px;padding:24px;width:420px;max-width:95vw}
        .modal h5{font-size:16px;font-weight:700;margin-bottom:18px}
        .form-group{margin-bottom:14px}
        .form-group label{display:block;font-size:12px;font-weight:600;color:#555;margin-bottom:5px}
        .form-group input,.form-group textarea,.form-group select{width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:10px;font-family:'Outfit',sans-serif;font-size:13px;outline:none}
        .form-group input:focus,.form-group textarea:focus,.form-group select:focus{border-color:var(--primary)}
        .btn{padding:10px 20px;border-radius:10px;border:none;cursor:pointer;font-weight:600;font-size:13px;font-family:'Outfit',sans-serif;display:inline-flex;align-items:center;gap:6px;transition:all .25s;text-decoration:none}
        .btn-primary{background:var(--gradient);color:#fff}.btn-outline{background:#fff;border:2px solid #e5e7eb;color:#555}
        .btn:hover{opacity:.88}
        .alert{padding:12px 16px;border-radius:10px;margin-bottom:14px;font-size:13px}
        .alert-success{background:#dcfce7;color:#16a34a}
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
        <a href="ordonnances.php"><i class="bi bi-file-medical"></i> Ordonnances</a>
        <a href="messages.php" class="active"><i class="bi bi-chat-dots"></i> Messages</a>
        <span class="menu-section">Compte</span>
        <a href="profil.php"><i class="bi bi-person-gear"></i> Mon profil</a>
        <a href="evaluations.php"><i class="bi bi-star"></i> Évaluations</a>
    </nav>
    <div class="sidebar-footer"><a href="../../auth/logout.php"><i class="bi bi-box-arrow-left"></i> Déconnexion</a></div>
</aside>

<div class="main-content">
    <div class="topbar">
        <h2><i class="bi bi-chat-dots" style="color:var(--primary)"></i> Messagerie</h2>
    </div>
    <div class="chat-layout">
        <!-- Panel conversations -->
        <div class="conv-panel">
            <div class="conv-header">
                <h5>Conversations</h5>
                <button class="new-msg-btn" onclick="document.getElementById('modalNvMsg').classList.add('show')"><i class="bi bi-plus-lg"></i> Nouveau</button>
            </div>
            <?php if ($message_ok): ?><div class="alert alert-success" style="margin:10px;border-radius:10px;padding:10px;font-size:12px"><i class="bi bi-check-circle"></i> <?php echo $message_ok; ?></div><?php endif; ?>
            <?php if (count($conversations) > 0): foreach ($conversations as $conv): ?>
            <a href="messages.php?conv=<?php echo $conv['id']; ?>" style="text-decoration:none;color:inherit">
                <div class="conv-item <?php echo $selected_conv==$conv['id']?'active':''; ?>">
                    <div class="av"><?php echo strtoupper(substr($conv['prenom'],0,1).substr($conv['nom'],0,1)); ?></div>
                    <div class="info">
                        <h6><?php echo htmlspecialchars($conv['prenom'].' '.$conv['nom']); ?></h6>
                        <p><?php echo ucfirst($conv['role']); ?></p>
                    </div>
                    <?php if ($conv['non_lus'] > 0): ?><div class="badge-dot"><?php echo $conv['non_lus']; ?></div><?php endif; ?>
                </div>
            </a>
            <?php endforeach; else: ?>
            <div style="padding:30px;text-align:center;color:#bbb;font-size:13px"><i class="bi bi-chat-square" style="font-size:32px;display:block;margin-bottom:10px;opacity:.4"></i>Aucune conversation</div>
            <?php endif; ?>
        </div>

        <!-- Zone chat -->
        <?php if ($selected_conv && $interlocuteur): ?>
        <div class="chat-area">
            <div class="chat-header">
                <div class="av"><?php echo strtoupper(substr($interlocuteur['prenom'],0,1).substr($interlocuteur['nom'],0,1)); ?></div>
                <div class="info">
                    <h5><?php echo htmlspecialchars($interlocuteur['prenom'].' '.$interlocuteur['nom']); ?></h5>
                    <p><?php echo ucfirst($interlocuteur['role']); ?></p>
                </div>
            </div>
            <div class="chat-messages" id="chatMessages">
                <?php foreach ($msgs_conv as $msg): $sent = $msg['expediteur_id'] == $user_id; ?>
                <div class="msg <?php echo $sent?'sent':''; ?>">
                    <div class="av"><?php echo $sent ? strtoupper(substr($_SESSION['user_prenom'],0,1).substr($_SESSION['user_nom'],0,1)) : strtoupper(substr($interlocuteur['prenom'],0,1).substr($interlocuteur['nom'],0,1)); ?></div>
                    <div>
                        <div class="bubble"><?php echo nl2br(htmlspecialchars($msg['contenu'])); ?></div>
                        <div class="time"><?php echo date('d/m H:i',strtotime($msg['created_at'])); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($msgs_conv)): ?><div style="text-align:center;color:#bbb;padding:30px;font-size:13px">Commencez la conversation</div><?php endif; ?>
            </div>
            <div class="chat-input">
                <form method="POST">
                    <input type="hidden" name="destinataire_id" value="<?php echo $selected_conv; ?>">
                    <input type="hidden" name="sujet" value="Message">
                    <textarea name="contenu" placeholder="Écrire un message..." rows="2" required onkeydown="if(event.ctrlKey&&event.key==='Enter')this.form.submit()"></textarea>
                    <button type="submit" name="envoyer" class="btn-send"><i class="bi bi-send"></i></button>
                </form>
            </div>
        </div>
        <?php else: ?>
        <div class="no-conv">
            <i class="bi bi-chat-dots"></i>
            <p style="font-size:15px">Sélectionnez une conversation</p>
            <p style="font-size:13px;margin-top:6px">ou démarrez-en une nouvelle</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal nouveau message -->
<div class="modal-overlay" id="modalNvMsg" onclick="if(event.target===this)this.classList.remove('show')">
    <div class="modal">
        <h5><i class="bi bi-pencil-square" style="color:var(--primary)"></i> Nouveau message</h5>
        <form method="POST">
            <input type="hidden" name="envoyer" value="1">
            <div class="form-group"><label>Destinataire</label>
                <select name="destinataire_id" required>
                    <option value="">Choisir...</option>
                    <?php foreach ($all_users as $u): ?><option value="<?php echo $u['id']; ?>"><?php echo htmlspecialchars($u['prenom'].' '.$u['nom']); ?> (<?php echo ucfirst($u['role']); ?>)</option><?php endforeach; ?>
                </select>
            </div>
            <div class="form-group"><label>Sujet</label><input type="text" name="sujet" placeholder="Sujet du message"></div>
            <div class="form-group"><label>Message</label><textarea name="contenu" rows="4" placeholder="Votre message..." required></textarea></div>
            <div style="display:flex;gap:10px">
                <button type="submit" class="btn btn-primary"><i class="bi bi-send"></i> Envoyer</button>
                <button type="button" class="btn btn-outline" onclick="document.getElementById('modalNvMsg').classList.remove('show')">Annuler</button>
            </div>
        </form>
    </div>
</div>
<script>
const cm = document.getElementById('chatMessages');
if(cm) cm.scrollTop = cm.scrollHeight;
</script>
</body>
</html>