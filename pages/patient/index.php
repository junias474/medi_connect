<?php
/**
 * Dashboard Patient
 * Application de Consultation Médicale
 */

require_once '../../auth/config.php';

// Vérifier si l'utilisateur est connecté et est un patient
if (!isLoggedIn() || $_SESSION['user_role'] !== 'patient') {
    header("Location: ../../login.php");
    exit();
}

$patient_id = $_SESSION['patient_id'];
$user_id = $_SESSION['user_id'];

try {
    $db = Database::getInstance()->getConnection();
    
    // Récupérer les informations du patient
    $stmt = $db->prepare("SELECT * FROM vue_patients_complets WHERE id = ?");
    $stmt->execute([$patient_id]);
    $patient = $stmt->fetch();
    
    // Statistiques du patient
    // Nombre de rendez-vous
    $stmt = $db->prepare("SELECT COUNT(*) as total FROM rendez_vous WHERE patient_id = ?");
    $stmt->execute([$patient_id]);
    $stats_rdv = $stmt->fetch();
    
    // Rendez-vous à venir
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM rendez_vous 
        WHERE patient_id = ? 
        AND date_rendez_vous >= CURDATE() 
        AND statut IN ('en_attente', 'confirme')
    ");
    $stmt->execute([$patient_id]);
    $rdv_a_venir = $stmt->fetch();
    
    // Consultations terminées
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM consultations 
        WHERE patient_id = ? 
        AND statut = 'termine'
    ");
    $stmt->execute([$patient_id]);
    $consultations_terminees = $stmt->fetch();
    
    // Prochains rendez-vous (3 prochains)
    $stmt = $db->prepare("
        SELECT rv.*, 
               CONCAT(u.nom, ' ', u.prenom) as medecin_nom,
               m.specialite
        FROM rendez_vous rv
        INNER JOIN medecins med ON rv.medecin_id = med.id
        INNER JOIN utilisateurs u ON med.utilisateur_id = u.id
        INNER JOIN medecins m ON rv.medecin_id = m.id
        WHERE rv.patient_id = ?
        AND rv.date_rendez_vous >= CURDATE()
        AND rv.statut IN ('en_attente', 'confirme')
        ORDER BY rv.date_rendez_vous ASC, rv.heure_debut ASC
        LIMIT 3
    ");
    $stmt->execute([$patient_id]);
    $prochains_rdv = $stmt->fetchAll();
    
    // Dernières consultations (3 dernières)
    $stmt = $db->prepare("
        SELECT c.*, 
               rv.date_rendez_vous,
               CONCAT(u.nom, ' ', u.prenom) as medecin_nom,
               m.specialite
        FROM consultations c
        INNER JOIN rendez_vous rv ON c.rendez_vous_id = rv.id
        INNER JOIN medecins med ON c.medecin_id = med.id
        INNER JOIN utilisateurs u ON med.utilisateur_id = u.id
        INNER JOIN medecins m ON c.medecin_id = m.id
        WHERE c.patient_id = ?
        ORDER BY c.date_consultation DESC
        LIMIT 3
    ");
    $stmt->execute([$patient_id]);
    $dernieres_consultations = $stmt->fetchAll();
    
    // Notifications non lues
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM notifications 
        WHERE utilisateur_id = ? 
        AND lu = 0
    ");
    $stmt->execute([$user_id]);
    $notif_non_lues = $stmt->fetch();
    
} catch(PDOException $e) {
    error_log("Erreur dashboard patient : " . $e->getMessage());
    $error = "Une erreur est survenue lors du chargement du dashboard.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Patient - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #667eea;
            --secondary-color: #764ba2;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar-header {
            padding: 30px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h4 {
            margin: 10px 0 5px 0;
            font-size: 18px;
        }
        
        .sidebar-header p {
            margin: 0;
            font-size: 13px;
            opacity: 0.8;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 25px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 4px solid transparent;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.1);
            border-left-color: white;
        }
        
        .sidebar-menu a i {
            width: 30px;
            font-size: 18px;
        }
        
        .sidebar-footer {
            position: absolute;
            bottom: 0;
            width: 100%;
            padding: 20px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        /* Main content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
        }
        
        /* Top navbar */
        .top-navbar {
            background: white;
            padding: 20px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .top-navbar h2 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .notification-badge {
            position: relative;
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }
        
        /* Content area */
        .content-area {
            padding: 30px;
        }
        
        /* Stats cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 15px;
        }
        
        .stat-card-icon.blue {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .stat-card-icon.green {
            background: linear-gradient(135deg, #11998e, #38ef7d);
            color: white;
        }
        
        .stat-card-icon.orange {
            background: linear-gradient(135deg, #f093fb, #f5576c);
            color: white;
        }
        
        .stat-card-icon.purple {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
            color: white;
        }
        
        .stat-card h3 {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0 5px 0;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        
        .card-header {
            background: white;
            border-bottom: 1px solid #f0f0f0;
            padding: 20px 25px;
            font-weight: 600;
            color: #333;
        }
        
        .card-body {
            padding: 25px;
        }
        
        /* Appointment card */
        .appointment-item {
            display: flex;
            align-items: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }
        
        .appointment-item:hover {
            background: #e9ecef;
            transform: translateX(5px);
        }
        
        .appointment-date {
            text-align: center;
            padding: 15px;
            background: white;
            border-radius: 10px;
            margin-right: 20px;
            min-width: 80px;
        }
        
        .appointment-date .day {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
            line-height: 1;
        }
        
        .appointment-date .month {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
        }
        
        .appointment-info {
            flex: 1;
        }
        
        .appointment-info h5 {
            margin: 0 0 5px 0;
            font-size: 16px;
            color: #333;
        }
        
        .appointment-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }
        
        .appointment-time {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-top: 5px;
            color: var(--primary-color);
            font-size: 14px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
        }
        
        .btn-action {
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.3;
        }
        
        /* Quick actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .quick-action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: var(--primary-color);
        }
        
        .quick-action-btn i {
            font-size: 28px;
            color: var(--primary-color);
        }
        
        .quick-action-btn span {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-user-circle" style="font-size: 60px;"></i>
            <h4><?php echo htmlspecialchars($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']); ?></h4>
            <p><i class="fas fa-circle" style="font-size: 8px; color: #4ade80;"></i> Patient</p>
        </div>
        
        <div class="sidebar-menu">
            <a href="index.php" class="active">
                <i class="fas fa-home"></i>
                <span>Accueil</span>
            </a>
            <a href="rendez-vous.php">
                <i class="fas fa-calendar-check"></i>
                <span>Mes Rendez-vous</span>
            </a>
            <a href="nouveau-rdv.php">
                <i class="fas fa-calendar-plus"></i>
                <span>Nouveau Rendez-vous</span>
            </a>
            <a href="consultations.php">
                <i class="fas fa-stethoscope"></i>
                <span>Mes Consultations</span>
            </a>
            <a href="symptomes.php">
                <i class="fas fa-notes-medical"></i>
                <span>Mes Symptômes</span>
            </a>
            <a href="documents.php">
                <i class="fas fa-file-medical"></i>
                <span>Documents Médicaux</span>
            </a>
            <a href="profil.php">
                <i class="fas fa-user-cog"></i>
                <span>Mon Profil</span>
            </a>
        </div>
        
        <div class="sidebar-footer">
            <a href="../../logout.php" style="color: white; text-decoration: none; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Déconnexion</span>
            </a>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Navbar -->
        <div class="top-navbar">
            <h2>Tableau de bord</h2>
            
            <div class="user-info">
                <div class="notification-badge">
                    <i class="fas fa-bell"></i>
                    <?php if ($notif_non_lues['total'] > 0): ?>
                        <span class="badge bg-danger"><?php echo $notif_non_lues['total']; ?></span>
                    <?php endif; ?>
                </div>
                
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['user_prenom'], 0, 1) . substr($_SESSION['user_nom'], 0, 1)); ?>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content-area">
            <!-- Welcome Message -->
            <div class="alert alert-info" style="border-radius: 15px; border: none;">
                <h5><i class="fas fa-info-circle"></i> Bienvenue, <?php echo htmlspecialchars($_SESSION['user_prenom']); ?> !</h5>
                <p class="mb-0">Gérez vos rendez-vous médicaux et consultez votre historique de santé en toute simplicité.</p>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="nouveau-rdv.php" class="quick-action-btn">
                    <i class="fas fa-calendar-plus"></i>
                    <span>Prendre RDV</span>
                </a>
                <a href="symptomes.php" class="quick-action-btn">
                    <i class="fas fa-notes-medical"></i>
                    <span>Saisir symptômes</span>
                </a>
                <a href="medecins.php" class="quick-action-btn">
                    <i class="fas fa-user-md"></i>
                    <span>Trouver un médecin</span>
                </a>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <h3><?php echo $stats_rdv['total']; ?></h3>
                    <p>Rendez-vous au total</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3><?php echo $rdv_a_venir['total']; ?></h3>
                    <p>Rendez-vous à venir</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-stethoscope"></i>
                    </div>
                    <h3><?php echo $consultations_terminees['total']; ?></h3>
                    <p>Consultations terminées</p>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon purple">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3><?php echo !empty($patient['groupe_sanguin']) ? $patient['groupe_sanguin'] : 'N/A'; ?></h3>
                    <p>Groupe sanguin</p>
                </div>
            </div>
            
            <div class="row">
                <!-- Prochains rendez-vous -->
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-calendar-alt"></i> Prochains rendez-vous
                        </div>
                        <div class="card-body">
                            <?php if (count($prochains_rdv) > 0): ?>
                                <?php foreach ($prochains_rdv as $rdv): 
                                    $date = new DateTime($rdv['date_rendez_vous']);
                                    $mois = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];
                                ?>
                                <div class="appointment-item">
                                    <div class="appointment-date">
                                        <div class="day"><?php echo $date->format('d'); ?></div>
                                        <div class="month"><?php echo $mois[(int)$date->format('n')]; ?></div>
                                    </div>
                                    <div class="appointment-info">
                                        <h5>Dr. <?php echo htmlspecialchars($rdv['medecin_nom']); ?></h5>
                                        <p><?php echo htmlspecialchars($rdv['specialite']); ?></p>
                                        <div class="appointment-time">
                                            <i class="fas fa-clock"></i>
                                            <span><?php echo substr($rdv['heure_debut'], 0, 5); ?></span>
                                        </div>
                                    </div>
                                    <div>
                                        <?php if ($rdv['statut'] === 'confirme'): ?>
                                            <span class="badge bg-success">Confirmé</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">En attente</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="rendez-vous.php" class="btn btn-primary btn-action">
                                        Voir tous les rendez-vous
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>Aucun rendez-vous à venir</p>
                                    <a href="nouveau-rdv.php" class="btn btn-primary btn-action mt-3">
                                        <i class="fas fa-plus"></i> Prendre un rendez-vous
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Dernières consultations -->
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <i class="fas fa-history"></i> Dernières consultations
                        </div>
                        <div class="card-body">
                            <?php if (count($dernieres_consultations) > 0): ?>
                                <?php foreach ($dernieres_consultations as $consult): 
                                    $date = new DateTime($consult['date_consultation']);
                                ?>
                                <div style="padding: 15px; background: #f8f9fa; border-radius: 8px; margin-bottom: 10px;">
                                    <h6 style="margin: 0 0 5px 0; font-size: 14px;">
                                        Dr. <?php echo htmlspecialchars($consult['medecin_nom']); ?>
                                    </h6>
                                    <p style="margin: 0; font-size: 12px; color: #666;">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo $date->format('d/m/Y'); ?>
                                    </p>
                                    <p style="margin: 5px 0 0 0; font-size: 12px; color: #666;">
                                        <?php echo htmlspecialchars($consult['specialite']); ?>
                                    </p>
                                </div>
                                <?php endforeach; ?>
                                
                                <div class="text-center mt-3">
                                    <a href="consultations.php" class="btn btn-outline-primary btn-sm">
                                        Voir tout l'historique
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-folder-open"></i>
                                    <p style="font-size: 13px;">Aucune consultation</p>
                                </div>
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