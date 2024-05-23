INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`) VALUES
(2, 9, 'TYPE:200', '', 'System', 'createOrder', 63),
(2, 9, 'TYPE:200', '', 'System', 'closeOtherPropal', 60),
(2, 10, 'TYPE:100', '', 'System', 'generateProject', 74);