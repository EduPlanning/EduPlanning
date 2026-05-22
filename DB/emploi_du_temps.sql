-- phpMyAdmin SQL Dump
-- Gestion d'Emploi du Temps — PFE 2025-2026
-- Base de données : `emploi_du_temps`

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Base de données : `emploi_du_temps`
-- --------------------------------------------------------

CREATE DATABASE IF NOT EXISTS `emploi_du_temps`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE `emploi_du_temps`;

-- --------------------------------------------------------
-- Table `utilisateur`
-- --------------------------------------------------------

CREATE TABLE `utilisateur` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`        VARCHAR(100) NOT NULL,
  `prenom`     VARCHAR(100) NOT NULL,
  `email`      VARCHAR(200) NOT NULL,
  `mot_de_passe` VARCHAR(255) NOT NULL,
  `groupe_id`  INT(11)      DEFAULT NULL,
  `role`       ENUM('administrateur','enseignant','etudiant') NOT NULL DEFAULT 'etudiant',
  `actif`      TINYINT(1)   NOT NULL DEFAULT 1,
  `cree_le`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
  ,KEY `groupe_id` (`groupe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `filiere`
-- --------------------------------------------------------

CREATE TABLE `filiere` (
  `id`     INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`    VARCHAR(150) NOT NULL,
  `code`   VARCHAR(20)  NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `groupe`
-- --------------------------------------------------------

CREATE TABLE `groupe` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`        VARCHAR(100) NOT NULL,
  `niveau`     VARCHAR(50)  NOT NULL,
  `filiere_id` INT(11)      NOT NULL,
  `capacite`   INT(11)      NOT NULL DEFAULT 30,
  PRIMARY KEY (`id`),
  KEY `filiere_id` (`filiere_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `salle`
-- --------------------------------------------------------

CREATE TABLE `salle` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`         VARCHAR(100) NOT NULL,
  `capacite`    INT(11)      NOT NULL DEFAULT 30,
  `equipements` TEXT,
  `disponible`  TINYINT(1)   NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `matiere`
-- --------------------------------------------------------

CREATE TABLE `matiere` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `nom`           VARCHAR(150) NOT NULL,
  `code`          VARCHAR(20)  NOT NULL,
  `volume_horaire` INT(11)     NOT NULL DEFAULT 0,
  `coefficient`   FLOAT        NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `enseignant`
-- --------------------------------------------------------

CREATE TABLE `enseignant` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT(11)     NOT NULL,
  `specialite`    VARCHAR(150),
  `disponibilites` TEXT COMMENT 'JSON des créneaux de disponibilité',
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `creneau`
-- --------------------------------------------------------

CREATE TABLE `creneau` (
  `id`            INT(11)   NOT NULL AUTO_INCREMENT,
  `date_cours`    DATE      NOT NULL,
  `heure_debut`   TIME      NOT NULL,
  `heure_fin`     TIME      NOT NULL,
  `matiere_id`    INT(11)   NOT NULL,
  `enseignant_id` INT(11)   NOT NULL,
  `salle_id`      INT(11)   NOT NULL,
  `groupe_id`     INT(11)   NOT NULL,
  `type`          ENUM('cours','td','tp','examen') NOT NULL DEFAULT 'cours',
  `recurrent`     TINYINT(1) NOT NULL DEFAULT 0,
  `freq_recurrence` ENUM('hebdomadaire','bi_mensuel') DEFAULT NULL,
  `date_fin_recurrence` DATE DEFAULT NULL,
  `cree_le`       DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modifie_le`    DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `matiere_id`    (`matiere_id`),
  KEY `enseignant_id` (`enseignant_id`),
  KEY `salle_id`      (`salle_id`),
  KEY `groupe_id`     (`groupe_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `notification`
-- --------------------------------------------------------

CREATE TABLE `notification` (
  `id`             INT(11)  NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT(11)  NOT NULL,
  `message`        TEXT     NOT NULL,
  `lu`             TINYINT(1) NOT NULL DEFAULT 0,
  `cree_le`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Table `historique`
-- --------------------------------------------------------

CREATE TABLE `historique` (
  `id`             INT(11)  NOT NULL AUTO_INCREMENT,
  `utilisateur_id` INT(11)  NOT NULL,
  `action`         VARCHAR(100) NOT NULL,
  `details`        TEXT,
  `cree_le`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Contraintes de clés étrangères
-- --------------------------------------------------------

ALTER TABLE `groupe`
  ADD CONSTRAINT `fk_groupe_filiere`
    FOREIGN KEY (`filiere_id`) REFERENCES `filiere` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `enseignant`
  ADD CONSTRAINT `fk_enseignant_utilisateur`
    FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `utilisateur`
  ADD CONSTRAINT `fk_utilisateur_groupe`
    FOREIGN KEY (`groupe_id`) REFERENCES `groupe` (`id`)
    ON DELETE SET NULL ON UPDATE CASCADE;

ALTER TABLE `creneau`
  ADD CONSTRAINT `fk_creneau_matiere`
    FOREIGN KEY (`matiere_id`) REFERENCES `matiere` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_creneau_enseignant`
    FOREIGN KEY (`enseignant_id`) REFERENCES `enseignant` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_creneau_salle`
    FOREIGN KEY (`salle_id`) REFERENCES `salle` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_creneau_groupe`
    FOREIGN KEY (`groupe_id`) REFERENCES `groupe` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `notification`
  ADD CONSTRAINT `fk_notification_utilisateur`
    FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `historique`
  ADD CONSTRAINT `fk_historique_utilisateur`
    FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------
-- Table `proposition`
-- --------------------------------------------------------

CREATE TABLE `proposition` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `auteur_id` INT(11) NOT NULL,
  `resource` VARCHAR(100) NOT NULL,
  `action` VARCHAR(20) NOT NULL,
  `cible_id` INT(11) DEFAULT NULL,
  `payload` JSON NOT NULL,
  `status` ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `cree_le` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `proposition`
  ADD CONSTRAINT `fk_proposition_auteur` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateur`(`id`) ON DELETE CASCADE ON UPDATE CASCADE;

-- --------------------------------------------------------
-- Données de démo
-- --------------------------------------------------------

-- Filières
INSERT INTO `filiere` (`nom`, `code`) VALUES
('Développement Informatique', 'DI'),
('Gestion des Entreprises', 'GE'),
('Réseaux et Télécommunications', 'RT');

-- Salles
INSERT INTO `salle` (`nom`, `capacite`, `equipements`, `disponible`) VALUES
('Salle A101', 30, 'Tableau, Projecteur', 1),
('Salle A102', 30, 'Tableau, Projecteur', 1),
('Salle B201', 40, 'Tableau, Projecteur, Climatisation', 1),
('Labo Informatique 1', 25, 'PCs, Tableau, Projecteur', 1),
('Labo Informatique 2', 25, 'PCs, Tableau', 1);

-- Matières
INSERT INTO `matiere` (`nom`, `code`, `volume_horaire`, `coefficient`) VALUES
('Algorithmes et Structures de Données', 'ASD', 60, 3),
('Développement Web', 'DW', 80, 4),
('Base de Données', 'BD', 60, 3),
('Programmation Orientée Objet', 'POO', 60, 3),
('Réseaux Informatiques', 'RI', 40, 2),
('Mathématiques Appliquées', 'MATH', 40, 2),
('Communication et Anglais', 'CA', 30, 1);

-- Utilisateurs (admin + enseignants + étudiants) password: 123456789
INSERT INTO `utilisateur` (`nom`, `prenom`, `email`, `mot_de_passe`, `role`, `actif`) VALUES
('Admin', 'Système', 'admin@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'administrateur', 1),
('Benali', 'Youssef', 'y.benali@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'enseignant', 1),
('Amine', 'Fatima', 'f.amine@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'enseignant', 1),
('Idrissi', 'Mohammed', 'm.idrissi@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'enseignant', 1),
('Elbouraqqady', 'Nouhaila', 'n.elbouraqqady@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'etudiant', 1),
('Tazi', 'Hamza', 'h.tazi@ecole.ma', '$2y$10$ji5xBThTAzMf1/oYSaN/NOWQEQqIlhShkdc9zUr/jLwTmrkun3z9O', 'etudiant', 1);

-- Enseignants
INSERT INTO `enseignant` (`utilisateur_id`, `specialite`) VALUES
(2, 'Informatique - Algorithmes'),
(3, 'Développement Web et Mobile'),
(4, 'Bases de Données et Réseaux');

-- Groupes
INSERT INTO `groupe` (`nom`, `niveau`, `filiere_id`, `capacite`) VALUES
('DI-TS1-A', 'Technicien Spécialisé 1ère année', 1, 25),
('DI-TS1-B', 'Technicien Spécialisé 1ère année', 1, 25),
('DI-TS2', 'Technicien Spécialisé 2ème année', 1, 30),
('GE-TS1', 'Technicien Spécialisé 1ère année', 2, 30);

COMMIT;
