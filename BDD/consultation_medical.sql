-- =====================================================
-- BASE DE DONNÉES - APPLICATION DE CONSULTATION MÉDICALE
-- Version 1.0 - Projet BEKONO
-- =====================================================

DROP DATABASE IF EXISTS consultation_medicale;
CREATE DATABASE consultation_medicale CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE consultation_medicale;

-- =====================================================
-- TABLE PRINCIPALE : utilisateurs
-- =====================================================
CREATE TABLE utilisateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    telephone VARCHAR(20) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    genre ENUM('Homme', 'Femme', 'Autre') NOT NULL,
    role ENUM('patient', 'medecin', 'administrateur') NOT NULL DEFAULT 'patient',
    photo_profil VARCHAR(255) DEFAULT NULL,
    date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion DATETIME DEFAULT NULL,
    statut ENUM('actif', 'inactif', 'suspendu') DEFAULT 'actif',
    adresse TEXT DEFAULT NULL,
    ville VARCHAR(100) DEFAULT NULL,
    code_postal VARCHAR(10) DEFAULT NULL,
    pays VARCHAR(100) DEFAULT 'Cameroun',
    date_naissance DATE DEFAULT NULL,
    INDEX idx_email (email),
    INDEX idx_telephone (telephone),
    INDEX idx_role (role),
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : patients
-- =====================================================
CREATE TABLE patients (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT UNIQUE NOT NULL,
    numero_patient VARCHAR(50) UNIQUE NOT NULL,
    groupe_sanguin ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-') DEFAULT NULL,
    allergies TEXT DEFAULT NULL,
    maladies_chroniques TEXT DEFAULT NULL,
    personne_contact_nom VARCHAR(100) DEFAULT NULL,
    personne_contact_telephone VARCHAR(20) DEFAULT NULL,
    assurance_medicale VARCHAR(100) DEFAULT NULL,
    numero_assurance VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_numero_patient (numero_patient)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : medecins
-- =====================================================
CREATE TABLE medecins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT UNIQUE NOT NULL,
    numero_medecin VARCHAR(50) UNIQUE NOT NULL,
    specialite VARCHAR(100) NOT NULL,
    numero_ordre VARCHAR(100) UNIQUE NOT NULL,
    annees_experience INT DEFAULT 0,
    diplomes TEXT DEFAULT NULL,
    hopital_affiliation VARCHAR(200) DEFAULT NULL,
    description TEXT DEFAULT NULL,
    tarif_consultation DECIMAL(10,2) DEFAULT 0.00,
    langues_parlees VARCHAR(255) DEFAULT 'Français',
    disponible BOOLEAN DEFAULT TRUE,
    note_moyenne DECIMAL(3,2) DEFAULT 0.00,
    nombre_consultations INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_specialite (specialite),
    INDEX idx_disponible (disponible),
    INDEX idx_note (note_moyenne)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : administrateurs
-- =====================================================
CREATE TABLE administrateurs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT UNIQUE NOT NULL,
    niveau_acces ENUM('super_admin', 'admin', 'moderateur') DEFAULT 'admin',
    departement VARCHAR(100) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : symptomes
-- =====================================================
CREATE TABLE symptomes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    description TEXT NOT NULL,
    severite ENUM('leger', 'moyen', 'grave', 'urgent') NOT NULL,
    date_debut DATE NOT NULL,
    temperature DECIMAL(4,2) DEFAULT NULL,
    autres_details TEXT DEFAULT NULL,
    statut ENUM('nouveau', 'en_cours', 'traite') DEFAULT 'nouveau',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_statut (statut),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : rendez_vous
-- =====================================================
CREATE TABLE rendez_vous (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    date_rendez_vous DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    type_consultation ENUM('presentiel', 'teleconsultation') DEFAULT 'teleconsultation',
    motif TEXT NOT NULL,
    statut ENUM('en_attente', 'confirme', 'annule', 'termine', 'patient_absent') DEFAULT 'en_attente',
    notes_patient TEXT DEFAULT NULL,
    rappel_envoye BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_medecin (medecin_id),
    INDEX idx_date (date_rendez_vous),
    INDEX idx_statut (statut),
    INDEX idx_date_statut (date_rendez_vous, statut)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : consultations
-- =====================================================
CREATE TABLE consultations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rendez_vous_id INT UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    diagnostic TEXT DEFAULT NULL,
    prescription TEXT DEFAULT NULL,
    examens_demandes TEXT DEFAULT NULL,
    notes_medicales TEXT DEFAULT NULL,
    recommandations TEXT DEFAULT NULL,
    duree_consultation INT DEFAULT NULL,
    date_consultation DATETIME NOT NULL,
    statut ENUM('en_cours', 'termine') DEFAULT 'en_cours',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_patient (patient_id),
    INDEX idx_medecin (medecin_id),
    INDEX idx_date (date_consultation)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : prescriptions
-- =====================================================
CREATE TABLE prescriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    consultation_id INT NOT NULL,
    medicament VARCHAR(200) NOT NULL,
    dosage VARCHAR(100) NOT NULL,
    frequence VARCHAR(100) NOT NULL,
    duree VARCHAR(50) NOT NULL,
    instructions TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,
    INDEX idx_consultation (consultation_id)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : horaires_medecin
-- =====================================================
CREATE TABLE horaires_medecin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medecin_id INT NOT NULL,
    jour_semaine ENUM('Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche') NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    disponible BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_medecin (medecin_id),
    INDEX idx_jour (jour_semaine)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : indisponibilites_medecin
-- =====================================================
CREATE TABLE indisponibilites_medecin (
    id INT PRIMARY KEY AUTO_INCREMENT,
    medecin_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    motif VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    INDEX idx_medecin (medecin_id),
    INDEX idx_dates (date_debut, date_fin)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : messages
-- =====================================================
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    date_lecture DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_expediteur (expediteur_id),
    INDEX idx_destinataire (destinataire_id),
    INDEX idx_lu (lu),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : notifications
-- =====================================================
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT NOT NULL,
    type ENUM('rendez_vous', 'message', 'rappel', 'system', 'urgence') NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    lien VARCHAR(255) DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_lu (lu),
    INDEX idx_type (type)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : avis_evaluations
-- =====================================================
CREATE TABLE avis_evaluations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    medecin_id INT NOT NULL,
    consultation_id INT DEFAULT NULL,
    note INT NOT NULL CHECK (note >= 1 AND note <= 5),
    commentaire TEXT DEFAULT NULL,
    date_avis DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (medecin_id) REFERENCES medecins(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
    INDEX idx_medecin (medecin_id),
    INDEX idx_note (note)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : centres_sante
-- =====================================================
CREATE TABLE centres_sante (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(200) NOT NULL,
    adresse TEXT NOT NULL,
    ville VARCHAR(100) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    latitude DECIMAL(10, 8) DEFAULT NULL,
    longitude DECIMAL(11, 8) DEFAULT NULL,
    type_centre ENUM('hopital', 'clinique', 'cabinet', 'centre_sante') NOT NULL,
    services_disponibles TEXT DEFAULT NULL,
    horaires_ouverture TEXT DEFAULT NULL,
    urgences_24h BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ville (ville),
    INDEX idx_type (type_centre),
    INDEX idx_coords (latitude, longitude)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : paiements
-- =====================================================
CREATE TABLE paiements (
    id INT PRIMARY KEY AUTO_INCREMENT,
    consultation_id INT NOT NULL,
    patient_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mode_paiement ENUM('carte_bancaire', 'mobile_money', 'especes', 'assurance') NOT NULL,
    statut ENUM('en_attente', 'valide', 'echoue', 'rembourse') DEFAULT 'en_attente',
    reference_transaction VARCHAR(100) UNIQUE DEFAULT NULL,
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    INDEX idx_consultation (consultation_id),
    INDEX idx_patient (patient_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : logs_activite
-- =====================================================
CREATE TABLE logs_activite (
    id INT PRIMARY KEY AUTO_INCREMENT,
    utilisateur_id INT DEFAULT NULL,
    action VARCHAR(255) NOT NULL,
    description TEXT DEFAULT NULL,
    adresse_ip VARCHAR(45) DEFAULT NULL,
    user_agent TEXT DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_utilisateur (utilisateur_id),
    INDEX idx_date (created_at)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : documents_medicaux
-- =====================================================
CREATE TABLE documents_medicaux (
    id INT PRIMARY KEY AUTO_INCREMENT,
    patient_id INT NOT NULL,
    consultation_id INT DEFAULT NULL,
    type_document ENUM('ordonnance', 'resultat_examen', 'rapport', 'autre') NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    taille_fichier INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patients(id) ON DELETE CASCADE,
    FOREIGN KEY (consultation_id) REFERENCES consultations(id) ON DELETE SET NULL,
    INDEX idx_patient (patient_id),
    INDEX idx_type (type_document)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : sessions_visio
-- =====================================================
CREATE TABLE sessions_visio (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rendez_vous_id INT UNIQUE NOT NULL,
    lien_session VARCHAR(500) NOT NULL,
    id_session VARCHAR(255) UNIQUE NOT NULL,
    statut ENUM('planifie', 'en_cours', 'termine', 'annule') DEFAULT 'planifie',
    duree_effective INT DEFAULT NULL,
    date_debut DATETIME DEFAULT NULL,
    date_fin DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous(id) ON DELETE CASCADE,
    INDEX idx_statut (statut)
) ENGINE=InnoDB;

-- =====================================================
-- TABLE : parametres_systeme
-- =====================================================
CREATE TABLE parametres_systeme (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cle_parametre VARCHAR(100) UNIQUE NOT NULL,
    valeur_parametre TEXT NOT NULL,
    description TEXT DEFAULT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- VUES
-- =====================================================

CREATE VIEW vue_medecins_complets AS
SELECT 
    m.id,
    m.numero_medecin,
    u.nom,
    u.prenom,
    u.email,
    u.telephone,
    u.ville,
    m.specialite,
    m.annees_experience,
    m.tarif_consultation,
    m.disponible,
    m.note_moyenne,
    m.nombre_consultations,
    m.langues_parlees
FROM medecins m
INNER JOIN utilisateurs u ON m.utilisateur_id = u.id
WHERE u.statut = 'actif';

CREATE VIEW vue_patients_complets AS
SELECT 
    p.id,
    p.numero_patient,
    u.nom,
    u.prenom,
    u.email,
    u.telephone,
    u.date_naissance,
    u.ville,
    p.groupe_sanguin,
    p.allergies,
    p.maladies_chroniques
FROM patients p
INNER JOIN utilisateurs u ON p.utilisateur_id = u.id
WHERE u.statut = 'actif';

CREATE VIEW vue_rendez_vous_complets AS
SELECT 
    rv.id,
    rv.date_rendez_vous,
    rv.heure_debut,
    rv.heure_fin,
    rv.type_consultation,
    rv.statut,
    rv.motif,
    rv.patient_id,
    rv.medecin_id,
    CONCAT(up.nom, ' ', up.prenom) AS patient_nom,
    up.telephone AS patient_telephone,
    CONCAT(um.nom, ' ', um.prenom) AS medecin_nom,
    m.specialite AS medecin_specialite,
    um.telephone AS medecin_telephone
FROM rendez_vous rv
INNER JOIN patients p ON rv.patient_id = p.id
INNER JOIN utilisateurs up ON p.utilisateur_id = up.id
INNER JOIN medecins m ON rv.medecin_id = m.id
INNER JOIN utilisateurs um ON m.utilisateur_id = um.id;

-- =====================================================
-- PROCÉDURES STOCKÉES
-- =====================================================

DELIMITER //

CREATE PROCEDURE verifier_disponibilite_medecin(
    IN p_medecin_id INT,
    IN p_date DATE,
    IN p_heure_debut TIME,
    IN p_heure_fin TIME,
    OUT p_disponible BOOLEAN
)
BEGIN
    DECLARE nombre_conflits INT;
    
    SELECT COUNT(*) INTO nombre_conflits
    FROM rendez_vous
    WHERE medecin_id = p_medecin_id
    AND date_rendez_vous = p_date
    AND statut IN ('en_attente', 'confirme')
    AND (
        (heure_debut BETWEEN p_heure_debut AND p_heure_fin)
        OR (heure_fin BETWEEN p_heure_debut AND p_heure_fin)
        OR (p_heure_debut BETWEEN heure_debut AND heure_fin)
    );
    
    SET p_disponible = (nombre_conflits = 0);
END //

CREATE PROCEDURE calculer_note_medecin(IN p_medecin_id INT)
BEGIN
    UPDATE medecins
    SET note_moyenne = (
        SELECT COALESCE(AVG(note), 0)
        FROM avis_evaluations
        WHERE medecin_id = p_medecin_id
    )
    WHERE id = p_medecin_id;
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

CREATE TRIGGER before_insert_patient
BEFORE INSERT ON patients
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_patient, 10) AS UNSIGNED)), 0) + 1 INTO next_num FROM patients;
    SET NEW.numero_patient = CONCAT('PAT-', YEAR(CURRENT_DATE), '-', LPAD(next_num, 3, '0'));
END //

CREATE TRIGGER before_insert_medecin
BEFORE INSERT ON medecins
FOR EACH ROW
BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_medecin, 10) AS UNSIGNED)), 0) + 1 INTO next_num FROM medecins;
    SET NEW.numero_medecin = CONCAT('MED-', YEAR(CURRENT_DATE), '-', LPAD(next_num, 3, '0'));
END //

CREATE TRIGGER after_insert_consultation
AFTER INSERT ON consultations
FOR EACH ROW
BEGIN
    UPDATE medecins
    SET nombre_consultations = nombre_consultations + 1
    WHERE id = NEW.medecin_id;
END //

DELIMITER ;

-- =====================================================
-- DONNÉES INITIALES (5 entrées maximum)
-- =====================================================

-- 1 Administrateur
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, genre, role, statut) 
VALUES ('Admin', 'Système', 'admin@consultation-medicale.cm', '+237600000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'administrateur', 'actif');

INSERT INTO administrateurs (utilisateur_id, niveau_acces) 
VALUES (LAST_INSERT_ID(), 'super_admin');

-- 2 Médecins
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, genre, role, statut, ville) 
VALUES 
('Dupont', 'Jean', 'dr.dupont@medical.cm', '+237677001122', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'medecin', 'actif', 'Yaoundé'),
('Kamga', 'Marie', 'dr.kamga@medical.cm', '+237677003344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Femme', 'medecin', 'actif', 'Douala');

INSERT INTO medecins (utilisateur_id, specialite, numero_ordre, annees_experience, tarif_consultation) 
VALUES 
(2, 'Médecine Générale', 'ORD-CM-12345', 15, 15000.00),
(3, 'Pédiatrie', 'ORD-CM-12346', 10, 20000.00);

-- 2 Patients
INSERT INTO utilisateurs (nom, prenom, email, telephone, mot_de_passe, genre, role, statut, ville, date_naissance) 
VALUES 
('Nkono', 'Alice', 'alice.nkono@email.cm', '+237655111222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Femme', 'patient', 'actif', 'Yaoundé', '1990-05-15'),
('Biya', 'Thomas', 'thomas.biya@email.cm', '+237655333444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'patient', 'actif', 'Douala', '1985-08-22');

INSERT INTO patients (utilisateur_id, groupe_sanguin) 
VALUES 
(4, 'A+'),
(5, 'O+');

-- Paramètres système
INSERT INTO parametres_systeme (cle_parametre, valeur_parametre, description) 
VALUES 
('duree_rendez_vous_defaut', '30', 'Durée par défaut d\'un rendez-vous en minutes'),
('version_app', '1.0', 'Version actuelle de l\'application');

-- =====================================================
-- FIN DU SCRIPT
-- =====================================================