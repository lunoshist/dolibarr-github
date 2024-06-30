INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`, `priority`) VALUES
(2, 8, 'TYPE:200', '', 'System', 'closeOtherPropal', 41, 1),
# (2, 8, 'TYPE:200', 'FUNCTION:checkCommandeExist($affaire):>=:0', 'System', 'createOrder', 44, 2),
# (2, 9, 'TYPE:100', '', 'System', 'generateProd', 55, 1),
# (2, 9, 'TYPE:200', 'TYPE:prod:<:100', 'changeStatus', 'Prod', 56, 1),
(2, 10, 'TYPE:200', 'TYPE:expe:<:100', 'changeStatusStep', 'Expe', 63, 1),
(2, 10, 'TYPE:200', 'TYPE:facture:<:100', 'changeStatusStep', 'Facture', 73, 2);