-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

--
-- Structure de la table `llx_c_affaire_status_types`
--

CREATE TABLE `llx_affaire_automation` (
  rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
  fk_workflow_type integer NOT NULL,
  origin_step integer NOT NULL,
  origin_status text NOT NULL,
  conditions text,
  automation_type varchar (255),
  new_step text,
  new_status text
) ENGINE=InnoDB;

--
-- Déchargement des données de la table `llx_c_affaire_status_types`
--

INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`) VALUES
(1, 2, 'TYPE:200', '', 'System', 'createOrder', 10),
(1, 2, 'TYPE:200', '', 'System', 'closeOtherPropal', 7),
(1, 3, 'TYPE:100', '', 'System', 'generateProject', 14);
