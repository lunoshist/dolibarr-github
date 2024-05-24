INSERT INTO `llx_affaire_automation` (`fk_workflow_type`, `origin_step`, `origin_status`, `conditions`, `automation_type`, `new_step`, `new_status`) VALUES
(2, 9, 'TYPE:200', '', 'System', 'createOrder', 51),
(2, 9, 'TYPE:200', '', 'System', 'closeOtherPropal', 48),
(2, 10, 'TYPE:100', '', 'System', 'generateProject', 62);