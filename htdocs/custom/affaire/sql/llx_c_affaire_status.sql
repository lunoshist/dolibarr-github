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
(1, 'Création', 'Création', 1, 1, 00, 0, 1),
(2, 'Clôturée', 'Clôturée', 1, 1, 300, 0, 1),
(3, 'Brouillon (à valider)', 'Brouillon', 1, 2, 00, 0, 0),
(4, 'Validée (proposition ouverte)', 'Ouverte', 1, 2, 100, 0, 1),
(5, 'À relancer', 'À relancer', 1, 2, 150, 0, 1),
(6, 'Signée (à facturer)', 'Signée', 1, 2, 200, 0, 1),
(7, 'Non signée (fermée)', 'Non signée', 1, 2, 350, 0, 1),
(8, 'Traitée | Facturé', 'Facturé', 1, 2, 300, 0, 1),
(9, 'Brouillon (à valider)', 'Brouillon', 1, 3, 00, 0, 0),
(10, 'Validée (prod en attente)', 'Validée', 1, 3, 100, 0, 1),
(11, 'Lancement production', 'Prod', 1, 3, 200, 0, 1),
(12, 'Annulée', 'Annulée', 1, 3, -1, 0, 1),
(13, 'Traitée | Facturée + Livrée', 'Traitée', 1, 3, 300, 0, 1),
(14, 'Att. Admin.', 'Att. Admin.', 1, 4, 00, 0, 1),
(15, 'À commencer', 'À commencer', 1, 4, 100, 0, 1),
(16, 'En cours de production', 'En cours', 1, 4, 100, 0, 1),
(18, 'Att. infos client', 'Att. infos cl.', 1, 4, 150, 0, 1),
(19, 'Att. fournisseur', 'Att. fourn.', 1, 4, 150, 0, 1),
(20, 'Terminé', 'Terminé', 1, 4, 200, 0, 1),
(21, 'Clôturé | Terminé', 'Clôturé', 1, 4, 300, 0, 1),
(22, 'À expédier', 'À expédier', 1, 5, 100, 0, 1),
(23, 'À facturer', 'À facturer', 1, 5, 100, 0, 1),
(24, 'Facturé', 'Facturé', 1, 5, 200, 0, 1),
(25, 'Expédition lancée', 'Expé. lancée', 1, 5, 200, 0, 1),
(26, 'Livré', 'Livré', 1, 5, 200, 0, 1),
(27, 'Livré | Att. Payement', 'Att. payement', 1, 5, 200, 0, 1),
(28, 'Traité | Facturé', 'Traité | Facturé', 1, 5, 300, 0, 1),
(29, 'Traité | Facturé + Livré', 'Traité | Facturé+Livré', 1, 5, 300, 0, 1),
(30, 'Brouillon (à valider)', 'Brouillon', 1, 6, 00, 0, 1),
(31, 'À préparer', 'À préparer', 1, 6, 100, 0, 1),
(32, 'À expédier', 'À expédier', 1, 6, 100, 0, 1),
(33, 'En cours d expédition', 'Expé. en cours', 1, 6, 200, 0, 1),
(34, 'Annulé', 'Annulé', 1, 6, -1, 0, 1),
(35, 'Traité | Livré', 'Livré', 1, 6, 300, 0, 1),
(36, 'Brouillon (à valider)', 'Brouillon', 1, 7, 00, 0, 1),
(37, 'Proforma', 'Proforma', 1, 7, 0, 0, 1),
(38, 'Impayée', 'Impayée', 1, 7, 100, 0, 1),
(39, 'Règlement commencée', 'Commencée', 1, 7, 150, 0, 1),
(40, 'Abandonnée', 'Abandonnée', 1, 6, 1+-1, 0, 1),
(41, 'Payée partiellement', 'Payée (en partie)', 1, 7, 350, 0, 1),
(42, 'Traité | Payée', 'Payée', 1, 7, 300, 0, 1);