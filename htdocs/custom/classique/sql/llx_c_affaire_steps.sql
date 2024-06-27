INSERT INTO `llx_c_affaire_steps` (`rowid`, `label`, `label_short`, `fk_workflow_type`, `fk_default_status`, `position`, `added`, `object`, `active`) VALUES
(7, 'Affaire', 'Affaire', 2, 35, null, 0, 0, 1),
(8, 'Proposition(s) Commerciale(s)', 'Propal', 2, 37, 1, 0, 1, 1),
(9, 'Commande Client', 'Cmde', 2, 43, 2, 0, 1, 1),
(10, 'Production', 'Prod', 2, 55, 3, 0, 1, 1),
(11, 'Expédition(s)', 'Expe', 2, 63, 5, 0, 1, 1),
(12, 'Facturation(s)', 'Facture', 2, 73, 6, 1, 1, 1),
(13, 'Administratif', 'Admin', 2, 79, 7, 1, 0, 1);