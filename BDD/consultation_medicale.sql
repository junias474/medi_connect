-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : jeu. 02 avr. 2026 à 02:19
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `consultation_medicale`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE `administrateurs` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `niveau_acces` enum('super_admin','admin','moderateur') DEFAULT 'admin',
  `departement` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id`, `utilisateur_id`, `niveau_acces`, `departement`, `created_at`, `updated_at`) VALUES
(1, 1, 'super_admin', NULL, '2026-02-13 11:17:48', '2026-02-13 11:17:48');

-- --------------------------------------------------------

--
-- Structure de la table `avis_evaluations`
--

CREATE TABLE `avis_evaluations` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `medecin_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `note` int(11) NOT NULL CHECK (`note` >= 1 and `note` <= 5),
  `commentaire` text DEFAULT NULL,
  `date_avis` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `centres_sante`
--

CREATE TABLE `centres_sante` (
  `id` int(11) NOT NULL,
  `nom` varchar(200) NOT NULL,
  `adresse` text NOT NULL,
  `ville` varchar(100) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `type_centre` enum('hopital','clinique','cabinet','centre_sante') NOT NULL,
  `services_disponibles` text DEFAULT NULL,
  `horaires_ouverture` text DEFAULT NULL,
  `urgences_24h` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `consultations`
--

CREATE TABLE `consultations` (
  `id` int(11) NOT NULL,
  `rendez_vous_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `medecin_id` int(11) NOT NULL,
  `diagnostic` text DEFAULT NULL,
  `prescription` text DEFAULT NULL,
  `examens_demandes` text DEFAULT NULL,
  `notes_medicales` text DEFAULT NULL,
  `recommandations` text DEFAULT NULL,
  `duree_consultation` int(11) DEFAULT NULL,
  `date_consultation` datetime NOT NULL,
  `statut` enum('en_cours','termine') DEFAULT 'en_cours',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déclencheurs `consultations`
--
DELIMITER $$
CREATE TRIGGER `after_insert_consultation` AFTER INSERT ON `consultations` FOR EACH ROW BEGIN
    UPDATE medecins
    SET nombre_consultations = nombre_consultations + 1
    WHERE id = NEW.medecin_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `documents_medicaux`
--

CREATE TABLE `documents_medicaux` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `consultation_id` int(11) DEFAULT NULL,
  `type_document` enum('ordonnance','resultat_examen','rapport','autre') NOT NULL,
  `nom_fichier` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille_fichier` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `horaires_medecin`
--

CREATE TABLE `horaires_medecin` (
  `id` int(11) NOT NULL,
  `medecin_id` int(11) NOT NULL,
  `jour_semaine` enum('Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche') NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `disponible` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `indisponibilites_medecin`
--

CREATE TABLE `indisponibilites_medecin` (
  `id` int(11) NOT NULL,
  `medecin_id` int(11) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `motif` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `logs_activite`
--

CREATE TABLE `logs_activite` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `adresse_ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `logs_activite`
--

INSERT INTO `logs_activite` (`id`, `utilisateur_id`, `action`, `description`, `adresse_ip`, `user_agent`, `created_at`) VALUES
(1, 6, 'connexion', 'Connexion réussie', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:147.0) Gecko/20100101 Firefox/147.0', '2026-02-13 12:26:43'),
(2, 6, 'connexion', 'Connexion réussie', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-03-17 12:54:52'),
(3, 6, 'connexion', 'Successful login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-04-02 01:03:17'),
(4, 6, 'deconnexion', 'Logout', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:148.0) Gecko/20100101 Firefox/148.0', '2026-04-02 01:08:47');

-- --------------------------------------------------------

--
-- Structure de la table `medecins`
--

CREATE TABLE `medecins` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `numero_medecin` varchar(50) NOT NULL,
  `specialite` varchar(100) NOT NULL,
  `numero_ordre` varchar(100) NOT NULL,
  `annees_experience` int(11) DEFAULT 0,
  `diplomes` text DEFAULT NULL,
  `hopital_affiliation` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `tarif_consultation` decimal(10,2) DEFAULT 0.00,
  `langues_parlees` varchar(255) DEFAULT 'Français',
  `disponible` tinyint(1) DEFAULT 1,
  `note_moyenne` decimal(3,2) DEFAULT 0.00,
  `nombre_consultations` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `medecins`
--

INSERT INTO `medecins` (`id`, `utilisateur_id`, `numero_medecin`, `specialite`, `numero_ordre`, `annees_experience`, `diplomes`, `hopital_affiliation`, `description`, `tarif_consultation`, `langues_parlees`, `disponible`, `note_moyenne`, `nombre_consultations`, `created_at`, `updated_at`) VALUES
(1, 2, 'MED-2026-001', 'Médecine Générale', 'ORD-CM-12345', 15, NULL, NULL, NULL, 15000.00, 'Français', 1, 0.00, 0, '2026-02-13 11:17:48', '2026-02-13 11:17:48'),
(2, 3, 'MED-2026-002', 'Pédiatrie', 'ORD-CM-12346', 10, NULL, NULL, NULL, 20000.00, 'Français', 1, 0.00, 0, '2026-02-13 11:17:48', '2026-02-13 11:17:48');

--
-- Déclencheurs `medecins`
--
DELIMITER $$
CREATE TRIGGER `before_insert_medecin` BEFORE INSERT ON `medecins` FOR EACH ROW BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_medecin, 10) AS UNSIGNED)), 0) + 1 INTO next_num FROM medecins;
    SET NEW.numero_medecin = CONCAT('MED-', YEAR(CURRENT_DATE), '-', LPAD(next_num, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `destinataire_id` int(11) NOT NULL,
  `sujet` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `date_lecture` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `type` enum('rendez_vous','message','rappel','system','urgence') NOT NULL,
  `titre` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `lu` tinyint(1) DEFAULT 0,
  `lien` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `mode_paiement` enum('carte_bancaire','mobile_money','especes','assurance') NOT NULL,
  `statut` enum('en_attente','valide','echoue','rembourse') DEFAULT 'en_attente',
  `reference_transaction` varchar(100) DEFAULT NULL,
  `date_paiement` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `parametres_systeme`
--

CREATE TABLE `parametres_systeme` (
  `id` int(11) NOT NULL,
  `cle_parametre` varchar(100) NOT NULL,
  `valeur_parametre` text NOT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `parametres_systeme`
--

INSERT INTO `parametres_systeme` (`id`, `cle_parametre`, `valeur_parametre`, `description`, `updated_at`) VALUES
(1, 'duree_rendez_vous_defaut', '30', 'Durée par défaut d\'un rendez-vous en minutes', '2026-02-13 11:17:48'),
(2, 'version_app', '1.0', 'Version actuelle de l\'application', '2026-02-13 11:17:48');

-- --------------------------------------------------------

--
-- Structure de la table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `numero_patient` varchar(50) NOT NULL,
  `groupe_sanguin` enum('A+','A-','B+','B-','AB+','AB-','O+','O-') DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `maladies_chroniques` text DEFAULT NULL,
  `personne_contact_nom` varchar(100) DEFAULT NULL,
  `personne_contact_telephone` varchar(20) DEFAULT NULL,
  `assurance_medicale` varchar(100) DEFAULT NULL,
  `numero_assurance` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `patients`
--

INSERT INTO `patients` (`id`, `utilisateur_id`, `numero_patient`, `groupe_sanguin`, `allergies`, `maladies_chroniques`, `personne_contact_nom`, `personne_contact_telephone`, `assurance_medicale`, `numero_assurance`, `created_at`, `updated_at`) VALUES
(1, 4, 'PAT-2026-001', 'A+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 11:17:48', '2026-02-13 11:17:48'),
(2, 5, 'PAT-2026-002', 'O+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 11:17:48', '2026-02-13 11:17:48'),
(3, 6, 'PAT-2026-003', 'O+', NULL, NULL, NULL, NULL, NULL, NULL, '2026-02-13 11:54:46', '2026-02-13 11:54:46');

--
-- Déclencheurs `patients`
--
DELIMITER $$
CREATE TRIGGER `before_insert_patient` BEFORE INSERT ON `patients` FOR EACH ROW BEGIN
    DECLARE next_num INT;
    SELECT COALESCE(MAX(CAST(SUBSTRING(numero_patient, 10) AS UNSIGNED)), 0) + 1 INTO next_num FROM patients;
    SET NEW.numero_patient = CONCAT('PAT-', YEAR(CURRENT_DATE), '-', LPAD(next_num, 3, '0'));
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `consultation_id` int(11) NOT NULL,
  `medicament` varchar(200) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequence` varchar(100) NOT NULL,
  `duree` varchar(50) NOT NULL,
  `instructions` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendez_vous`
--

CREATE TABLE `rendez_vous` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `medecin_id` int(11) NOT NULL,
  `date_rendez_vous` date NOT NULL,
  `heure_debut` time NOT NULL,
  `heure_fin` time NOT NULL,
  `type_consultation` enum('presentiel','teleconsultation') DEFAULT 'teleconsultation',
  `motif` text NOT NULL,
  `statut` enum('en_attente','confirme','annule','termine','patient_absent') DEFAULT 'en_attente',
  `notes_patient` text DEFAULT NULL,
  `rappel_envoye` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `sessions_visio`
--

CREATE TABLE `sessions_visio` (
  `id` int(11) NOT NULL,
  `rendez_vous_id` int(11) NOT NULL,
  `lien_session` varchar(500) NOT NULL,
  `id_session` varchar(255) NOT NULL,
  `statut` enum('planifie','en_cours','termine','annule') DEFAULT 'planifie',
  `duree_effective` int(11) DEFAULT NULL,
  `date_debut` datetime DEFAULT NULL,
  `date_fin` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `symptomes`
--

CREATE TABLE `symptomes` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `severite` enum('leger','moyen','grave','urgent') NOT NULL,
  `date_debut` date NOT NULL,
  `temperature` decimal(4,2) DEFAULT NULL,
  `autres_details` text DEFAULT NULL,
  `statut` enum('nouveau','en_cours','traite') DEFAULT 'nouveau',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `genre` enum('Homme','Femme','Autre') NOT NULL,
  `role` enum('patient','medecin','administrateur') NOT NULL DEFAULT 'patient',
  `photo_profil` varchar(255) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `derniere_connexion` datetime DEFAULT NULL,
  `statut` enum('actif','inactif','suspendu') DEFAULT 'actif',
  `adresse` text DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `code_postal` varchar(10) DEFAULT NULL,
  `pays` varchar(100) DEFAULT 'Cameroun',
  `date_naissance` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `telephone`, `mot_de_passe`, `genre`, `role`, `photo_profil`, `date_inscription`, `derniere_connexion`, `statut`, `adresse`, `ville`, `code_postal`, `pays`, `date_naissance`) VALUES
(1, 'Admin', 'Système', 'admin@consultation-medicale.cm', '+237600000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'administrateur', NULL, '2026-02-13 11:17:47', NULL, 'actif', NULL, NULL, NULL, 'Cameroun', NULL),
(2, 'Dupont', 'Jean', 'dr.dupont@medical.cm', '+237677001122', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'medecin', NULL, '2026-02-13 11:17:48', NULL, 'actif', NULL, 'Yaoundé', NULL, 'Cameroun', NULL),
(3, 'Kamga', 'Marie', 'dr.kamga@medical.cm', '+237677003344', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Femme', 'medecin', NULL, '2026-02-13 11:17:48', NULL, 'actif', NULL, 'Douala', NULL, 'Cameroun', NULL),
(4, 'Nkono', 'Alice', 'alice.nkono@email.cm', '+237655111222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Femme', 'patient', NULL, '2026-02-13 11:17:48', NULL, 'actif', NULL, 'Yaoundé', NULL, 'Cameroun', '1990-05-15'),
(5, 'Biya', 'Thomas', 'thomas.biya@email.cm', '+237655333444', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Homme', 'patient', NULL, '2026-02-13 11:17:48', NULL, 'actif', NULL, 'Douala', NULL, 'Cameroun', '1985-08-22'),
(6, 'beding', 'junias', 'bedingjunias474@gmail.com', '+237696628941', '$2y$10$73l61JZOssjQwJESnTj4TO6aIzBl.qDL5dcw/M5JUVJM4XLYp2NqC', 'Homme', 'patient', NULL, '2026-02-13 11:54:45', '2026-04-02 01:03:17', 'actif', NULL, 'bertoua', NULL, 'Cameroun', '2026-02-11');

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vue_medecins_complets`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vue_medecins_complets` (
`id` int(11)
,`numero_medecin` varchar(50)
,`nom` varchar(100)
,`prenom` varchar(100)
,`email` varchar(150)
,`telephone` varchar(20)
,`ville` varchar(100)
,`specialite` varchar(100)
,`annees_experience` int(11)
,`tarif_consultation` decimal(10,2)
,`disponible` tinyint(1)
,`note_moyenne` decimal(3,2)
,`nombre_consultations` int(11)
,`langues_parlees` varchar(255)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vue_patients_complets`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vue_patients_complets` (
`id` int(11)
,`numero_patient` varchar(50)
,`nom` varchar(100)
,`prenom` varchar(100)
,`email` varchar(150)
,`telephone` varchar(20)
,`date_naissance` date
,`ville` varchar(100)
,`groupe_sanguin` enum('A+','A-','B+','B-','AB+','AB-','O+','O-')
,`allergies` text
,`maladies_chroniques` text
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `vue_rendez_vous_complets`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `vue_rendez_vous_complets` (
`id` int(11)
,`date_rendez_vous` date
,`heure_debut` time
,`heure_fin` time
,`type_consultation` enum('presentiel','teleconsultation')
,`statut` enum('en_attente','confirme','annule','termine','patient_absent')
,`motif` text
,`patient_id` int(11)
,`medecin_id` int(11)
,`patient_nom` varchar(201)
,`patient_telephone` varchar(20)
,`medecin_nom` varchar(201)
,`medecin_specialite` varchar(100)
,`medecin_telephone` varchar(20)
);

-- --------------------------------------------------------

--
-- Structure de la vue `vue_medecins_complets`
--
DROP TABLE IF EXISTS `vue_medecins_complets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vue_medecins_complets`  AS SELECT `m`.`id` AS `id`, `m`.`numero_medecin` AS `numero_medecin`, `u`.`nom` AS `nom`, `u`.`prenom` AS `prenom`, `u`.`email` AS `email`, `u`.`telephone` AS `telephone`, `u`.`ville` AS `ville`, `m`.`specialite` AS `specialite`, `m`.`annees_experience` AS `annees_experience`, `m`.`tarif_consultation` AS `tarif_consultation`, `m`.`disponible` AS `disponible`, `m`.`note_moyenne` AS `note_moyenne`, `m`.`nombre_consultations` AS `nombre_consultations`, `m`.`langues_parlees` AS `langues_parlees` FROM (`medecins` `m` join `utilisateurs` `u` on(`m`.`utilisateur_id` = `u`.`id`)) WHERE `u`.`statut` = 'actif' ;

-- --------------------------------------------------------

--
-- Structure de la vue `vue_patients_complets`
--
DROP TABLE IF EXISTS `vue_patients_complets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vue_patients_complets`  AS SELECT `p`.`id` AS `id`, `p`.`numero_patient` AS `numero_patient`, `u`.`nom` AS `nom`, `u`.`prenom` AS `prenom`, `u`.`email` AS `email`, `u`.`telephone` AS `telephone`, `u`.`date_naissance` AS `date_naissance`, `u`.`ville` AS `ville`, `p`.`groupe_sanguin` AS `groupe_sanguin`, `p`.`allergies` AS `allergies`, `p`.`maladies_chroniques` AS `maladies_chroniques` FROM (`patients` `p` join `utilisateurs` `u` on(`p`.`utilisateur_id` = `u`.`id`)) WHERE `u`.`statut` = 'actif' ;

-- --------------------------------------------------------

--
-- Structure de la vue `vue_rendez_vous_complets`
--
DROP TABLE IF EXISTS `vue_rendez_vous_complets`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vue_rendez_vous_complets`  AS SELECT `rv`.`id` AS `id`, `rv`.`date_rendez_vous` AS `date_rendez_vous`, `rv`.`heure_debut` AS `heure_debut`, `rv`.`heure_fin` AS `heure_fin`, `rv`.`type_consultation` AS `type_consultation`, `rv`.`statut` AS `statut`, `rv`.`motif` AS `motif`, `rv`.`patient_id` AS `patient_id`, `rv`.`medecin_id` AS `medecin_id`, concat(`up`.`nom`,' ',`up`.`prenom`) AS `patient_nom`, `up`.`telephone` AS `patient_telephone`, concat(`um`.`nom`,' ',`um`.`prenom`) AS `medecin_nom`, `m`.`specialite` AS `medecin_specialite`, `um`.`telephone` AS `medecin_telephone` FROM ((((`rendez_vous` `rv` join `patients` `p` on(`rv`.`patient_id` = `p`.`id`)) join `utilisateurs` `up` on(`p`.`utilisateur_id` = `up`.`id`)) join `medecins` `m` on(`rv`.`medecin_id` = `m`.`id`)) join `utilisateurs` `um` on(`m`.`utilisateur_id` = `um`.`id`)) ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `avis_evaluations`
--
ALTER TABLE `avis_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `idx_medecin` (`medecin_id`),
  ADD KEY `idx_note` (`note`);

--
-- Index pour la table `centres_sante`
--
ALTER TABLE `centres_sante`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ville` (`ville`),
  ADD KEY `idx_type` (`type_centre`),
  ADD KEY `idx_coords` (`latitude`,`longitude`);

--
-- Index pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rendez_vous_id` (`rendez_vous_id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_medecin` (`medecin_id`),
  ADD KEY `idx_date` (`date_consultation`);

--
-- Index pour la table `documents_medicaux`
--
ALTER TABLE `documents_medicaux`
  ADD PRIMARY KEY (`id`),
  ADD KEY `consultation_id` (`consultation_id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_type` (`type_document`);

--
-- Index pour la table `horaires_medecin`
--
ALTER TABLE `horaires_medecin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_medecin` (`medecin_id`),
  ADD KEY `idx_jour` (`jour_semaine`);

--
-- Index pour la table `indisponibilites_medecin`
--
ALTER TABLE `indisponibilites_medecin`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_medecin` (`medecin_id`),
  ADD KEY `idx_dates` (`date_debut`,`date_fin`);

--
-- Index pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `medecins`
--
ALTER TABLE `medecins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD UNIQUE KEY `numero_medecin` (`numero_medecin`),
  ADD UNIQUE KEY `numero_ordre` (`numero_ordre`),
  ADD KEY `idx_specialite` (`specialite`),
  ADD KEY `idx_disponible` (`disponible`),
  ADD KEY `idx_note` (`note_moyenne`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_expediteur` (`expediteur_id`),
  ADD KEY `idx_destinataire` (`destinataire_id`),
  ADD KEY `idx_lu` (`lu`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_lu` (`lu`),
  ADD KEY `idx_type` (`type`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_transaction` (`reference_transaction`),
  ADD KEY `idx_consultation` (`consultation_id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `parametres_systeme`
--
ALTER TABLE `parametres_systeme`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cle_parametre` (`cle_parametre`);

--
-- Index pour la table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD UNIQUE KEY `numero_patient` (`numero_patient`),
  ADD KEY `idx_numero_patient` (`numero_patient`);

--
-- Index pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_consultation` (`consultation_id`);

--
-- Index pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_medecin` (`medecin_id`),
  ADD KEY `idx_date` (`date_rendez_vous`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date_statut` (`date_rendez_vous`,`statut`);

--
-- Index pour la table `sessions_visio`
--
ALTER TABLE `sessions_visio`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rendez_vous_id` (`rendez_vous_id`),
  ADD UNIQUE KEY `id_session` (`id_session`),
  ADD KEY `idx_statut` (`statut`);

--
-- Index pour la table `symptomes`
--
ALTER TABLE `symptomes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_patient` (`patient_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_date` (`created_at`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `telephone` (`telephone`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_telephone` (`telephone`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_statut` (`statut`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `avis_evaluations`
--
ALTER TABLE `avis_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `centres_sante`
--
ALTER TABLE `centres_sante`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `consultations`
--
ALTER TABLE `consultations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `documents_medicaux`
--
ALTER TABLE `documents_medicaux`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `horaires_medecin`
--
ALTER TABLE `horaires_medecin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `indisponibilites_medecin`
--
ALTER TABLE `indisponibilites_medecin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `medecins`
--
ALTER TABLE `medecins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `parametres_systeme`
--
ALTER TABLE `parametres_systeme`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `sessions_visio`
--
ALTER TABLE `sessions_visio`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `symptomes`
--
ALTER TABLE `symptomes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD CONSTRAINT `administrateurs_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `avis_evaluations`
--
ALTER TABLE `avis_evaluations`
  ADD CONSTRAINT `avis_evaluations_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_evaluations_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `avis_evaluations_ibfk_3` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `consultations`
--
ALTER TABLE `consultations`
  ADD CONSTRAINT `consultations_ibfk_1` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultations_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consultations_ibfk_3` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `documents_medicaux`
--
ALTER TABLE `documents_medicaux`
  ADD CONSTRAINT `documents_medicaux_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `documents_medicaux_ibfk_2` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `horaires_medecin`
--
ALTER TABLE `horaires_medecin`
  ADD CONSTRAINT `horaires_medecin_ibfk_1` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `indisponibilites_medecin`
--
ALTER TABLE `indisponibilites_medecin`
  ADD CONSTRAINT `indisponibilites_medecin_ibfk_1` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `logs_activite`
--
ALTER TABLE `logs_activite`
  ADD CONSTRAINT `logs_activite_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `medecins`
--
ALTER TABLE `medecins`
  ADD CONSTRAINT `medecins_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`destinataire_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `paiements_ibfk_2` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`consultation_id`) REFERENCES `consultations` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rendez_vous`
--
ALTER TABLE `rendez_vous`
  ADD CONSTRAINT `rendez_vous_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rendez_vous_ibfk_2` FOREIGN KEY (`medecin_id`) REFERENCES `medecins` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `sessions_visio`
--
ALTER TABLE `sessions_visio`
  ADD CONSTRAINT `sessions_visio_ibfk_1` FOREIGN KEY (`rendez_vous_id`) REFERENCES `rendez_vous` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `symptomes`
--
ALTER TABLE `symptomes`
  ADD CONSTRAINT `symptomes_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
