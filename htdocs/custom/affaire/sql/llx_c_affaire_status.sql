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
(1, 'Brouillon (à valider)', 'Brouillon', 2, 1, 00, 0, 0),
(2, 'Validée (proposition ouverte)', 'Ouverte', 2, 1, 100, 0, 1),
(3, 'À relancer', 'À relancer', 2, 1, 150, 0, 1),
(4, 'Signée (à facturer)', 'Signée', 2, 1, 200, 0, 1),
(5, 'Non signée (fermée)', 'Non signée', 1, 2, -1, 0, 1),
(6, 'Traitée | Facturé', 'Facturé', 2, 1, 300, 0, 1),
(8, 'Brouillon (à valider)', 'Brouillon', 2, 2, 00, 0, 0),
(9, 'Validée (prod en attente)', 'Validée', 2, 2, 100, 0, 1),
(10, 'Att.régulation cmde cl.', 'Att. régul. cmde cl.', 2, 2, 150, 1, 1),
(11, 'Att.règlement cmde cl.', 'Att. payment cl.', 2, 2, 150, 1, 1),
(12, 'Att.validation technique cl.', 'Att. valid. techn. cl.', 2, 2, 150, 1, 1),
(13, 'Att. validation incoterme cl.', 'Att. valid. incoterm cl.', 2, 2, 150, 1, 1),
(14, 'Lancement production', 'Prod', 2, 2, 200, 0, 1),
(15, 'Prod. Fabrication', 'Prod Fab.', 2, 2, 200, 1, 1),
(16, 'Prod. Fabrication', 'Prod Répa.', 2, 2, 200, 1, 1),
(17, 'Prod. Maintenance', 'Prod Mtnc.', 2, 2, 200, 1, 1),
(18, 'Annnulée', 'Annulée', 1, 2, -1, 0, 1),
(19, 'Traitée | Facturée + Livrée', 'Traitée', 2, 2, 300, 0, 1),
(20, 'Att. Admin.', 'Att. Admin.', 2, 3, 00, 0, 1),
(21, 'À commencer', 'À commencer', 2, 3, 100, 0, 1),
(22, 'En cours de production', 'En cours', 2, 3, 100, 0, 1),
(23, 'Att. infos client', 'Att. infos cl.', 2, 3, 150, 0, 1),
(24, 'Att. fournisseur', 'Att. fourn.', 2, 3, 150, 0, 1),
(25, 'Terminé', 'Terminé', 2, 3, 200, 0, 1),
(26, 'Clôturer | Terminé', 'Clôturer', 2, 3, 300, 0, 1),
(27, 'À expédier', 'À expédier', 2, 4, 100, 0, 1),
(28, 'À facturer', 'À facturer', 2, 4, 100, 0, 1),
(29, 'Facturé', 'Facturé', 2, 4, 200, 0, 1),
(30, 'Expédition lancée', 'Expé. lancée', 2, 4, 200, 0, 1),
(31, 'Livré', 'Livré', 2, 4, 200, 0, 1),
(32, 'Livré | Att. Payement', 'Att. payement', 2, 4, 200, 0, 1),
(33, 'Traité | Facturé', 'Traité | Facturé', 2, 4, 300, 0, 1),
(34, 'Traité | Facturé + Livré', 'Traité | Facturé+Livré', 2, 4, 300, 0, 1),
(35, 'Brouillon (à valider)', 'Brouillon', 2, 5, 00, 0, 1),
(36, 'À préparer', 'À préparer', 2, 5, 100, 0, 1),
(37, 'À expédier', 'À expédier', 2, 5, 100, 0, 1),
(38, 'Att. Docs CCI', 'Att. Docs CCI', 2, 5, 150, 1, 1),
(39, 'Att. infos transport client', 'Att. info trans. cl.', 2, 5, 150, 1, 1),
(40, 'Att. enlevement', 'Att. enlevement', 2, 5, 150, 1, 1),
(41, 'En cours d expédition', 'Expé. en cours', 2, 5, 200, 0, 1),
(42, 'Annulé', 'Annulé', 1, 2, -1, 0, 1),
(43, 'Traité | Livré', 'Livré', 2, 5, 300, 0, 1),
(44, 'Brouillon (à valider)', 'Brouillon', 2, 6, 00, 0, 1),
(45, 'Proforma', 'Proforma', 2, 6, 00, 0, 1),
(46, 'Impayée', 'Impayée', 2, 6, 100, 0, 1),
(47, 'Règlement commencée', 'Commencée', 2, 6, 150, 0, 1),
(48, 'Abandonnée', 'Abandonnée', 1, 2, -1, 0, 1),
(49, 'Payée partiellement', 'Payée (en partie)', 2, 6, 350, 0, 1),
(50, 'Traité | Payée', 'Payée', 2, 6, 300, 0, 1),
(51, 'Validation Admin. (de clore l affaire)', 'Valid Admin.', 2, 7, 100, 0, 1),
(52, 'Att. certificat d exportation', 'Att. certificat export.', 2, 7, 150, 0, 1),
(53, 'Traité | Administré', 'Administré', 2, 7, 300, 0, 1);
