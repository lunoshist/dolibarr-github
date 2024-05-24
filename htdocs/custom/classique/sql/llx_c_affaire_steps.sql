INSERT INTO `llx_c_affaire_steps` (`rowid`, `label`, `label_short`, `fk_workflow_type`, `fk_default_status`, `position`, `added`, `object`, `active`) VALUES
(8, 'Affaire', 'Affaire', 2, 42, null, 0, 0, 1),
(9, 'Proposition Commerciale', 'Propal', 2, 44, 1, 0, 1, 1),
(10, 'Commande Client', 'Cmde', 2, 51, 2, 0, 1, 1),
(11, 'Production', 'Prod', 2, 62, 3, 0, 1, 1),
(12, 'Post-production', 'Post_Prod', 2, 69, 4, 0, 0, 1),
(13, 'Expédition', 'Expe', 2, 78, 5, 0, 1, 1),
(14, 'Facturation', 'Facture', 2, 86, 6, 1, 1, 1),
(15, 'Administratif', 'Admin', 2, 93, 7, 1, 0, 1);