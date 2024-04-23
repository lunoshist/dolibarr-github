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


CREATE TABLE llx_affaire_affaire(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	ref varchar(128) NOT NULL, 
	label varchar(255), 
	description text, 
	fk_workflow_type integer NOT NULL, 
	fk_soc integer, 
	final_customer integer, 
	note_public text, 
	note_private text, 
	total_ht double DEFAULT NULL, 
	total_ttc double DEFAULT NULL, 
	fk_status integer NOT NULL
	fk_step integer NOT NULL, 
	date_creation datetime NOT NULL, 
	tms timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, 
	date_close datetime, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	fk_user_close integer, 
	last_main_doc varchar(255), 
	import_key varchar(14), 
	model_pdf varchar(255), 
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
