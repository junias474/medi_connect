<?php
/**
 * Page de déconnexion
 * Application de Consultation Médicale
 */

require_once 'config.php';

// Enregistrer l'activité de déconnexion si l'utilisateur est connecté
if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO logs_activite (utilisateur_id, action, description, adresse_ip, user_agent)
            VALUES (:user_id, 'deconnexion', 'Déconnexion', :ip, :user_agent)
        ");
        
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Inconnue',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Inconnu'
        ]);
    } catch(PDOException $e) {
        error_log("Erreur lors de l'enregistrement de la déconnexion : " . $e->getMessage());
    }
}

// Détruire toutes les variables de session
$_SESSION = array();

// Détruire le cookie de session si il existe
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Détruire la session
session_destroy();

// Rediriger vers la page de connexion avec un message
header("Location: login.php?message=deconnecte");
exit();
?>