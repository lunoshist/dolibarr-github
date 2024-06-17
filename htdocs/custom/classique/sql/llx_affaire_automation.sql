INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`) VALUES
(2, 8, 'TYPE:200', '', 'System', 'createOrder', 43),
(2, 8, 'TYPE:200', '', 'System', 'closeOtherPropal', 40),
(2, 9, 'TYPE:100', '', 'System', 'generateProd', 54),
(2, 9, 'TYPE:200', '', 'changeStatus', 'Prod', 55),
(2, 10, 'TYPE:200', '', 'changeStatus', 'Expe', 62);