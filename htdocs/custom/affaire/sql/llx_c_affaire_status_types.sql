-- Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>

--
-- Structure de la table `llx_c_affaire_status_types`
--

CREATE TABLE `llx_c_affaire_status_types` (
  `rowid` integer AUTO_INCREMENT PRIMARY KEY NOT NULL,
  `code` varchar (128) PRIMARY KEY NOT NULL,
  `label` varchar(255) NOT NULL,
  `description` varchar (255),
  `color` varchar (255),
  `border-color` varchar (255),
  `backgroung-color` varchar (255)
) ENGINE=InnoDB

--
-- Déchargement des données de la table `llx_c_affaire_status_types`
--

INSERT INTO `llx_c_affaire_status_types` (`rowid`, `code`, `label`, `description`, `color`, `border-color`, `backgroung-color`) VALUES
(1, '-1', `cancel`, `(Clôturer) Étape annulé, abandonner ou qui n'a pas donné suite (Ex: propal non signé)`, `999999`, ``, `e7f0f0`),
(2, '00', `draft`, `Brouillon`, `999999`, `cbd3d3`, ``),
(3, '100', `open`, `Étape en cours - À nous de jouer `, `ffffff`, ``, `bc9526`),
(4, '150', `-open-`, `Étape en cours - dépend pas de nous | Qui demande une attention particulière`, `212529`, `bc9526`, ``),
(5, '200', `success`, `Étape terminée, ou en voie de se terminé`, `ffffff`, ``, `25a580`),
(6, '250', `-success-`, `Bonne nouvelle, cette étape à réussie :)`, `212529`, `25a580`, ``),
(7, '300', `closed`, `Clôturé, achevé de manière optimal`, `777777`, ``, `cad2d2`),
(8, '350', `-closed-`, `Clôturé, achevé de manière relative (Ex: payement partiel)`, `212529`, `cad2d2`, ``),
(9, '400', `error`, `Quelque chose ne va pas !!!`, `ffffff`, ``, `993013`),
(10, '450', `-error-`, `Aïe! Aïe! Aïe! Cette étape a échoué :(`, `212529`, `993013`, ``),
(11, '500', `bonnus`, `bonnus`, `ffffff`, ``, `9c9c26`),
