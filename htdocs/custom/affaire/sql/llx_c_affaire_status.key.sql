-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

ALTER TABLE llx_c_affaire_status ADD CONSTRAINT llx_c_affaire_status_fk_workflow_type FOREIGN KEY (fk_workflow_type) REFERENCES llx_c_affaire_workflow_types(rowid);
ALTER TABLE llx_c_affaire_status ADD CONSTRAINT llx_c_affaire_status_fk_step FOREIGN KEY (fk_step) REFERENCES llx_c_affaire_steps(rowid);
ALTER TABLE llx_c_affaire_status ADD CONSTRAINT llx_c_affaire_status_fk_type FOREIGN KEY (fk_type) REFERENCES llx_c_affaire_status_types(code);