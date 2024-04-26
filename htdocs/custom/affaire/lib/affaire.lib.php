<?php
/* Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    affaire/lib/affaire.lib.php
 * \ingroup affaire
 * \brief   Library files with common functions for Affaire
 */

/**
 * Prepare admin pages header
 *
 * @return array
 */
function affaireAdminPrepareHead()
{
	global $langs, $conf;

	// global $db;
	// $extrafields = new ExtraFields($db);
	// $extrafields->fetch_name_optionals_label('myobject');

	$langs->load("affaire@affaire");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/affaire/admin/setup.php", 1);
	$head[$h][1] = $langs->trans("Settings");
	$head[$h][2] = 'settings';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/affaire/admin/myobject_extrafields.php", 1);
	$head[$h][1] = $langs->trans("ExtraFields");
	$nbExtrafields = is_countable($extrafields->attributes['myobject']['label']) ? count($extrafields->attributes['myobject']['label']) : 0;
	if ($nbExtrafields > 0) {
		$head[$h][1] .= ' <span class="badge">' . $nbExtrafields . '</span>';
	}
	$head[$h][2] = 'myobject_extrafields';
	$h++;
	*/

	$head[$h][0] = dol_buildpath("/affaire/admin/about.php", 1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@affaire:/affaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@affaire:/affaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'affaire@affaire');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'affaire@affaire', 'remove');

	return $head;
}

/**
 * Get the id of the linked affaire if there is one
 *
 * @param int $id 	id of the object 
 * @return int 		id of the linked affaire if exist
 */
function getLinkedAff($id) {
	// TODO The entiere function
	$AffID = 1;
	return $AffID;
}

/**
 * Print a nav bar with the tabs of the specified object
 *
 * @param object $object
 * @return void
 */
function dol_tabs($object) {
	// TODO The entiere function
	print dol_get_fiche_head();
}

/**
 * Print the banner (Icon, Ref, Thirdparty, Affaire, backlink, Status) of the specified object
 *
 * @param object $object
 * @return void
 */
function dol_banner($object) {
	// TODO The entiere function
	dol_banner_tab($object, '');
}

/**
 * Print a nav bar with the steps of the specified workflow as tabs
 *
 * @param int $workflow_type
 * @return void
 */
function dol_workflow_tabs($workflow_type) {
	// TODO review the entiere function
	$sql = 'SELECT label from llx_c_affaire_workflow_types WHERE rowid='.$workflow_type;
	$res= $db->query($sql);
	$label = $res->label;
	dol_include_once("/$label/core/modules/mod$label.class.php");
	$modLabel = "mod$label";
	$modAffaire = new $modLabel;
	dol_tabs($modAffaire);
}

/**
 * Return the list of linked objects of an object (where type of objects returned can be specified)
 *
 * @param object $object			
 * @param string $linkedObjectType	String to spécify the type of objects returned ('propal', 'order' ...)
 * @return array
 */
function getLinkedObject($object, $linkedObjectType) {
	// TODO The entiere function
	return $linkedObjectArray;
}