-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

ALTER TABLE llx_affaire_affaire_status ADD CONSTRAINT llx_c_affaire_status_fk_affaire FOREIGN KEY (fk_affaire) REFERENCES llx_affaire_affaire(rowid);