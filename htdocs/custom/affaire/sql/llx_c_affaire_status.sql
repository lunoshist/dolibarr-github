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
(1, 'Brouillon (à valider)', 'Brouillon', 2, 1, 2, 0, 0),
(2, 'Validée (proposition ouverte)', 'Ouverte', 2, 1, 3, 0, 1),
(3, 'À relancer', 'À relancer', 2, 1, 4, 0, 1),
(4, 'Signée (à facturer)', 'Signée', 2, 1, 5, 0, 1),
(5, 'Non signée (fermée)', 'Non signée', 2, 1, 1, 0, 1),
(6, 'Traitée | Facturé', 'Facturé', 2, 1, 7, 0, 1),
(7, 'Brouillon (à valider)', 'Brouillon', 2, 2, 2, 0, 0),
(8, 'Validée (prod en attente)', 'Validée', 2, 2, 3, 0, 1),
(9, 'Att.régulation cmde cl.', 'Att. régul. cmde cl.', 2, 2, 4, 1, 1),
(10, 'Att.règlement cmde cl.', 'Att. payment cl.', 2, 2, 4, 1, 1),
(11, 'Att.validation technique cl.', 'Att. valid. techn. cl.', 2, 2, 4, 1, 1),
(12, 'Att. validation incoterme cl.', 'Att. valid. incoterm cl.', 2, 2, 4, 1, 1),
(13, 'Lancement production', 'Prod', 2, 2, 5, 0, 1),
(14, 'Prod. Fabrication', 'Prod Fab.', 2, 2, 5, 1, 1),
(15, 'Prod. Fabrication', 'Prod Répa.', 2, 2, 5, 1, 1),
(16, 'Prod. Maintenance', 'Prod Mtnc.', 2, 2, 5, 1, 1),
(17, 'Annulée', 'Annulée', 2, 2, 1, 0, 1),
(18, 'Traitée | Facturée + Livrée', 'Traitée', 2, 2, 7, 0, 1),
(19, 'Att. Admin.', 'Att. Admin.', 2, 3, 2, 0, 1),
(20, 'À commencer', 'À commencer', 2, 3, 3, 0, 1),
(21, 'En cours de production', 'En cours', 2, 3, 3, 0, 1),
(22, 'Att. infos client', 'Att. infos cl.', 2, 3, 4, 0, 1),
(23, 'Att. fournisseur', 'Att. fourn.', 2, 3, 4, 0, 1),
(24, 'Terminé', 'Terminé', 2, 3, 5, 0, 1),
(25, 'Clôturer | Terminé', 'Clôturer', 2, 3, 7, 0, 1),
(26, 'À expédier', 'À expédier', 2, 4, 3, 0, 1),
(27, 'À facturer', 'À facturer', 2, 4, 3, 0, 1),
(28, 'Facturé', 'Facturé', 2, 4, 5, 0, 1),
(29, 'Expédition lancée', 'Expé. lancée', 2, 4, 5, 0, 1),
(30, 'Livré', 'Livré', 2, 4, 5, 0, 1),
(31, 'Livré | Att. Payement', 'Att. payement', 2, 4, 5, 0, 1),
(32, 'Traité | Facturé', 'Traité | Facturé', 2, 4, 7, 0, 1),
(33, 'Traité | Facturé + Livré', 'Traité | Facturé+Livré', 2, 4, 7, 0, 1),
(34, 'Brouillon (à valider)', 'Brouillon', 2, 5, 2, 0, 1),
(35, 'À préparer', 'À préparer', 2, 5, 3, 0, 1),
(36, 'À expédier', 'À expédier', 2, 5, 3, 0, 1),
(37, 'Att. Docs CCI', 'Att. Docs CCI', 2, 5, 4, 1, 1),
(38, 'Att. infos transport client', 'Att. info trans. cl.', 2, 5, 4, 1, 1),
(39, 'Att. enlevement', 'Att. enlevement', 2, 5, 4, 1, 1),
(40, 'En cours d expédition', 'Expé. en cours', 2, 5, 5, 0, 1),
(41, 'Annulé', 'Annulé', 2, 5, 1, 0, 1),
(42, 'Traité | Livré', 'Livré', 2, 5, 7, 0, 1),
(43, 'Brouillon (à valider)', 'Brouillon', 2, 6, 2, 0, 1),
(44, 'Proforma', 'Proforma', 2, 6, 2, 0, 1),
(45, 'Impayée', 'Impayée', 2, 6, 3, 0, 1),
(46, 'Règlement commencée', 'Commencée', 2, 6, 4, 0, 1),
(47, 'Abandonnée', 'Abandonnée', 2, 6, 1, 0, 1),
(48, 'Payée partiellement', 'Payée (en partie)', 2, 6, 8, 0, 1),
(49, 'Traité | Payée', 'Payée', 2, 6, 7, 0, 1),
(50, 'Validation Admin. (de clore l affaire)', 'Valid Admin.', 2, 7, 3, 0, 1),
(51, 'Att. certificat d exportation', 'Att. certificat export.', 2, 7, 4, 0, 1),
(52, 'Traité | Administré', 'Administré', 2, 7, 7, 0, 1);
