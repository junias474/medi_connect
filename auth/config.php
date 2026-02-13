<?php
/**
 * Configuration de la base de données
 * Application de Consultation Médicale
 */

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'consultation_medicale');
define('DB_USER', 'root');
define('DB_PASS', '');

// Configuration de l'application
define('SITE_URL', 'http://localhost/consultation_medicale');
define('SITE_NAME', 'Consultation Médicale');

// Démarrer la session si elle n'est pas déjà démarrée
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Classe de connexion à la base de données
class Database {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion à la base de données : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Empêcher le clonage
    private function __clone() {}
    
    // Empêcher la désérialisation
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Fonction pour nettoyer les données
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

// Fonction pour rediriger selon le rôle
function redirectToDashboard($role) {
    switch($role) {
        case 'patient':
            header("Location: dashboard/patient/index.php");
            break;
        case 'medecin':
            header("Location: dashboard/medecin/index.php");
            break;
        case 'administrateur':
            header("Location: dashboard/admin/index.php");
            break;
        default:
            header("Location: index.php");
    }
    exit();
}
?>