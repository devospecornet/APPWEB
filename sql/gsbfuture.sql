CREATE DATABASE IF NOT EXISTS gsbfuture CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gsbfuture;

DROP TABLE IF EXISTS api_jetons;
DROP TABLE IF EXISTS journal_actions;
DROP TABLE IF EXISTS justificatifs;
DROP TABLE IF EXISTS hors_forfaits;
DROP TABLE IF EXISTS fiches_frais;
DROP TABLE IF EXISTS utilisateurs;

CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    mdp VARCHAR(255) NOT NULL,
    role ENUM('visiteur', 'comptable', 'administrateur') NOT NULL DEFAULT 'visiteur',
    est_approuve TINYINT(1) NOT NULL DEFAULT 1,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fiches_frais (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_fiche VARCHAR(20) NOT NULL UNIQUE,
    id_utilisateur INT NOT NULL,
    mois CHAR(7) NOT NULL,
    frais_essence DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frais_petit_dejeuner DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frais_repas_midi DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frais_repas_soir DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    frais_hotel DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    taux_tva_essence DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    taux_tva_petit_dejeuner DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    taux_tva_repas_midi DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    taux_tva_repas_soir DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    taux_tva_hotel DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    montant_total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    statut ENUM('saisie', 'transmise', 'validee', 'refusee', 'remboursee') NOT NULL DEFAULT 'saisie',
    commentaire_visiteur TEXT NULL,
    commentaire_comptable TEXT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_modification DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_fiches_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    CONSTRAINT uc_fiche_utilisateur_mois UNIQUE (id_utilisateur, mois)
) ENGINE=InnoDB;

CREATE TABLE hors_forfaits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_fiche INT NOT NULL,
    type_consommation VARCHAR(50) NOT NULL DEFAULT 'hors_forfait',
    date DATE NOT NULL,
    libelle VARCHAR(255) NOT NULL,
    montant DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    commentaire VARCHAR(255) NULL,
    taux_tva DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    montant_ht DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_tva DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_ttc DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    date_ajout DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_hors_forfaits_fiche FOREIGN KEY (id_fiche) REFERENCES fiches_frais(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE justificatifs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_fiche INT NOT NULL,
    nom_reel VARCHAR(255) NOT NULL,
    nom_serveur VARCHAR(255) NOT NULL,
    extension VARCHAR(10) NOT NULL,
    contient_tva TINYINT(1) NOT NULL DEFAULT 0,
    montant_ttc_5 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_ttc_10 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    montant_ttc_20 DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_justificatifs_fiche FOREIGN KEY (id_fiche) REFERENCES fiches_frais(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE journal_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NULL,
    role_utilisateur VARCHAR(50) NOT NULL DEFAULT 'invite',
    action VARCHAR(100) NOT NULL,
    type_objet VARCHAR(100) NOT NULL,
    id_objet INT NULL,
    details TEXT NOT NULL,
    niveau ENUM('info', 'warning', 'error') NOT NULL DEFAULT 'info',
    date_action DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_journal_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL,
    INDEX idx_journal_action (action),
    INDEX idx_journal_type_objet (type_objet),
    INDEX idx_journal_date_action (date_action)
) ENGINE=InnoDB;

CREATE TABLE api_jetons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    jeton VARCHAR(128) NOT NULL UNIQUE,
    date_expiration DATETIME NOT NULL,
    date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_api_jetons_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO utilisateurs (nom, prenom, email, mdp, role, est_approuve) VALUES
('Admin', 'GSB', 'admin@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'administrateur', 1),
('Comptable', 'GSB', 'comptable@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'comptable', 1),
('Visiteur', 'GSB', 'visiteur@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'visiteur', 1);

INSERT INTO journal_actions (id_utilisateur, role_utilisateur, action, type_objet, id_objet, details, niveau)
VALUES
(1, 'administrateur', 'INITIALISATION', 'base_de_donnees', NULL, 'Base jsb_future initialisée avec les comptes de démonstration.', 'info');
