-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

ALTER TABLE llx_c_affaire_steps ADD CONSTRAINT llx_c_affaire_steps_fk_workflow_type FOREIGN KEY (fk_workflow_type) REFERENCES llx_c_affaire_workflow_types(rowid);
ALTER TABLE llx_c_affaire_steps ADD CONSTRAINT llx_c_affaire_steps_fk_default_status FOREIGN KEY (fk_default_status) REFERENCES llx_c_affaire_status(rowid);