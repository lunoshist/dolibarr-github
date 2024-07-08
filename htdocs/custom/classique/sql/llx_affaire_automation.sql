INSERT INTO `llx_affaire_automation` (`rowid`, `fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`, `priority`) VALUES
(1, 2, 8, 'TYPE:200', '', 'System', 'closeOtherPropal', 41, 1),
(2, 2, 10, 'TYPE:200', 'TYPE:expe:<:100', 'changeStatusStep', 'Expe', 63, 1),
(3, 2, 10, 'TYPE:200', 'TYPE:facture:<:100', 'changeStatusStep', 'Facture', 73, 2),
(4, 2, 9, 'TYPE:300', 'TYPE:admin:<:100', 'changeStatusStep', 'Admin', 79, 1),
(5, 2, 13, 'TYPE:300', 'TYPE:affaire:<:100', 'changeStatusStep', 'Affaire', 36, 1);