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
	// $res = @include dol_buildpath($reldir . '/' . $tplname . '.tpl.php');
	dol_include_once("/$label/core/modules/mod$label.class.php");
	$modLabel = "mod$label";
	$modAffaire = new $modLabel;
	dol_tabs($modAffaire);
}

/**
 * Change the status of a step of an affaire, 
 * then look for automating (like another status change induced), 
 * finally update affaire status and step
 *
 * @param Affaire $affaire		object affaire to deal with
 * @param int $newStatus		rowid of the new status
 * @param string $condition		a status changement might need to match some condition espacially for automation
 * @param string $step 			step can be precised for optimisation
 * @param int $previousStatus 	previousStatus can be precised for optimisation
 * @param object $workflow 		workflow can be precised for optimisation
 * @return void
 */
function change_status($affaire, $newStatus, $condition='', $step='', $previousStatus='', $workflow='') {
	global $db;
	
	/** TODO : hook 'changeStatus'
	* Maybe if someone want to personnalyzed comportement of change_status function
	*
	* $parameters = array($newStatus, $condition);
	* $reshook = $hookmanager->executeHooks('changeStatus', $parameters, $affaire, 'changeStatus'); // Note that $action and $object may have been modified by some hooks
	* if ($reshook < 0) {
	* 	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	* }
	*
	* if (empty($reshook)) {
	*/

	// CHECK CONDITIONS
	if (!empty($condition)) {
		// TODO 

		if ($invalid_codition) {
			return ;
		}
	}

	// GET INFO
	if (empty($workflow)){
		// TODO sql query based on $affaire->fk_workflow_type
	}

	if ($step != 'affaire') {
		// GET INFO
		if (empty($step)){
			// TODO sql query based on $newStatus
		}
		if (empty($previousStatus)){
			// TODO sql query based on $affaire
		}

		//CHANGE STATUS
		$sql = "UPDATE llx_affaire_affaire_status SET fk_status_$step = $newStatus WHERE fk_affaire = $affaire->id";
		$resql = $db->query($sql);
		if ($resql) {
			setEventMessages("Nouveau statut :$newStatus", null, 'mesgs');
		} else {
			dol_print_error($db);
		}

		// PREPARE UPDATE AFFAIRE STEP & STATUS
		$newAffaireStep = '';
		$newAffaireStatus = '';
		$affaireStatus = array();

		// 1- Fetch steps in good order
		$sql = "SELECT rowid, label_short FROM llx_c_affaire_steps WHERE fk_workflow_type = $workflow->rowid ORDER BY position";
		$resql = $db->query($sql);
		if ($resql) {
			// 2- For each step (starting by the first step) ...
			while (empty($newAffaireStatus)) {
				$rstep = $db->fetch_object($resql);
				$rstepLabel = strtolower($rstep->label_short);
				$rstepRowid = strtolower($rstep->rowid);

				// ... fetch step status ...
				$sql = "SELECT fk_status_$rstepLabel FROM llx_affaire_affaire_status WHERE fk_affaire = $affaire->id";
				$resql = $db->query($sql);
				if ($resql) {
					$obj = $db->fetch_object($resql);
					$rstepStatus = $obj->{"fk_status_$rstepLabel"};
				}

				// ... then fetch status type code ...
				$sql = "SELECT fk_type FROM llx_c_affaire_status WHERE rowid = $rstepStatus";
				$resql = $db->query($sql);
				if ($resql) {
					$obj = $db->fetch_object($resql);
					$code = $obj->fk_type;
				}

				// 3- Stock it
				$affaireStatus[$rstepLabel] = array("rowid"=>$rstepRowid, "label"=>$rstepLabel, "status"=> $rstepStatus, "code"=> $code);

				// 4- Look for a open code
				if (99 < $code && $code < 200) {
					$newAffaireStep = $rstepRowid;
					$newAffaireStatus = $rstepStatus;
				}
			}

			// 5- If no open code were found : look for the last step with a status
			if (empty($newAffaireStatus)) {
				$reversed = array_reverse($affaireStatus);
				while (empty($newAffaireStatus)) {
					$rstep = array_shift($reversed);

					$newAffaireStep = $rstep["rowid"];
					$newAffaireStatus = $rstep["status"];
				}
			}
		} else {
			dol_print_error($db);
		}
	} else {
		$newAffaireStep = '';
		$newAffaireStatus = $newStatus;
	}

	// UPDATE AFFAIRE STEP & STATUS
	$sql = "UPDATE llx_affaire_affaire SET fk_status = $newAffaireStatus ";
	if (!empty($newAffaireStep)) { $sql .= ", fk_step = '$newAffaireStep' "; }
	$sql .= "WHERE rowid = $affaire->id";
	$resql = $db->query($sql);
	if ($resql) {
		setEventMessages("Nouveau statut de l'affaire :$newAffaireStatus", null, 'mesgs');
	} else {
		dol_print_error($db);
	}

	// LOOK FOR AUTOMATING
	look_for_automating($affaire, $newStatus, $previousStatus, $workflow, $step);
}

/**
 * Chage Status function is separated in two part for better readability
 *
 * @param Affaire $affaire
 * @param int $newStatus
 * @param int $previousStatus
 * @param object $workflow
 * @param string $step
 * @return void
 */
function look_for_automating($affaire,$newStatus, $previousStatus, $workflow, $step) {
	global $db;

	/** TODO : hook 'automating'
	* This where it became possible to make great automating with Zapier for exemple
	*
	* $parameters = array($newStatus, $previousStatus, $workflow, $step);
	* $reshook = $hookmanager->executeHooks('automating', $parameters, $affaire, 'automation'); // Note that $action and $object may have been modified by some hooks
	* if ($reshook < 0) {
	* 	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
	* }
	*
	* if (empty($reshook)) {
	*/

	$Iwant = false;
	// Create object action
	if ($Iwant) {
		if (getDolGlobalInt('CREATE_ORDER_ON_PROPAL_SIGNATURE_FOR_'.$workflow->label) && $step = 'propal' && 199 < $newStatus->fk_type && $newStatus->fk_type < 300) {
			// TODO
		}
		if (getDolGlobalInt('CLOSE_OTHER_PROPAL_ON_SIGNATURE_FOR_'.$workflow->label) && $step = 'propal' && 199 < $newStatus->fk_type && $newStatus->fk_type < 300) {
			// TODO
		}
		if (getDolGlobalInt('CREATE_XX_FOR_'.$workflow->label) && $step = 'XX' && 000 < $newStatus->fk_type && $newStatus->fk_type < 000) {
			// TODO
		}
		if (getDolGlobalInt('CREATE_XX_FOR_'.$workflow->label) && $step = 'XX' && 000 < $newStatus->fk_type && $newStatus->fk_type < 000) {
			// TODO
		}
	}

	if ($Iwant) {
		// Other status changement induced
		$sql = "SELECT fk_status_has_changed, condition, fk_status_to_change FROM llx_c_affaire_automation WHERE fk_status_has_changed = $newStatus";
		$resql = $db->query($sql);
		if ($resql > 0) {
			while ($r = $db->fetch_object($resql)) {
				change_status($affaire, $r->fk_status_to_change, $r->condition);
			}
		}
	}
}