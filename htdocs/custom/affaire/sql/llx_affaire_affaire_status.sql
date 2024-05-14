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


CREATE TABLE llx_affaire_affaire_status(
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	fk_affaire integer,
	fk_status_propal integer,
	fk_status_cmde integer,
	fk_status_prod integer,
	fk_status_post-prod integer,
	fk_status_expe integer,
	fk_status_facture integer,
	fk_status_admin integer
) ENGINE=innodb;
