-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mar. 21 avr. 2026 à 23:13
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gsbfutur`
--

-- --------------------------------------------------------

--
-- Structure de la table `api_jetons`
--

CREATE TABLE `api_jetons` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `jeton` varchar(128) NOT NULL,
  `date_expiration` datetime NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `fiches_frais`
--

CREATE TABLE `fiches_frais` (
  `id` int(11) NOT NULL,
  `numero_fiche` varchar(20) NOT NULL,
  `id_utilisateur` int(11) NOT NULL,
  `mois` char(7) NOT NULL,
  `frais_essence` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frais_petit_dejeuner` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frais_repas_midi` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frais_repas_soir` decimal(10,2) NOT NULL DEFAULT 0.00,
  `frais_hotel` decimal(10,2) NOT NULL DEFAULT 0.00,
  `taux_tva_essence` decimal(5,2) NOT NULL DEFAULT 0.00,
  `taux_tva_petit_dejeuner` decimal(5,2) NOT NULL DEFAULT 0.00,
  `taux_tva_repas_midi` decimal(5,2) NOT NULL DEFAULT 0.00,
  `taux_tva_repas_soir` decimal(5,2) NOT NULL DEFAULT 0.00,
  `taux_tva_hotel` decimal(5,2) NOT NULL DEFAULT 0.00,
  `montant_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `statut` enum('saisie','transmise','validee','refusee','remboursee') NOT NULL DEFAULT 'saisie',
  `commentaire_visiteur` text DEFAULT NULL,
  `commentaire_comptable` text DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp(),
  `date_modification` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `fiches_frais`
--

INSERT INTO `fiches_frais` (`id`, `numero_fiche`, `id_utilisateur`, `mois`, `frais_essence`, `frais_petit_dejeuner`, `frais_repas_midi`, `frais_repas_soir`, `frais_hotel`, `taux_tva_essence`, `taux_tva_petit_dejeuner`, `taux_tva_repas_midi`, `taux_tva_repas_soir`, `taux_tva_hotel`, `montant_total`, `statut`, `commentaire_visiteur`, `commentaire_comptable`, `date_creation`, `date_modification`) VALUES
(1, 'FF-000001', 3, '2026-01', 52.00, 12.00, 20.00, 23.00, 150.00, 20.00, 10.00, 20.00, 20.00, 20.00, 269.63, 'transmise', '', NULL, '2026-04-21 21:02:08', '2026-04-21 21:06:51');

-- --------------------------------------------------------

--
-- Structure de la table `hors_forfaits`
--

CREATE TABLE `hors_forfaits` (
  `id` int(11) NOT NULL,
  `id_fiche` int(11) NOT NULL,
  `type_consommation` varchar(50) NOT NULL DEFAULT 'hors_forfait',
  `date` date NOT NULL,
  `libelle` varchar(255) NOT NULL,
  `montant` decimal(10,2) NOT NULL DEFAULT 0.00,
  `commentaire` varchar(255) DEFAULT NULL,
  `taux_tva` decimal(5,2) NOT NULL DEFAULT 20.00,
  `montant_ht` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_tva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_ttc` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_ajout` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `hors_forfaits`
--

INSERT INTO `hors_forfaits` (`id`, `id_fiche`, `type_consommation`, `date`, `libelle`, `montant`, `commentaire`, `taux_tva`, `montant_ht`, `montant_tva`, `montant_ttc`, `date_ajout`) VALUES
(1, 1, 'hors_forfait', '2026-04-21', 'petit dej & repas soir', 12.63, 'petit dej & repas soir', 20.00, 10.53, 2.10, 12.63, '2026-04-21 21:04:37');

-- --------------------------------------------------------

--
-- Structure de la table `journal_actions`
--

CREATE TABLE `journal_actions` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `role_utilisateur` varchar(50) NOT NULL DEFAULT 'invite',
  `action` varchar(100) NOT NULL,
  `type_objet` varchar(100) NOT NULL,
  `id_objet` int(11) DEFAULT NULL,
  `details` text NOT NULL,
  `niveau` enum('info','warning','error') NOT NULL DEFAULT 'info',
  `date_action` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `journal_actions`
--

INSERT INTO `journal_actions` (`id`, `id_utilisateur`, `role_utilisateur`, `action`, `type_objet`, `id_objet`, `details`, `niveau`, `date_action`) VALUES
(1, 1, 'administrateur', 'INITIALISATION', 'base_de_donnees', NULL, 'Base GSBFutur initialisée avec les comptes de démonstration.', 'info', '2026-04-21 20:56:43'),
(2, 3, 'visiteur', 'CONNEXION', 'utilisateur', 3, 'Connexion réussie pour visiteur@gsb.local', 'info', '2026-04-21 20:59:58'),
(3, 3, 'visiteur', 'CREATION_FICHE', 'fiche_frais', 1, 'Création d\'une nouvelle fiche visiteur', 'info', '2026-04-21 21:02:08'),
(4, 3, 'visiteur', 'AJOUT_HORS_FORFAIT', 'hors_forfait', 1, 'Ajout d\'un hors forfait : petit dej & repas soir', 'info', '2026-04-21 21:04:37'),
(5, 3, 'visiteur', 'TRANSMISSION_FICHE', 'fiche_frais', 1, 'Fiche transmise au comptable', 'info', '2026-04-21 21:06:51'),
(6, 3, 'visiteur', 'DECONNEXION', 'utilisateur', 3, 'Déconnexion utilisateur', 'info', '2026-04-21 21:07:02'),
(7, 2, 'comptable', 'CONNEXION', 'utilisateur', 2, 'Connexion réussie pour comptable@gsb.local', 'info', '2026-04-21 21:07:19'),
(8, 2, 'comptable', 'DECONNEXION', 'utilisateur', 2, 'Déconnexion utilisateur', 'info', '2026-04-21 21:08:28'),
(9, 1, 'administrateur', 'CONNEXION', 'utilisateur', 1, 'Connexion réussie pour admin@gsb.local', 'info', '2026-04-21 21:08:39');

-- --------------------------------------------------------

--
-- Structure de la table `justificatifs`
--

CREATE TABLE `justificatifs` (
  `id` int(11) NOT NULL,
  `id_fiche` int(11) NOT NULL,
  `nom_reel` varchar(255) NOT NULL,
  `nom_serveur` varchar(255) NOT NULL,
  `extension` varchar(10) NOT NULL,
  `contient_tva` tinyint(1) NOT NULL DEFAULT 0,
  `montant_ttc_5` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_ttc_10` decimal(10,2) NOT NULL DEFAULT 0.00,
  `montant_ttc_20` decimal(10,2) NOT NULL DEFAULT 0.00,
  `date_envoi` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `justificatifs`
--

INSERT INTO `justificatifs` (`id`, `id_fiche`, `nom_reel`, `nom_serveur`, `extension`, `contient_tva`, `montant_ttc_5`, `montant_ttc_10`, `montant_ttc_20`, `date_envoi`) VALUES
(1, 1, 'FOND ECRAN.jpg', 'justif_69e7caa65d1132.93217864.jpg', 'jpg', 0, 0.00, 0.00, 0.00, '2026-04-21 21:06:14');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `mdp` varchar(255) NOT NULL,
  `role` enum('visiteur','comptable','administrateur') NOT NULL DEFAULT 'visiteur',
  `est_approuve` tinyint(1) NOT NULL DEFAULT 1,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `prenom`, `email`, `mdp`, `role`, `est_approuve`, `date_creation`) VALUES
(1, 'Admin', 'GSB', 'admin@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'administrateur', 1, '2026-04-21 20:56:43'),
(2, 'Comptable', 'GSB', 'comptable@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'comptable', 1, '2026-04-21 20:56:43'),
(3, 'Visiteur', 'GSB', 'visiteur@gsb.local', '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi', 'visiteur', 1, '2026-04-21 20:56:43');


-- --------------------------------------------------------

--
-- Structure de la table `fiche_commentaires`
--

CREATE TABLE `fiche_commentaires` (
  `id` int(11) NOT NULL,
  `id_fiche` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `auteur_role` varchar(50) NOT NULL DEFAULT 'visiteur',
  `type_commentaire` varchar(50) NOT NULL DEFAULT 'commentaire',
  `contenu` text NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `titre` varchar(190) NOT NULL,
  `message` text NOT NULL,
  `niveau` enum('info','warning','error') NOT NULL DEFAULT 'info',
  `est_lue` tinyint(1) NOT NULL DEFAULT 0,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `api_jetons`
--
ALTER TABLE `api_jetons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `jeton` (`jeton`),
  ADD KEY `fk_api_jetons_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `fiches_frais`
--
ALTER TABLE `fiches_frais`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_fiche` (`numero_fiche`),
  ADD UNIQUE KEY `uc_fiche_utilisateur_mois` (`id_utilisateur`,`mois`);

--
-- Index pour la table `hors_forfaits`
--
ALTER TABLE `hors_forfaits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_hors_forfaits_fiche` (`id_fiche`);

--
-- Index pour la table `journal_actions`
--
ALTER TABLE `journal_actions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_journal_utilisateur` (`id_utilisateur`),
  ADD KEY `idx_journal_action` (`action`),
  ADD KEY `idx_journal_type_objet` (`type_objet`),
  ADD KEY `idx_journal_date_action` (`date_action`);

--
-- Index pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_justificatifs_fiche` (`id_fiche`);

--
-- Index pour la table `fiche_commentaires`
--
ALTER TABLE `fiche_commentaires`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_commentaires_fiche` (`id_fiche`),
  ADD KEY `fk_commentaires_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_notifications_utilisateur` (`id_utilisateur`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `api_jetons`
--
ALTER TABLE `api_jetons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `fiches_frais`
--
ALTER TABLE `fiches_frais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `hors_forfaits`
--
ALTER TABLE `hors_forfaits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `journal_actions`
--
ALTER TABLE `journal_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `fiche_commentaires`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `api_jetons`
--
ALTER TABLE `api_jetons`
  ADD CONSTRAINT `fk_api_jetons_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fiches_frais`
--
ALTER TABLE `fiches_frais`
  ADD CONSTRAINT `fk_fiches_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `hors_forfaits`
--
ALTER TABLE `hors_forfaits`
  ADD CONSTRAINT `fk_hors_forfaits_fiche` FOREIGN KEY (`id_fiche`) REFERENCES `fiches_frais` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `journal_actions`
--
ALTER TABLE `journal_actions`
  ADD CONSTRAINT `fk_journal_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `justificatifs`
--
ALTER TABLE `justificatifs`
  ADD CONSTRAINT `fk_justificatifs_fiche` FOREIGN KEY (`id_fiche`) REFERENCES `fiches_frais` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `fiche_commentaires`
--
ALTER TABLE `fiche_commentaires`
  ADD CONSTRAINT `fk_commentaires_fiche` FOREIGN KEY (`id_fiche`) REFERENCES `fiches_frais` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_commentaires_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `fk_notifications_utilisateur` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
