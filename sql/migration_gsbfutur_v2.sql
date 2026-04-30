-- Mise à jour de la base existante gsbfutur
-- 1. Création des tables si besoin
CREATE TABLE IF NOT EXISTS fiche_commentaires (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_fiche INT NOT NULL,
  id_utilisateur INT NULL,
  auteur_role VARCHAR(50) NOT NULL DEFAULT 'visiteur',
  type_commentaire VARCHAR(50) NOT NULL DEFAULT 'commentaire',
  contenu TEXT NOT NULL,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_commentaires_fiche FOREIGN KEY (id_fiche) REFERENCES fiches_frais(id) ON DELETE CASCADE,
  CONSTRAINT fk_commentaires_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_utilisateur INT NULL,
  titre VARCHAR(190) NOT NULL,
  message TEXT NOT NULL,
  niveau ENUM('info','warning','error') NOT NULL DEFAULT 'info',
  est_lue TINYINT(1) NOT NULL DEFAULT 0,
  date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notifications_utilisateur FOREIGN KEY (id_utilisateur) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Mise à jour des mots de passe de démonstration
UPDATE utilisateurs SET mdp = '$2y$12$bOewzb/ULDFKpFs7UDrOcOwtV/PYuGYJAeOpqOb7Y2OIXBfh4VOEi' WHERE email IN ('admin@gsb.local', 'comptable@gsb.local', 'visiteur@gsb.local');

-- 3. Nettoyage de quelques anciens textes de démonstration
UPDATE fiches_frais SET commentaire_visiteur = '' WHERE commentaire_visiteur = 'Fiche créée depuis le formulaire visiteur.';
DELETE FROM notifications WHERE titre IN ('Connexion réussie') OR message LIKE '%premium%' OR message LIKE '%cockpit%';
