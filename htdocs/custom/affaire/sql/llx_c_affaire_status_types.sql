-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

--
-- Structure de la table `llx_c_affaire_status_types`
--

CREATE TABLE `llx_c_affaire_status_types` (
  code integer PRIMARY KEY NOT NULL,
  label varchar(255) NOT NULL,
  description varchar (255),
  color varchar (255),
  border_color varchar (255),
  background_color varchar (255)
) ENGINE=InnoDB;

--
-- Déchargement des données de la table `llx_c_affaire_status_types`
--

INSERT INTO `llx_c_affaire_status_types` (`code`, `label`, `description`, `color`, `border_color`, `background_color`) VALUES
('-1', 'cancel', 'Étape annulée, abandonnér', '999999', '', 'e7f0f0'),
('00', 'draft', 'Brouillon', '999999', 'cbd3d3', ''),
('100', 'open', 'Étape en cours - À nous de jouer ', 'ffffff', '', 'bc9526'),
('150', '-open-', 'Étape en cours - dépend pas de nous | Qui demande une attention particulière', '212529', 'bc9526', ''),
('200', 'success', 'Étape terminée, ou en voie de se terminé', 'ffffff', '', '25a580'),
('250', '-success-', 'Bonne nouvelle, cette étape à réussie :)', '212529', '25a580', ''),
('300', 'closed', 'Clôturé, achevé de manière optimal', '777777', '', 'cad2d2'),
('350', '-closed-', 'Clôturé, achevé de manière relative (Ex: payement partiel) ou qui n a pas donné suite (Ex: propal non signé)', '212529', 'cad2d2', ''),
('400', 'error', 'Quelque chose ne va pas !!!', 'ffffff', '', '993013'),
( '450', '-error-', 'Aïe! Aïe! Aïe! Cette étape a échoué :(', '212529', '993013', ''),
( '500', 'bonnus', 'bonnus', 'ffffff', '', '9c9c26');
