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
-- Structure de la table `llx_c_affaire_status`
--

CREATE TABLE llx_c_affaire_status(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	label varchar(255) NOT NULL, 
	label_short varchar(128) NOT NULL, 

	fk_workflow_type integer, 
	fk_step integer,
	fk_type integer,
	
  added integer,
	active integer
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

--
-- Déchargement des données de la table `llx_c_affaire_status`
--

INSERT INTO `llx_c_affaire_status` (`rowid`, `label`, `label_short`, `fk_workflow_type`, `fk_step`, `fk_type`, `added`, `active`) VALUES
(1, 'Brouillon (à valider)', 'Brouillon', 1, 1, 00, 0, 0),
(2, 'Validée (proposition ouverte)', 'Ouverte', 1, 1, 100, 0, 1),
(3, 'À relancer', 'À relancer', 1, 1, 150, 0, 1),
(4, 'Signée (à facturer)', 'Signée', 1, 1, 200, 0, 1),
(5, 'Non signée (fermée)', 'Non signée', 1, 1, -1, 0, 1),
(6, 'Traitée | Facturé', 'Facturé', 1, 1, 300, 0, 1),
(7, 'Brouillon (à valider)', 'Brouillon', 1, 2, 00, 0, 0),
(8, 'Validée (prod en attente)', 'Validée', 1, 2, 100, 0, 1),
(9, 'Lancement production', 'Prod', 1, 2, 200, 0, 1),
(10, 'Annulée', 'Annulée', 1, 2, -1, 0, 1),
(11, 'Traitée | Facturée + Livrée', 'Traitée', 1, 2, 300, 0, 1),
(12, 'Att. Admin.', 'Att. Admin.', 1, 3, 00, 0, 1),
(13, 'À commencer', 'À commencer', 1, 3, 100, 0, 1),
(14, 'En cours de production', 'En cours', 1, 3, 100, 0, 1),
(15, 'Att. infos client', 'Att. infos cl.', 1, 3, 150, 0, 1),
(16, 'Att. fournisseur', 'Att. fourn.', 1, 3, 150, 0, 1),
(18, 'Terminé', 'Terminé', 1, 3, 200, 0, 1),
(19, 'Clôturer | Terminé', 'Clôturer', 1, 3, 300, 0, 1),
(20, 'À expédier', 'À expédier', 1, 4, 100, 0, 1),
(21, 'À facturer', 'À facturer', 1, 4, 100, 0, 1),
(22, 'Facturé', 'Facturé', 1, 4, 200, 0, 1),
(23, 'Expédition lancée', 'Expé. lancée', 1, 4, 200, 0, 1),
(24, 'Livré', 'Livré', 1, 4, 200, 0, 1),
(25, 'Livré | Att. Payement', 'Att. payement', 1, 4, 200, 0, 1),
(26, 'Traité | Facturé', 'Traité | Facturé', 1, 4, 300, 0, 1),
(27, 'Traité | Facturé + Livré', 'Traité | Facturé+Livré', 1, 4, 300, 0, 1),
(28, 'Brouillon (à valider)', 'Brouillon', 1, 5, 00, 0, 1),
(29, 'À préparer', 'À préparer', 1, 5, 100, 0, 1),
(30, 'À expédier', 'À expédier', 1, 5, 100, 0, 1),
(31, 'En cours d expédition', 'Expé. en cours', 1, 5, 200, 0, 1),
(32, 'Annulé', 'Annulé', 1, 5, -1, 0, 1),
(33, 'Traité | Livré', 'Livré', 1, 5, 300, 0, 1),
(34, 'Brouillon (à valider)', 'Brouillon', 1, 6, 00, 0, 1),
(35, 'Proforma', 'Proforma', 1, 6, 0, 0, 1),
(36, 'Impayée', 'Impayée', 1, 6, 100, 0, 1),
(37, 'Règlement commencée', 'Commencée', 1, 6, 150, 0, 1),
(38, 'Abandonnée', 'Abandonnée', 1, 6, -1, 0, 1),
(39, 'Payée partiellement', 'Payée (en partie)', 1, 6, 350, 0, 1),
(40, 'Traité | Payée', 'Payée', 1, 6, 300, 0, 1);
