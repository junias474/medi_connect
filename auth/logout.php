<?php
/**
 * Logout Page
 * Medical Consultation Application
 */
require_once 'config.php';

// Log the logout activity if the user is connected
if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("
            INSERT INTO logs_activite (utilisateur_id, action, description, adresse_ip, user_agent)
            VALUES (:user_id, 'deconnexion', 'Logout', :ip, :user_agent)
        ");
        
        $stmt->execute([
            ':user_id' => $_SESSION['user_id'],
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? 'Unknown',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
        ]);
    } catch(PDOException $e) {
        error_log("Error logging logout: " . $e->getMessage());
    }
}

// Destroy all session variables
$_SESSION = array();

// Destroy the session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Redirect to login page with a message
header("Location: ../index.php?message=logged_out");
exit();
?>