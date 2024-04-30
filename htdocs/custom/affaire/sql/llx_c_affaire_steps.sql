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

--
-- Structure de la table `llx_c_affaire_steps`
--

CREATE TABLE llx_c_affaire_steps(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	label varchar(255) NOT NULL, 
	label_short varchar(128) NOT NULL, 

	fk_workflow_type integer, 
	fk_default_status integer,
	
	position integer,
	added integer,
	active integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

--
-- Déchargement des données de la table `llx_c_affaire_steps`
--

INSERT INTO `llx_c_affaire_steps` (`rowid`, `label`, `label_short`, `fk_workflow_type`, `fk_default_status`, `position`, `added`, `active`) VALUES
(1, 'Proposition Commerciale', 'Propal', 2, 2, 1, 0, 1),
(2, 'Commande Client', 'Cmde', 2, 8, 2, 0, 1),
(3, 'Production', 'Prod', 2, 19, 3, 0, 1),
(4, 'Post-production', 'Post-Prod', 2, 26, 4, 0, 1),
(5, 'Expédition', 'Expe', 2, 35, 5, 0, 1),
(6, 'Facturation', 'Facture', 2, 43, 6, 0, 1),
(7, 'Administratif', 'Admin', 2, 50, 7, 1, 1);