-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


-- BEGIN MODULEBUILDER INDEXES
ALTER TABLE llx_affaire_affaire ADD INDEX idx_affaire_affaire_rowid (rowid);
ALTER TABLE llx_affaire_affaire ADD INDEX idx_affaire_affaire_ref (ref);
ALTER TABLE llx_affaire_affaire ADD INDEX idx_affaire_affaire_fk_soc (fk_soc);
ALTER TABLE llx_affaire_affaire ADD INDEX idx_affaire_affaire_final_customer (final_customer);
-- END MODULEBUILDER INDEXES

--ALTER TABLE llx_affaire_affaire ADD UNIQUE INDEX uk_affaire_affaire_fieldxy(fieldx, fieldy);

--ALTER TABLE llx_affaire_affaire ADD CONSTRAINT llx_affaire_affaire_fk_field FOREIGN KEY (fk_field) REFERENCES llx_affaire_myotherobject(rowid);
ALTER TABLE llx_affaire_affaire ADD CONSTRAINT llx_affaire_affaire_fk_workflow_type FOREIGN KEY (fk_workflow_type) REFERENCES llx_c_affaire_workflow_types(rowid);