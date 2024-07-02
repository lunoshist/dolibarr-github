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
  new_status text,
  priority integer
) ENGINE=InnoDB;

--
-- Déchargement des données de la table `llx_c_affaire_status_types`
--

-- INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`, `priority`) VALUES
-- (2, 8, 'TYPE:200', '', 'System', 'closeOtherPropal', 41, 1),
-- (2, 8, 'TYPE:200', 'FUNCTION:checkCommandeExist($affaire):>=:0', 'System', 'NOcreateOrder', 44, 2),
-- (2, 9, 'TYPE:100', '', 'System', 'NOgenerateProd', 55, 1),
-- (2, 9, 'TYPE:200', 'TYPE:prod:<:100', 'changeStatus', 'Prod', 56, 1),
-- (2, 10, 'TYPE:200', 'TYPE:expe:<:100', 'changeStatusStep', 'Expe', 63, 1),
-- (2, 10, 'TYPE:200', 'TYPE:facture:<:100', 'changeStatusStep', 'Facture', 73, 2);
