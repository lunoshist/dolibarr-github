-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

--
-- Structure de la table `llx_c_affaire_workflow_types`
--

CREATE TABLE `llx_c_affaire_workflow_types` (
  `rowid` integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `label` varchar(255) NOT NULL
) ENGINE=InnoDB

--
-- Déchargement des données de la table `llx_c_affaire_workflow_types`
--

INSERT INTO `llx_c_affaire_workflow_types` (`rowid`, `label`) VALUES
(1, 'BASIC'),
(2, 'Affaire_Classique');
