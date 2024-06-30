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
 * @param object|Propal $object		
 * @return int 						id of the linked affaire if exist
 */
function getLinkedAff($object) {
	$AffID = 0;

	$object->fetchObjectLinked($object->id, $object->element, $object->id, $object->element);

	if (isset($object->linkedObjects["affaire"])) {
		$affaire_array = $object->linkedObjects["affaire"];
		reset($affaire_array);
		$key = key($affaire_array);
		$AffID = $affaire_array[$key]->id;
	}

	return $AffID;
}

/**
 * Print a nav bar with the steps of the specified workflow as tabs
 *
 * @param Affaire $affaire
 * @param object $selectedStep 
 * @param array $affaireStatusbyStep
 * @param object
 * @return string
 */
function dol_workflow_tabs($affaire, $selectedStep, $affaireStatusbyStep='', $Workflow=null) {
	global $langs, $conf, $db;
	if (!$affaireStatusbyStep) {
		$sql = "SELECT * FROM llx_affaire_affaire_status WHERE fk_affaire = $affaire->id";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$affaireStatusbyStep = $db->fetch_object($resql);
			} else {
				setEventMessages($langs->trans("No row in llx_afaire_affaire_status"), null, 'errors');
			}
		} else {
			dol_print_error($db);
		}
	}
	$out = '';

	// TODO if no $workflow rsql  ...

	//var_dump($selectedStep);
	//var_dump($affaireStatusbyStep);

	$out.= '<div class="tabs" data-role="controlgroup" data-type="horizontal" style="border: 1px solid #BBB">';

	$tab ='';

	// Fetch all step
	$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_default_status, position, object, active FROM llx_c_affaire_steps WHERE active = 1 AND fk_workflow_type = $affaire->fk_workflow_type ORDER BY position";
	$resql = $db->query($sql);
	if ($resql) {
		if ($resql->num_rows > 0) {
			while ($Step = $db->fetch_object($resql)) {
				if (!$Step->position) continue;
				$active = ($Step->rowid == $selectedStep->rowid) ? 'tabactive' : 'tabunactive';

				// Fetch status of this step
				$fk_status_thisstep = "fk_status_".strtolower($Step->label_short);
				$thisStatusRowid = isset($affaireStatusbyStep->{"$fk_status_thisstep"}) ? $affaireStatusbyStep->{"$fk_status_thisstep"} : "' '";
				unset($thisStatus);

				$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, status_for, active FROM llx_c_affaire_status WHERE rowid = $thisStatusRowid AND fk_step = '$Step->rowid' AND fk_workflow_type = $affaire->fk_workflow_type";
				$resql2 = $db->query($sql);
				if ($resql2) {
					if ($resql2->num_rows > 0) {
						$thisStatus = $db->fetch_object($resql2);
					}
				} else {
					dol_print_error($db);
				}
				$db->free($resql2);


				$path = '/'.strtolower($Workflow->label).'/'.strtolower($Workflow->label).'_'.strtolower($Step->label_short).'_stateOfPlay.php?affaire='.$affaire->id;
				$card_page = dol_buildpath($path, 1);
				$tab .= '<div class="inline-block tabsElem"><!-- id tab = contact --><div class="tab '.$active.'" style="margin: 0 !important"><a id="'.$Step->label_short.'" class="tab inline-block valignmiddle" href="'.$card_page.'" title="'.$Step->label_short.'">'.$Step->label.(isset($thisStatus) ? printBagde($thisStatus,'mini') : '').'</a></div></div>';
			}
		} else {
			setEventMessages($langs->trans("BeleBele"), null, 'mesg');
		}
	} else {
		dol_print_error($db);
	}
	$out.= $tab;
	
	$out.= '</div>';

	return $out;
}

function affaireBanner($affaire, $selectedStep=null, $affaireStatusbyStep='', $workflow=null) {
	global $langs;
	$thisStep = $affaire->getStep();
	$thisStatus = $affaire->getStatus();

	$out = '';
	
	$out.= '<div style="border-bottom: 4px solid #0A1464; padding: 5px; margin-bottom: 25px; background-color: rgb(117 190 218 / 15%);">';
	
	$head = affairePrepareHead($affaire);
	$out.= dol_get_fiche_head($head, 'affaire', 'Affaire', 1, 'affaire@affaire');

	// $out.= '<div class="refid">'.$affaire->getNomUrl(1, '', 0, "refid").'</div>';
	// $out.= '<table class="border centpercent tableforfield"><tbody>
	// 	<tr><td class="titlefield fieldname_type">Titre</td><td class="valuefield fieldname_type">'.$affaire->title.'</tr>
	// 	<tr><td class="titlefield fieldname_type">Client final</td><td class="valuefield fieldname_type">'.$affaire->final_customer.'</tr>
	// 	<tr><td class="titlefield fieldname_type">Step</td><td class="valuefield fieldname_type">'.$thisStep->label.'</tr>
	// 	<tr><td class="titlefield fieldname_type">Status</td><td class="valuefield fieldname_type">'.printBagde($thisStatus, 'big').'</tr>
	// </tbody></table>';

	$form = new Form($affaire->db);
	dol_include_once('htdocs/societe/class/societe.class.php');
	$usercancreate = 1;

	$picto = $affaire->picto;  // @phan-suppress-current-line PhanUndeclaredProperty
	$prefix = 'object_';
	$nophoto = img_picto('No photo', $prefix.$picto);

	$morehtmlleft = '<!-- No photo to show -->';
	$morehtmlleft .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref">';
	$morehtmlleft .= $nophoto;
	$morehtmlleft .= '</div></div>';


	$morehtmlref = '<div class="refidno">';
	// Ref customer
	$morehtmlref.= '<span class="opacitymedium">'.$affaire->label.'</span>';
	// $morehtmlref .= $form->editfieldkey("Titre", 'label', $affaire->label, $affaire, $usercancreate, 'string', '', 0, 1);
	// $morehtmlref .= $form->editfieldval("Titre", 'label', $affaire->label, $affaire, $usercancreate, 'string', '', null, null, '', 1);
	// Thirdparty
	if ($affaire->fk_soc) {
		$res = $affaire->fetch_thirdparty();
		$morehtmlref .= '<br><span class="hideonsmartphone">'.$langs->trans('ThirdParty').' : </span>'.$affaire->thirdparty->getNomUrl(1, 'customer');
	}

	$morehtmlref .= '</div>';


	//$out.= dol_banner_tab($affaire, 'ref', '', 1, 'ref', 'ref', $morehtmlref);
	$onlybanner = 1;

	$out.= '<div class="'.($onlybanner ? 'arearefnobottom ' : 'arearef ').'heightref valignmiddle centpercent">';
	$out.= $form->showrefnav($affaire, 'ref', '', 0, 'ref', 'ref', $morehtmlref, '', 0, $morehtmlleft, printBagde($thisStatus, 'big'), '');
	$out.= '</div>';
	//$out.= '<div class="underrefbanner clearboth"></div>';

	$out.= dol_workflow_tabs($affaire, $selectedStep, $affaireStatusbyStep, $workflow);

	$out.= dol_get_fiche_end();
	$out.= '</div>';
	$out.= '<div class="fiche">';

	return $out;
}

/**
 * function to get html for bagde of a status
 * 
 * @param object|int $Status
 * @param string $width
 * 
 * @return string html to print
 */
function printBagde($Status, $width) {
	global $db, $langs;

	// TODO check if $status is int and fetch status if it's the case
	if (!is_object($Status) && is_numeric($Status)) {
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, active FROM llx_c_affaire_status WHERE rowid = $Status";
		$resql = $db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			$Status = $db->fetch_object($resql);	
		} else {
			dol_print_error($db);
		}
		$db->free($resql);
	}

	if (is_object($Status)) {
		// STYLE 
		$color = '';
		$sql = "SELECT * FROM llx_c_affaire_status_types WHERE code = ".$Status->fk_type;
		$resql = $db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			$Type = $db->fetch_object($resql);
			$color = "color: #$Type->color !important; ";
			$color .= !empty($Type->border_color) ? "border-color: #$Type->border_color !important; " : "border-color: transparent !important; ";
			$color .= !empty($Type->background_color) ? "background-color: #$Type->background_color !important; " : "background-color: transparent !important; ";
		}
	} else {
		return '<span class="badge badge-status">Pas de status</span>';
	}

	switch ($width) {
		case 'mini':
			return '<span class="badge marginleftonlyshort" style=" '.$color.'">'.$Status->label_short.'</span>';
		case 'small':		
			return '<span class="badge badge-status'.$Status->fk_type.' badge-status" style=" '.$color.'">'.$Status->label_short.'</span>';
		case 'default':
		case 'big':
			return '<span class="badge badge-status'.$Status->fk_type.' badge-status" style=" '.$color.' font-size: 1.1em; padding: .4em .4em;">'.$Status->label.'</span>';
	}
}

/**
 * Change the status of a step of an affaire, 
 * then look for automating (like another status change induced), 
 * finally update affaire status and step
 *
 * @param Affaire $affaire						object affaire to deal with
 * @param object|int $newStatus					rowid, label or obj(rowid, label, label_short, fk_workflow_type, fk_step, fk_type, active) of the new status
 * @param string $condition						a status changement might need to match some condition espacially for automation
 * @param object|int|string $step 				step can be precised for optimisation
 * @param object|int|string $previousStatus 	previousStatus can be precised for optimisation
 * @param object $workflow 						workflow can be precised for optimisation
 * @param object $object 						optional param for an object (Ex: a propal, cmde or project... )
 * @return integer|string 						0 if OK, -1 or $error if not OK
 */
function change_status($affaire, $newStatus, $condition='', $step='', $previousStatus='', $workflow='', $object='') {
	global $db, $langs;
	$error = 0;

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
		$valid = checkConditions($condition, $affaire);
		if (!$valid) {
			$db->rollback();
			return "INVALID_CONDITION";
		}
	}

	// GET INFO
	// --------------------------------- //
	// Workflow
	if (!is_object($workflow)){
		$sql = "SELECT rowid, label FROM llx_c_affaire_workflow_types WHERE rowid = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			$workflow = $db->fetch_object($resql);
		} else {
			$error--;
			dol_print_error($db);
		}
		$db->free($resql);
	}
	// Status
	if (!is_object($newStatus) && is_numeric($newStatus) && !$error) {
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, active FROM llx_c_affaire_status WHERE rowid = $newStatus AND fk_workflow_type = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$newStatus = $db->fetch_object($resql);	

			} else {
				$error--;
				setEventMessages($langs->trans("NoSuchStatus"), null, 'errors');
			}
		} else {
			$error--;
			dol_print_error($db);
		}
		$db->free($resql);
	} else {
		$error--;
		setEventMessages($langs->trans("InvalidStatusProviden"), null, 'errors');
	}
	// Step 
	if (!is_object($step) && !$error) {
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_default_status, position, active FROM llx_c_affaire_steps WHERE ";
		if (is_numeric($step)) { 
			$sql .= "rowid = $step "; 
		} else if (is_string($step)) {
			$sql .= "label_short = '$step' ";
		} else if (empty($step)) {
			$sql .= "rowid = '$newStatus->fk_step' ";
		} else {
			$error--;
			setEventMessages($langs->trans("InvalidStepProviden"), null, 'errors');
		}
		$sql .= "AND fk_workflow_type = $affaire->fk_workflow_type";

		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$step = $db->fetch_object($resql);	
			} else {
				$error--;
				setEventMessages($langs->trans("NoSuchStep"), null, 'errors');
			}
		} else {
			$error--;
			dol_print_error($db);
		}
		$db->free($resql);
	}
	if (empty($previousStatus) && !$error){
		// TODO sql query based on $affaire
	}

	if ($error) {
		return $error;
	}
	$db->commit();

	if ($step->label_short != 'Affaire') {
		//CHANGE STATUS
		$sql = "UPDATE llx_affaire_affaire_status SET fk_status_".strtolower($step->label_short)." = $newStatus->rowid WHERE fk_affaire = $affaire->id";
		$resql = $db->query($sql);
		if ($resql) {
			setEventMessages("Nouveau statut :$newStatus->label", null, 'mesgs');
		} else {
			dol_print_error($db);
			$db->rollback();
			return $error--;
		}
		$db->free($resql);

		// PREPARE UPDATE AFFAIRE STEP & STATUS
		$newAffaireStep = '';
		$newAffaireStatus = '';
		$affaireStatus = array();

		// 1- Fetch steps in good order
		$sql = "SELECT rowid, label_short FROM llx_c_affaire_steps WHERE fk_workflow_type = $workflow->rowid AND position IS NOT NULL ORDER BY position";
		$result = $db->query($sql);
		if ($result) {
			// 2- For each step (starting by the first step) ...
			while (empty($newAffaireStatus)) {
				$rstep = $db->fetch_object($result);
				if (is_null($rstep)) break;
				$rstepLabel = strtolower($rstep->label_short);
				$rstepRowid = strtolower($rstep->rowid);

				// ... fetch step status ...
				$sql = "SELECT fk_status_$rstepLabel FROM llx_affaire_affaire_status WHERE fk_affaire = $affaire->id";
				$resql = $db->query($sql);
				if ($resql) {
					$obj = $db->fetch_object($resql);
					$rstepStatus = $obj->{"fk_status_$rstepLabel"};
				}
				$db->free($resql);

				if (is_null($rstepStatus)) continue;

				// ... then fetch status type code ...
				$sql = "SELECT fk_type FROM llx_c_affaire_status WHERE rowid = $rstepStatus";
				$resql = $db->query($sql);
				if ($resql) {
					$obj = $db->fetch_object($resql);
					$code = $obj->fk_type;
				}
				$db->free($resql);

				// 3- Stock it
				$affaireStatus[$rstepLabel] = array("rowid"=>$rstepRowid, "label"=>$rstepLabel, "status"=> $rstepStatus, "code"=> $code);

				// 4- Look for a open code
				if (100 <= $code && $code <= 199) {
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
			$error--;
			dol_print_error($db);
		}
	} else {
		$newAffaireStep = $newStatus->fk_step;
		$newAffaireStatus = $newStatus->rowid;
	}


	if ($error){
		$db->rollback();
		return $error;
	}

	// UPDATE AFFAIRE STEP & STATUS
	$sql = "UPDATE llx_affaire_affaire SET fk_status = $newAffaireStatus ";
	if (!empty($newAffaireStep)) { $sql .= ", fk_step = '$newAffaireStep' "; }
	$sql .= "WHERE rowid = $affaire->id";
	$resql = $db->query($sql);
	if ($resql) {
		setEventMessages("Nouveau statut de l'affaire :$newAffaireStatus", null, 'mesgs');
	} else {
		$error--;
		dol_print_error($db);
	}
	$db->free($resql);

	// LOOK FOR AUTOMATING
	$error = look_for_automating($affaire, $newStatus, $previousStatus, $workflow, $step, $object);

	if ($error) {
		$db->rollback();
		return $error;
	} else {
		$db->commit();
		return 0;
	}
}

/**
 * Chage Status function is separated in two part for better readability
 *
 * @param Affaire $affaire
 * @param object $newStatus
 * @param object $previousStatus
 * @param object $workflow
 * @param object $step
 * @param object $object
 * @return integer|string			0 if OK, -1 or $error if not OK
 */
function look_for_automating($affaire, $newStatus, $previousStatus, $workflow, $step, $object) {
	global $db, $user;
	$error = 0;

	$sql = "SELECT fk_workflow_type, origin_step, origin_status, conditions, automation_type, new_step, new_status FROM llx_affaire_automation WHERE fk_workflow_type = $workflow->rowid AND (origin_step = $step->rowid OR origin_step = $newStatus->fk_step) AND (origin_status = $newStatus->rowid OR origin_status = 'TYPE:$newStatus->fk_type') ORDER BY priority";
	$resql = $db->query($sql);
	if ($resql) {
		while (!$error && $r = $db->fetch_object($resql)) {
			// CHECK CONDITIONS
			if (!empty($r->conditions)) {
				$valid = checkConditions($r->conditions, $affaire);
				if (!$valid) {
					continue;
				}
			}
			
			/** TODO : hook 'automating'
			 * This is where it became possible to make great automating with Zapier or Make for exemple
			 * By adding a line in llx_affaire_automation with automation_type = 'Zapier' or 'Personnalized' and an hook with an if statement
			 *
			 * $parameters = array($newStatus, $previousStatus, $workflow, $step);
			 * $reshook = $hookmanager->executeHooks('automating', $parameters, $affaire, 'automation'); // Note that $action and $object may have been modified by some hooks
			 * if ($reshook < 0) {
			 * 	setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			 * }
			 *
			 * if (empty($reshook)) {
			 */
			if ($r->automation_type == 'System') {
				// TODO
				if ($r->new_step == 'createOrder') {
					// TODO
					// $result = generateCommande($object->id, $object->element, $object, $affaire, $r->new_status);
					// if (is_string($result) || (is_numeric($result) && $result <= 0)) {
					// 	$error = $result;
					// } else if (is_object($result)) {
					// 	$path = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_cmde_stateOfPlay.php?affaire='.$affaire->id.'&action=changeStatus&newStatus=defaultStatus&status_for=both&&token='.newToken();
					// 	$cmde_page = dol_buildpath($path, 1);
						
					// 	addUrlToOpen($cmde_page);
					// }

					// CHECK COMMANDE ASSOCIED TO AFFAIRE
					$order = checkCommandeExist($affaire);
					if (is_array($order)) {
						$error = "ERROR: L'affaire est associé à plusieurs commandes !!!";
						break;
					}
					if ($order < 0) {
						$error = "ERROR SQL : ".$affaire->db->lasterror();
						break;
					}
					if ($order > 0) {
						$error = "ERROR: Une commande existe déjà !!! Celle-ci n'a pas été mis à jour.";
						break;
					}
					if (empty($order)) {
						$path = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_cmde_stateOfPlay.php?affaire='.$affaire->id.'&action=create&origin='.$object->element.'&originid='.$object->id.'&socid='.$object->socid.'&token='.newToken();
						$cmde_page = dol_buildpath($path, 1);

						addUrlToOpen($cmde_page);
					}
						
				}
				if ($r->new_step == 'generateProd') {
					if (checkProjectExist($affaire) == 0) {
						$changeStatus = 1;
					}
					$result = generateProject($object->id, $object->element, $object, $affaire, $r->new_status);
					if (is_string($result) || (is_numeric($result) && $result <= 0)) {
						$error = $result;
					} else if (is_object($result)) {
						$path = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_prod_stateOfPlay.php?affaire='.$affaire->id.'&id='.$result->id.'&token='.newToken();
						if (!empty($changeStatus)) $path .= '&action=changeStatus&newStatus=defaultStatus&status_for=both';
						$prod_page = dol_buildpath($path, 1);

						addUrlToOpen($prod_page);
					}
				}
				if ($r->new_step == 'closeOtherPropal') {
					$affaire->fetchObjectLinked($affaire->id, $affaire->element, $affaire->id, $affaire->element);

					if (isset($affaire->linkedObjects["propal"])) {
						$propal_array = $affaire->linkedObjects["propal"];
						// If only one linked propal : $id = this propal
						if (count($propal_array) == 1) {
							reset($propal_array);
							$key = key($propal_array);
							if ($object->id != $propal_array[$key]->id) {
								$error = "L'object ne correspond pas à celui lié à l'affaire !!!";
							};
						// If many propal : display a list
						} else if (count($propal_array) > 1) {
							$steplabel = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid);
							$automatic = 1;
							foreach ($propal_array as $key => $value) {
								if ($object->id != $propal_array[$key]->id) {
									$value->fetch_thirdparty();

									// CHANGE SYSTEM STATUS
									if (!getDolGlobalString('GLOBAL_CHANGE_STATUS_WITHOUT_CHANGING_SYSTEM_STATUS') && !getDolGlobalString('PROPAL_CHANGE_STATUS_WITHOUT_CHANGING_SYSTEM_STATUS')) {
										// NOTSIGNED
										// prevent browser refresh from closing proposal several times
										if ($value->statut == $value::STATUS_VALIDATED || ((getDolGlobalString('PROPAL_SKIP_ACCEPT_REFUSE') || $automatic) && $value->statut == $value::STATUS_DRAFT)) {
											$db->begin();
							
											$result = $value->closeProposal($user, Propal::STATUS_NOTSIGNED);
											if ($result < 0) {
												$error = $value->error;
											}
							
											$deposit = null;
							
											$deposit_percent_from_payment_terms = getDictionaryValue('c_payment_term', 'deposit_percent', $value->cond_reglement_id);
				
											if (!$error) {
												$db->commit();
							
												if ($deposit && !getDolGlobalString('MAIN_DISABLE_PDF_AUTOUPDATE')) {
													$ret = $deposit->fetch($deposit->id); // Reload to get new records
													$outputlangs = $langs;
							
													if (getDolGlobalInt('MAIN_MULTILANGS')) {
														$outputlangs = new Translate('', $conf);
														$outputlangs->setDefaultLang($deposit->thirdparty->default_lang);
														$outputlangs->load('products');
													}
							
													$result = $deposit->generateDocument($deposit->model_pdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
							
													if ($result < 0) {
														setEventMessages($deposit->error, $deposit->errors, 'errors');
													}
												}
											}
										} else if ($value->statut != Propal::STATUS_NOTSIGNED) {
											$error = "Impossible mettre le status à non signé (System status : Propal::STATUS_NOTSIGNED) depuis l'ancien status system : ".$value->LibStatut($value->statut);
										}
									}

									// Change extrafield
									if (empty($error)) {
										$value->array_options["options_aff_status"] = $r->new_status;
										$result = $value->updateExtraField('aff_status', 'PROPAL_MODIFY');
										if ($result < 0) {
											$error = $value->error;
										}
									}

									// OLD METHOD
									// $path = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_'.$steplabel.'_stateOfPlay.php?affaire='.$affaire->id.'&id='.$propal_array[$key]->id.'&token='.newToken();
									// $path .= '&action=changeStatus&newStatus='.$r->new_status.'&status_for=object&close_window=1&automatic=1';
									// $page = dol_buildpath($path, 1);

									// addUrlToOpen($page);
								}
							}
						} else if (count($propal_array) == 0) {
							$error = "L'affaire n'a aucune propositions liés !!!";
						}
					}
				}
				if ($r->new_step == 'STRING') {
					// Do domething
				}
				if ($r->new_step == 'STRING') {
					// Do domething
				}
			} else if ($r->automation_type == 'changeStatusStep' || $r->automation_type == 'changeStatus') {
				$error = change_status($affaire, $r->new_status, $r->condition ?? '', $r->new_step);
			} else if ($r->automation_type == 'changeStatusObject' || $r->automation_type == 'changeStatus') {
				$path = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_'.$r->new_step.'_stateOfPlay.php?affaire='.$affaire->id.'&token='.newToken();
				$path .= '&action=changeStatus&newStatus='.$r->new_status.'&status_for=object&close_window=1';
				$page = dol_buildpath($path, 1);

				addUrlToOpen($page);
			}
		}

		// If no error $error should equal 0
		return $error ?? 0;
	} else {
		return $error--;
	}
}

function checkConditions($arg, $affaire) {
	/*
	FUNCTION:functionName($arg):operator(==,!=,>,<,<=,>=):result expected
	TYPE:step(rowid or label_short):operator(==,!=,>,<,<=,>=):status code
	STATUS:step(rowid or label_short):operator(==,!=,>,<,<=,>=):status rowid
	Ex: 
	FUNCTION:checkCommandeExist(__affaire__):==:0

	many conditions can be provided by 
		- joining the codition into a parantese ()
		- and adding & or | in the beginnig (!! A pararentese is either and(&) either or(|) but a sub-condition can be a parentese)
		- and adding && or || to separate each sub-condition 
	Ex:
	(&:FUNCTION:checkCommandeExist($affaire):>=:0:&&:TYPE:cmde:>=:200:&&:(|:STATUS:prod:!=:22:||:STATUS:prod:!=:23))

	in english correspond to if result of checkCommandeExist is superior to 0, and commande status type success or higher, but prod status isn't ___ nor ___
	
	in logic correspond to 
	(1 and 2 and 3) ?= true 
	where
	1 = FUNCTION:checkCommandeExist($affaire):>=:0 ?= true (in english : result of checkCommandeExist is superior to 0)
	2 = TYPE:prod:>=:300 ?= true (in english : commande type success or higher)
	3 = (4 or 5) ?= true (in english : one of the to option is true)

	4 = STATUS:prod:!=:22 ?= true (in english : prod status isn't ___)
	5 = STATUS:prod:!=:23 ?= true (in english : prod status isn't ___)

	*/
	
	
    // Vérifie si l'argument est une combinaison de conditions
    if (preg_match('/^\(|^\&\:|^\|\:/', $arg)) {
        // C'est une combinaison de conditions
        return checkComplexCondition($arg, $affaire);
    } else {
        // C'est une condition simple
        return checkSimpleCondition($arg, $affaire);
    }
}

function checkComplexCondition($arg, $affaire) {
    // // Supprime les parenthèses externes si présentes
    // if ($arg[0] == '(' && $arg[strlen($arg) - 1] == ')') {
    //     $arg = substr($arg, 1, -1);
    // }

    // // Divise les sous-conditions en utilisant && ou ||
    // $conditions = preg_split('/(\&\&|\|\|)/', $arg, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
    // $result = null;
    // $operator = null;

	// Vérifie si la chaîne commence par ( et capture l'opérateur principal (& ou |)
    if (preg_match('/^\((\&|\|):/', $arg, $matches)) {
        $operator = $matches[1];
    } else {
		throw new Exception("Format incorrect");
    }

    $arg = substr($arg, 2, -1); // Supprime l'opérateur principal et les parenthèses autour de la chaîne
    
    $conditions = [];
    $currentCondition = '';
    $depth = 0;
    $length = strlen($arg);
    
    for ($i = 0; $i < $length; $i++) {
        $char = $arg[$i];

        if ($char === '(') {
            $depth++;
        } elseif ($char === ')') {
            $depth--;
        }

        if ($depth === 0 && ($arg[$i] === $operator && $arg[$i + 1] === $operator)) {
            // Sépare la condition en utilisant le séparateur && ou ||
            $conditions[] = trim($currentCondition);
            $currentCondition = '';
            $i++; // Skip the next & or |
        } else {
            $currentCondition .= $char;
        }
    }
    
    if ($currentCondition !== '') {
        $conditions[] = trim($currentCondition);
    }

	
	$result = null;
    foreach ($conditions as $condition) {
		// Supprime les ":" au début et à la fin si présent
		if ($condition[0] == ':') {
			$condition = substr($condition, 1);
		}
		if ($condition[strlen($condition) - 1] == ':') {
			$condition = substr($condition, 0, -1);
		}

		$subConditionResult = checkConditions(trim($condition), $affaire);
		
		if ($result === null) {
			$result = $subConditionResult;
		} else {
			if ($operator == '&') {
				$result = $result && $subConditionResult;
			} else if ($operator == '|') {
				$result = $result || $subConditionResult;
			}
		}
    }

    return $result;
}

function checkSimpleCondition($arg, $affaire) {
    // Décompose l'argument en parties de la condition simple
    $parts = explode(':', $arg);
    $type = $parts[0];
    $subject = $parts[1];
    $operator = $parts[2];
    $expected = $parts[3];

    // Exécution et vérification de la condition en fonction du type
    switch ($type) {
        case 'FUNCTION':
            // Appelle la fonction spécifiée avec ses arguments
            // $functionParts = explode('(', rtrim($subject, ')'));
            // $functionName = $functionParts[0];
            // $functionArgs = explode(',', $functionParts[1]);
            // $result = call_user_func_array($functionName, $functionArgs);
			if ($subject == 'checkCommandeExist(__affaire__)') {
				$result = checkCommandeExist($affaire);
			} else {
				throw new Exception("Incorrect function");
			}
            break;
        case 'TYPE':
            // Récupère la valeur de type spécifié
            $status = $affaire->getStatus($subject);
			$result = $status->fk_type;
            break;
        case 'STATUS':
            // Récupère la valeur de statut spécifié
            $status = $affaire->getStatus($subject);
			$result = $status->rowid;
            break;
        default:
            throw new Exception("Type inconnu : $type");
    }

    // Compare le résultat avec la valeur attendue
    switch ($operator) {
        case '==':
            return $result == $expected;
        case '!=':
            return $result != $expected;
        case '>':
            return $result > $expected;
        case '<':
            return $result < $expected;
        case '>=':
            return $result >= $expected;
        case '<=':
            return $result <= $expected;
        default:
            throw new Exception("Opérateur inconnu : $operator");
    }
}

/**
 * Stock url to open them in a new tab
 */
function addUrlToOpen($url) {
    global $urlsToOpen;
    $urlsToOpen[] = $url;
}

/**
 * Open stocked url in a new tab
 */
function injectOpenUrlsScript() {
	if (!empty($_SESSION['urlsToOpen'])) {
		echo '<script type="text/javascript">';
		foreach ($_SESSION['urlsToOpen'] as $url) {
			echo 'window.open("' . $url . '", "_blank");';
		}
		echo '</script>';
		// Nettoyer la session après utilisation
		unset($_SESSION['urlsToOpen']);
	}
}

function generateCommande($affaire, $propal) {
	// CHECK COMMANDE ALREADY EXIST
	$result = checkCommandeExist($affaire);
	/*
	if no result -> create

	else if result and fk_import = propal id  -> update

	else return error = affaire can't have several commande
	*/

	/* triger from workflow module:
	if (isModEnabled('order') && getDolGlobalString('WORKFLOW_PROPAL_AUTOCREATE_ORDER')) {
		$object->fetchObjectLinked();
		if (!empty($object->linkedObjectsIds['commande'])) {
			if (empty($object->context['closedfromonlinesignature'])) {
				$langs->load("orders");
				setEventMessages($langs->trans("OrderExists"), null, 'warnings');
			}
			return $ret;
		}

		include_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
		$newobject = new Commande($this->db);

		$newobject->context['createfrompropal'] = 'createfrompropal';
		$newobject->context['origin'] = $object->element;
		$newobject->context['origin_id'] = $object->id;

		$ret = $newobject->createFromProposal($object, $user);
		if ($ret < 0) {
			$this->setErrorsFromObject($newobject);
		}

		$object->clearObjectLinkedCache();

		return (int) $ret;
	}
	*/
	// 
}

/**
 * Check if affaire already have an order linked (an affaire equal to only one commande)
 * 
 * @param Affaire $affaire
 * @return int -1 if KO : many order | 0 if no order | $id if order 
 */
function checkCommandeExist($affaire) {
	// OLD WAY
	// $affaire->fetchObjectLinked($affaire->id, $affaire->element, $affaire->id, $affaire->element);
	
	// if (isset($affaire->linkedObjects["commande"])) {
	// 	if (count($affaire->linkedObjects["commande"]) > 1) {
	// 		return -1;
	// 	} else {
	// 		reset($affaire->linkedObjects["commande"]);
	// 		$key = key($affaire->linkedObjects["commande"]);
	// 		$id = $affaire->linkedObjects["commande"][$key]->id;
	// 		return $id;
	// 	}
	// } else {
	// 	return 0;
	// }

	$sql = "SELECT rowid, fk_source, sourcetype, fk_target, targettype";
	$sql .= " FROM ".$affaire->db->prefix()."element_element";
	$sql .= " WHERE (fk_source = ".((int) $affaire->id)." AND sourcetype = '".$affaire->db->escape($affaire->element)."' AND targettype = 'commande')";
	$sql .= " OR (fk_target = ".((int) $affaire->id)." AND targettype = '".$affaire->db->escape($affaire->element)."' AND sourcetype = 'commande')";
	$resql = $affaire->db->query($sql);
	if ($resql) {
		if ($resql->num_rows > 1) {
			// KO trop de commande
			return -1;
		} else if ($resql->num_rows < 1) {
			// OK
			return 0;
		} else {
			// return commande
			$res = $affaire->db->fetch_object($resql);
			if ($res->sourcetype == $affaire->db->escape($affaire->element)) {
				return $res->fk_target;
			} else {
				return $res->fk_source;

			}
		}
	} else {
		dol_print_error($affaire->db);
		return -1;
	}
}

/**
 * Check if affaire already have a propal linked
 * 
 * @param Affaire $affaire
 * @return int -1 if KO : many propal | 0 if no propal | $id if propal 
 */
function checkPropalExist($affaire) {
	return checkObjectLinkToAffaire($affaire, 'propal');
}
/**
 * Check if affaire already have a prooject linked
 * 
 * @param Affaire $affaire
 * @return int -1 if KO : many prooject | 0 if no prooject | $id if prooject 
 */
function checkProjectExist($affaire) {
	return checkObjectLinkToAffaire($affaire, 'project');
}

/**
 * Check if affaire have objects linked
 * 
 * @param Affaire $affaire
 * @param string $ObjectTYPE 'propal', 'commande', 'project', 'shipment', '
 * @return int|array -1 if KO | 0 if no object | $id if object | array of $id if many object
 */
function checkObjectLinkToAffaire($affaire, $objectTYPE='') {
	$sql = "SELECT rowid, fk_source, sourcetype, fk_target, targettype";
	$sql .= " FROM ".$affaire->db->prefix()."element_element";
	$sql .= " WHERE (fk_source = ".((int) $affaire->id)." AND sourcetype = '".$affaire->db->escape($affaire->element)."'".(!empty($objectTYPE) ? " AND targettype = '$objectTYPE')" : ")");
	$sql .= " OR (fk_target = ".((int) $affaire->id)." AND targettype = '".$affaire->db->escape($affaire->element)."'".(!empty($objectTYPE) ? " AND targettype = '$objectTYPE')" : ")");
	$resql = $affaire->db->query($sql);
	if ($resql) {
		if ($resql->num_rows > 1) {
			// Plusieurs objects
			$array_of_id = array();
			while ($res = $affaire->db->fetch_object($resql)) {
				if ($res->sourcetype == $affaire->db->escape($affaire->element)) {
					$array_of_id[] = $res->fk_target;
				} else {
					$array_of_id[] = $res->fk_source;
				}
				$array_of_id[] = -1;
			}
			return $array_of_id;
		} else if ($resql->num_rows < 1) {
			// Pas d'objets
			return 0;
		} else {
			// Un seul objet
			$res = $affaire->db->fetch_object($resql);
			if ($res->sourcetype == $affaire->db->escape($affaire->element)) {
				return $res->fk_target;
			} else {
				return $res->fk_source;
			}
		}
	} else {
		dol_print_error($affaire->db);
		return -1;
	}
}

/**
 * Check if affaire have linked object
 * 
 * @param Affaire $affaire
 * @return int -1 if KO | 0 if no oobject
 */
function checkCanDeleteAffaire($affaire) {
	if (empty(checkObjectLinkToAffaire($affaire))) {
		return 0;
	} else {	
		return -1;
	}
}

/**
 * function to create a project as production for a given propal or commande
 *
 * @param int $id							$id of the origin object
 * @param string $element					'propal' or 'commande'
 * @param Propal|Commande $object			origin object (optional for optimization)
 * @param Affaire $affaire					affaire to link
 * @param string|int $defaultStepStatus		default status for object projet
 * @return integer|string|Project			$Project if OK, 1 or $error if not OK
 */
function generateProject($id, $element, $object='', $affaire=false, $defaultStepStatus='', $leader=0, $groupid=0, $addToTask=0) {
	global $db, $user, $langs, $conf;
	$langs->load("projects");
	require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
	require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
	$error = 0;

	// Fetch object
	if (!$object) {
		if ($element == 'propal') $object = new Propal($db);
		if ($element == 'commande') $object = new Commande($db);
		$object->fetch($id);
	}

	// Check delivery date
	if (getDolGlobalInt("CANOT_CREATE_PROJECT_IF_NO_DELIVERY_DATE") && !$object->delivery_date) {
		setEventMessage("IMPOSSIBLE DE CREER LE PROJET : La date livraison est vide dans la commande ou proposition.",'errors');
		return $error--; 
	}
	// Check Leader
	if (getDolGlobalInt('PROJECT_LEADER_MUST_BE_DEFINED') && empty($leader)) {
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->trans("TypeContact_project_internal_PROJECTLEADER")), null, 'errors');
		$error--;
	}

	$Projet = new Project($db);
	if ($object->fk_project) {
		$Projet->fetch($object->fk_project);
	} else {
		// REF
		if (getDolGlobalInt("USE_CMDE_REF_FOR_PROJECT_REF")) {
			$CmdeRef=explode("-",$object->ref);
			$Projet->ref = 'P_' . $CmdeRef[0];
		} else {
			//Generate next ref
			$defaultref = '';
			$obj = !getDolGlobalString('PROJECT_ADDON') ? 'mod_project_simple' : $conf->global->PROJECT_ADDON;
			// Search template files
			$file = '';
			$dirmodels = array_merge(array('/'), (array) $conf->modules_parts['models']);
			foreach ($dirmodels as $reldir) {
				$file = dol_buildpath($reldir."core/modules/project/".$obj.'.php', 0);
				if (file_exists($file)) {
					dol_include_once($reldir."core/modules/project/".$obj.'.php');
					$modProject = new $obj();
					$defaultref = $modProject->getNextValue(is_object($object->fk_soc) ? $object->fk_soc : null, '');
					break;
				}
			}
			if (is_numeric($defaultref) && $defaultref <= 0) {
				$defaultref = '';
			}

			$Projet->ref = $defaultref;
		}	
	}
	
	// Delivery date
	$Projet->date_end = $object->delivery_date;

	// Thirdparty
	$object->fetch_thirdparty();
	$Projet->socid = $object->thirdparty->id;
	$Projet->thirdparty_name = $object->thirdparty->name;

	// Title
	if (getDolGlobalInt("USE_PRODUCT_FOR_PROJECT_TITLE")) {
		$Projet->title = trim(substr ($Projet->thirdparty_name,0,7)); // commence par le nom de la societe

		// recherche des produits
		if ($object->element == 'commande'){
    		$sql2 = "SELECT cmdep.rowid, cmdep.fk_commande, cmdep.fk_product, cmdep.description ";
    		$sql2.= 'FROM '.MAIN_DB_PREFIX.'commandedet AS cmdep ';
    		$sql2.= 'WHERE cmdep.fk_commande='.$object->rowid;
		} else if ($object->element == 'propal') {
		    $sql2 = "SELECT propaldet.rowid, propaldet.fk_propal, propaldet.fk_product, propaldet.description ";
		    $sql2.= 'FROM '.MAIN_DB_PREFIX.'propaldet AS propaldet ';
		    $sql2.= 'WHERE propaldet.fk_propal='.$object->rowid;
		    
		}
		$resql2 = $db->query($sql2);
		if ($resql2 && $resql2->num_rows > 0) {
			// on boucle sur les commande non enregistree en projet
			while ($obj2 = $db->fetch_object($resql2)) {
				if ($obj2->fk_product != NULL) {
					// si produit on recherche ref du produit
					$sql3 = "SELECT p.rowid, p.ref ";
					$sql3.= 'FROM '.MAIN_DB_PREFIX.'product AS p ';
					$sql3.= 'WHERE p.rowid='.$obj2->fk_product;
			
					$resql3 = $db->query($sql3);
					if ($resql3 && $resql3->num_rows > 0) {
						$obj3 = $db->fetch_object($resql3);
						$Projet->title.= '-'.trim(substr ($obj3->ref,0,4));
					}
					$db->free($resql3);
				}
				else {
					// Si pas de ref on prend la descrition libre
					$Projet->title.= '-'.trim(substr ($obj2->description,0,4));
				}
			}
		}
		$db->free($resql2);
				
		$Projet->title = trim(substr ($Projet->title,0,20));
	} 
	// Else re-use affaire title
	if (!$Projet->title){
		// TODO 
		$Projet->title = $Projet->ref;
	}

	$Projet->public = getDolGlobalInt('GENERATED_PROJECT_ARE_PUBLIC') ?? 0;

	
	$db->begin();

	// Creation ou Update
	if ($object->fk_project) {
		$res = $Projet->update($user);
		$ProjetRowid = $object->fk_project;
	} else {
		// Extrafields for affaire
		if ($affaire) {
			$Projet->array_options["options_fk_affaire"] = $affaire->id;
			$Projet->array_options["options_aff_status"] = $defaultStepStatus;
		}

		$res = $ProjetRowid = $Projet->create($user);

		if ($res > 0 && $affaire) $res = $Projet->add_object_linked($affaire->element, $affaire->id);
	}
	if ($res <= 0 || $ProjetRowid <= 0) {
		$error_message = $db->lasterror();
		setEventMessage("IMPOSSIBLE DE CREER OU DE METTRE-A-JOUR LE PROJET : $error_message", 'errors');
		$db->rollback();
		return -1;
	}

	// Maj fk_projet de la commande ou propal
	$sql = 'UPDATE '.MAIN_DB_PREFIX.$object->element;
	$sql.= ' SET fk_projet = '.$ProjetRowid;
	$sql.= ' WHERE rowid = '.$object->id;
	$result=$db->query($sql);
	$db->free($result);

	// Maj fk_projet de la propal si lien entre commande et propal
	if ($object->element == 'commande') {
		$sql2 = "SELECT e.fk_source ";
		$sql2.= 'FROM '.MAIN_DB_PREFIX.'element_element AS e ';
		$sql2.= 'WHERE e.targettype="commande" AND e.sourcetype="propal" AND e.fk_target='.$object->id;
		$result2=$db->query($sql2);
		if ($result2 && $result2->num_rows > 0) {
			$obj = $db->fetch_object($result2);
			
			$sql3='UPDATE '.MAIN_DB_PREFIX.'propal';
			$sql3.= ' SET fk_projet = '.$ProjetRowid;
			$sql3.= ' WHERE rowid = '.$obj->fk_source;
			$result3=$db->query($sql3);
			$db->free($result3);
		}
		$db->free($result2);
	}


	// // Mise a jour du status a termine des extrafiels existants
	// $sqlupdate='UPDATE '.MAIN_DB_PREFIX.'projet_task_extrafields AS extra, '.MAIN_DB_PREFIX.'projet_task AS tache';
	// $sqlupdate.= ' SET extra.statut_fab=4 ,extra.statut_adm=11 ' ;
	// $sqlupdate.= ' WHERE extra.fk_object=tache.rowid AND tache.fk_projet='.$ProjetRowid;
	// $resultupdate=$db->query($sqlupdate);
	// $db->free($resultupdate);


	// Ajout des taches en fonction des items de la commande
	// 1- recherche des produits ...
	$sql = "SELECT objdet.rowid, objdet.fk_commande, objdet.fk_product, objdet.description ";
    $sql.= 'FROM '.MAIN_DB_PREFIX.$object->element.'det AS objdet ';
	$sql.= 'WHERE objdet.fk_'.$object->element.'='.$object->id;
	$resql = $db->query($sql);
	if ($resql && $resql->num_rows > 0) {
		while ($objdet = $db->fetch_object($resql)){
			// Check if it's a product
			if ($objdet->fk_product != NULL) {
				// recherche ref du produit mais qui ne sont pas du PORT = cat 39
				$sql2 = "SELECT p.rowid, p.ref, p.label ";
				$sql2.= 'FROM '.MAIN_DB_PREFIX.'product AS p ';
				$sql2.= 'WHERE p.rowid='.$objdet->fk_product;
				
				$resql2 = $db->query($sql2);
				if ($resql2 && $resql2->num_rows > 0) {
					$obj2 = $db->fetch_object($resql2);

					if (isModEnabled('serem')){
						$NewTaskName= SeremTaskNaming($objdet, $obj2);
					} else {
						$ref_mini = substr ($obj2->ref,0,10);

						$vowels = array(".",',',":","!","?","(",")","[","]","\\","-","&&","==","+","/","*",'"',"<",">");
							
						$Label = "_".substr ($objdet->description,0,30);
						$Label = str_replace($vowels, "_", $Label);

						$NewTaskName=$ref_mini.$Label;
					}

					$NewTaskDescription=$obj2->label;
				}
				$db->free($resql2);
			}
			else
			{
				$NewTaskName=substr ($objdet->description,0,30);
				$NewTaskDescription=$objdet->description;
			}	

			
			// Mise a jour du status a  des extrafiels existants
			$sqlupdate='UPDATE '.MAIN_DB_PREFIX.'projet_task_extrafields AS Extra, '.MAIN_DB_PREFIX.'projet_task AS Tache';
			$sqlupdate.= ' SET Extra.statut_fab=0 ';
			$sqlupdate.= ' WHERE Extra.fk_object=Tache.rowid AND Tache.note_private='.$objdet->rowid;
			$resultupdate=$db->query($sqlupdate);
			$db->free($resultupdate);
			
			
			if ($NewTaskName!='')
			{
				// Mise a jour du status a  des extrafiels existants
				$sqlupdate='UPDATE '.MAIN_DB_PREFIX.'projet_task AS Tache';
				$sqlupdate.= ' SET Tache.description="'.$NewTaskDescription.'", Tache.label="'.$NewTaskName.'"';
				$sqlupdate.= ' WHERE Tache.note_private="'.$objdet->rowid.'"';
				$resultupdate=$db->query($sqlupdate);
				$db->free($resultupdate);
				
				
				$Task = new Task($db);
			
				if (isModEnabled('serem')) {
					$Task->ref=TaskMarbreSerem();
				} else {
					$defaultref = '';
					$obj = !getDolGlobalString('PROJECT_TASK_ADDON') ? 'mod_task_simple' : $conf->global->PROJECT_TASK_ADDON;
					if (getDolGlobalString('PROJECT_TASK_ADDON') && is_readable(DOL_DOCUMENT_ROOT."/core/modules/project/task/" . getDolGlobalString('PROJECT_TASK_ADDON').".php")) {
						require_once DOL_DOCUMENT_ROOT."/core/modules/project/task/" . getDolGlobalString('PROJECT_TASK_ADDON').'.php';
						$modTask = new $obj();
						$defaultref = $modTask->getNextValue(0, $Task);
					}
					$Task->ref = $defaultref;
				}
				$Task->fk_project=$ProjetRowid;
				$Task->date_start=$Projet->date_start;
				$Task->date_end=$Projet->date_end;
				$Task->label=$NewTaskName;
				$Task->note_private=$objdet->rowid;  // Lien entre la tache et le poste de la commande dont elle est issue !Attention lien perdu si modification de la commande initiale
				// $GroupId=$obj->rowid;
				
				
				
				// Ne pas inserer une nouvelle tache identique
				$sqlsearch = "SELECT task.rowid,task.fk_projet,task.label,task.note_private ";
				$sqlsearch.= 'FROM '.MAIN_DB_PREFIX.'projet_task AS task ';
				$sqlsearch.= 'WHERE task.fk_projet='.$Task->fk_project.' AND task.note_private="'.$Task->note_private.'"';
				$resqlsearch = $db->query($sqlsearch);
				
				
				if ($resqlsearch){
					$objsearch = $db->fetch_object($resqlsearch);
					if ($resqlsearch->num_rows > 0) {
						// trouver donc Ne pas inserer nouvelle tache
						$TaskRowid = $objsearch->rowid;

						// update leader
						if ($object->fk_project_leader && getDolGlobalInt("SET_PROJECT_LEADER_ON_CREATION")) {		
							
							$sqlupdate='UPDATE '.MAIN_DB_PREFIX.'element_contact';
							$sqlupdate.= ' SET fk_socpeople='.$object->fk_project_leader;
							$sqlupdate.= ' WHERE statut=4 AND fk_c_type_contact=180 AND element_id='.$TaskRowid;
							$result=$db->query($sqlupdate);
							$db->free($result);
						}					
					} else {
						$result = $TaskRowid = $Task->create($user);
					
						// Ajout leader comme cdp de la tache pour qu'il ai les droits
						if ($result > 0 && $object->fk_project_leader && getDolGlobalInt("SET_PROJECT_LEADER_ON_CREATION")) {
							// Declaration comme intervenent les membres du groupes  180=TASKLEADER INTERNAL
							$sql = 'INSERT  INTO '.MAIN_DB_PREFIX.'element_contact (statut, element_id , fk_c_type_contact, fk_socpeople)';
							$sql.= ' values (4, "'.$TaskRowid.'", 180, "'.$object->fk_project_leader.'")';
							$result=$db->query($sql);
							$db->free($result);
						}
					}
					if ($result <= 0) {
						$error_message = $db->lasterror();
						setEventMessage("IMPOSSIBLE DE CREER LA TACHE : $error_message", 'errors');
						$db->rollback();
						return -1;
					}
					
					
					// Mise a jour des statuts de chaque ligne de la tache
					$sqlexist = "SELECT fk_object ";
					$sqlexist.= 'FROM '.MAIN_DB_PREFIX.'projet_task_extrafields ';
					$sqlexist.= 'WHERE fk_object='.$TaskRowid;
					
					$resqlexist = $db->query($sqlexist);
					if ($resqlexist){
						if ($db->num_rows($resqlsearch)==1) {
							$sqlupdate='UPDATE '.MAIN_DB_PREFIX.'projet_task_extrafields';
							$sqlupdate.= ' SET statut_fab=0 ,statut_adm=0 ' ;
							$sqlupdate.= ' WHERE fk_object='.$TaskRowid;
							$resultupdate=$db->query($sqlupdate);
							$db->free($resultupdate);
						} else {
							$sqldel='DELETE FROM '.MAIN_DB_PREFIX.'projet_task_extrafields';
							$sqldel.= ' WHERE  fk_object = '.$TaskRowid;
							$ressqldel=$db->query($sqldel);
							$db->free($ressqldel);
							
							$sqlinsert = 'INSERT INTO '.MAIN_DB_PREFIX.'projet_task_extrafields (fk_object, statut_fab)';
							$sqlinsert.= ' values ('.$TaskRowid.', 0)';
							$result=$db->query($sqlinsert);
							$db->free($result);
						}
					}
					$db->free($resqlexist);
					$db->free($resqlsearch);
				}
				else {
					$error_message = $db->lasterror();
					setEventMessage("SQL ERROR : $error_message", 'errors');
					$db->rollback();
					return -1;
				}
			}
		}
	}

	// Add every body or a specifique groupe as contact
	if (getDolGlobalInt('FORCE_ADD_EVERYONE_AS_CONTACT_ON_PROJECT_CREATION')) {
		$sql = "SELECT u.rowid ";
		$sql.= 'FROM '.MAIN_DB_PREFIX.'user AS u ';
		$sql.= 'WHERE u.statut=1';
		$resql=$db->query($sql);
		if ($resql && $resql->num_rows > 0) {
			while ($obj2 = $db->fetch_object($resql)) {
				// Declaration comme intervenant  161=PROJECTCONTRIBUTOR INTERNAL
				$sql2 = 'INSERT INTO ' . MAIN_DB_PREFIX . 'element_contact (statut, element_id, fk_c_type_contact, fk_socpeople)';
				$sql2 .= ' VALUES (4, "' . $ProjetRowid . '", 161, "' . $obj2->rowid . '")';
				$sql2 .= ' ON DUPLICATE KEY UPDATE ';
				$sql2 .= ' statut = VALUES(statut), ';
				$sql2 .= ' element_id = VALUES(element_id), ';
				$sql2 .= ' fk_c_type_contact = VALUES(fk_c_type_contact), ';
				$sql2 .= ' fk_socpeople = VALUES(fk_socpeople)';

				$result2=$db->query($sql2);
				$db->free($result2);
			}
		}
		$db->free($resql);
	} else if ($groupid && $groupid > 0) {
		$typeid = 161; // Declaration comme intervenant  161=PROJECTCONTRIBUTOR INTERNAL
		$contactarray = array();
		$errorgroup = 0;
		$errorgrouparray = array();

		
		require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';
		$usergroup = new UserGroup($db);
		$result = $usergroup->fetch($groupid);
		if ($result > 0) {
			$tmpcontactarray = $usergroup->listUsersForGroup();
			if ($contactarray <= 0) {
				$error++;
			} else {
				foreach ($tmpcontactarray as $tmpuser) {
					$contactarray[] = $tmpuser->id;
				}
			}
		} else {
			$error++;
		}

		if (!$error && $ProjetRowid > 0) {
			foreach ($contactarray as $key => $contactid) {
				$result = $Projet->add_contact($contactid, $typeid, 'internal');

				if ($result == 0) {
					// if ($groupid > 0) {
					// 	$errorgroup++;
					// 	$errorgrouparray[] = $contactid;
					// } else {
					// 	$langs->load("errors");
					// 	setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
					// }
				} elseif ($result < 0) {
					if ($Projet->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
						// if ($groupid > 0) {
						// 	$errorgroup++;
						// 	$errorgrouparray[] = $contactid;
						// } else {
						// 	$langs->load("errors");
						// 	setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
						// }
					} else {
						setEventMessages($Projet->error, $Projet->errors, 'errors');
					}
				}

				$taskstatic = new Task($db);
				$task_array = $taskstatic->getTasksArray(0, 0, $Projet->id, 0, 0);
				if ($addToTask && !empty($task_array)) {
					foreach ($task_array as $key => $tasksToAffect) {
						$result = $tasksToAffect->add_contact($contactid, 181, 'internal'); // 181 => TASKEXECUTIVE => Intervenant
						if ($result < 0) {
							if ($tasksToAffect->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
								// $langs->load("errors");
								// setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
							} else {
								setEventMessages($tasksToAffect->error, $tasksToAffect->errors, 'errors');
							}
						}
					}
				}
			}
		}
		// if ($errorgroup > 0) {
		// 	$langs->load("errors");
		// 	if ($errorgroup == count($contactarray)) {
		// 		setEventMessages($langs->trans("ErrorThisGroupIsAlreadyDefinedAsThisType"), null, 'errors');
		// 	} else {
		// 		$tmpuser = new User($db);
		// 		foreach ($errorgrouparray as $key => $value) {
		// 			$tmpuser->fetch($value);
		// 			setEventMessages($langs->trans("ErrorThisContactXIsAlreadyDefinedAsThisType", dolGetFirstLastname($tmpuser->firstname, $tmpuser->lastname)), null, 'errors');
		// 		}
		// 	}
		// }
	}

	// Project Leader
	if ($leader && $leader > 0) {
		$typeid = 160; // Declaration du chef de projet  160=PROJECTLEADER
		
		if (getDolGlobalInt('ONLY_ONE_LEADER')) {
			// Suppression des anciens chefs de projet
			$sqldel='DELETE FROM '.MAIN_DB_PREFIX.'element_contact';
			$sqldel.= ' WHERE  element_id = '.$ProjetRowid;
			$sqldel.= ' AND fk_c_type_contact = '.$typeid;
			$ressqldel=$db->query($sqldel);
			$db->free($ressqldel);
		}

		$result = $Projet->add_contact($leader, $typeid, 'internal');
		if ($result < 0 && $Projet->error != 'DB_ERROR_RECORD_ALREADY_EXISTS') {
			setEventMessages($Projet->error, $Projet->errors, 'errors');
		}

		$taskstatic = new Task($db);
		$task_array = $taskstatic->getTasksArray(0, 0, $Projet->id, 0, 0);
		if ($addToTask && !empty($task_array)) {
			foreach ($task_array as $key => $tasksToAffect) {

				if (getDolGlobalInt('ONLY_ONE_LEADER')) {
					// Suppression des anciens chefs de projet
					$sqldel='DELETE FROM '.MAIN_DB_PREFIX.'element_contact';
					$sqldel.= ' WHERE  element_id = '.$tasksToAffect->id;
					$sqldel.= ' AND fk_c_type_contact = 180';
					$ressqldel=$db->query($sqldel);
					$db->free($ressqldel);
				}

				$result = $tasksToAffect->add_contact($leader, 180, 'internal'); // 180 => TASKEXECUTIVE => Responsable
				if ($result < 0) {
					if ($tasksToAffect->error == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
						// $langs->load("errors");
						// setEventMessages($langs->trans("ErrorThisContactIsAlreadyDefinedAsThisType"), null, 'errors');
					} else {
						setEventMessages($tasksToAffect->error, $tasksToAffect->errors, 'errors');
					}
				}
			}
		}
	}


	if ($error) {
		$db->rollback();
		return $error;
	}
	$db->commit();
	return $Projet;
}

/**
 * Function to change (or set) the project leader
 * 
 * @param Project $object Project
 */
function setProjectLeader ($object, $UserId) {
	global $db;
	$ProjetRowid = $object->id;

	// 160=PROJECTLEADER - 161=PROJECTCONTRIBUTOR INTERNAL

	// Downgrade previous leader (if exist) 
	$sqldel='DELETE FROM '.MAIN_DB_PREFIX.'element_contact';
	$sqldel.= ' WHERE  element_id = '.$ProjetRowid;
	$sqldel.= ' AND fk_c_type_contact = 160';
	$ressqldel=$db->query($sqldel);
	$db->free($ressqldel);
	
	// Declaration of new project leader
	$sql = 'INSERT  INTO '.MAIN_DB_PREFIX.'element_contact (statut, element_id , fk_c_type_contact, fk_socpeople)';
	$sql.= ' values (4, "'.$ProjetRowid.'", 160, "'.$UserId.'")';
	$result=$db->query($sql);
	$db->free($result);



}

/**
 * Function specific of Serem to name task
 * 
 * @param object $objectdet	
 * @param object $obj2
 * @return string $NewTaskName
 */
function SeremTaskNaming ($objdet, $obj2) {
	global $db;

	// Il ne faut pas que le produit soit de categorie PORT = cat 39 DISCOUNT = 44
	$sql = "SELECT cat.fk_categorie, cat.fk_product ";
	$sql.= 'FROM '.MAIN_DB_PREFIX.'categorie_product AS cat ';
	$sql.= 'WHERE cat.fk_product='.$objdet->fk_product.' AND (cat.fk_categorie=39 OR cat.fk_categorie=44)';
		
	$resql = $db->query($sql);
	if ($resql && $db->num_rows($resql)==0) {
		$Initial=substr ($obj2->ref,0,2);
		if ( $Initial==="A_" || $Initial==="M_" || $Initial==="R_" ) {
			$ref_mini = substr ($obj2->ref,2,10);
		} else {
			$ref_mini = substr ($obj2->ref,0,10);
		}

		if ( $Initial==="A_" || $Initial==="M_" ) {
			$Label = substr ($obj2->label,0,30);
			$Label = "_".strtr($Label,"- ","__");
		} else if ( $Initial==="R_") {
			$Label = "";
		} else {
			$vowels = array(".",',',":","!","?","(",")","[","]","\\","-","&&","==","+","/","*",'"',"<",">");
			
			$Label = "_".substr ($objdet->description,0,30);
			$Label = str_replace($vowels, "_", $Label);
		}


		$NewTaskName=$ref_mini.$Label;
	} else {
		$NewTaskName='';
	}
	
	$db->free($resql);

	return $NewTaskName;
}

function TaskMarbreSerem()
{
	global $db,$conf;
	$prefix='T';

	$date=time(); // adaptation SEREM
	$yy = date("y",$date);
	$ddd = sprintf("%03s",date("z",$date) +1);

	// D'abord on recupere la valeur max
	$sql = "SELECT MAX(SUBSTRING(ref,7,2)) as max";	// This is standard SQL
	$sql.= " FROM ".MAIN_DB_PREFIX."projet_task";
	$sql.= " WHERE left(ref, 6) = '".$prefix.$yy.$ddd."'";

	$resql=$db->query($sql);
	if ($resql)
	{
		$obj = $db->fetch_object($resql);
		if ($obj) $max = intval($obj->max);
		else $max=0;
	}
	else
	{
		dol_syslog("generateproject::getNextValue sql=".$sql);
		return -1;
	}

	$date = time();
	$yymm = strftime("%y%m",$date);
	$num = sprintf("%02s",$max+1);
	// on r�cup�re le nom du client
	return $prefix.$yy.$ddd.$num;

}

/**
 * Return HTML string to put an input field into a page
 * Code very similar with showInputField of common object
 *
 * @param  Extrafields	 $extra            		$extrafields
 * @param  string        $key            		Key of attribute
 * @param  string|array  $value 			    Preselected value to show (for date type it must be in timestamp format, for amount or price it must be a php numeric value); for dates in filter mode, a range array('start'=><timestamp>, 'end'=><timestamp>) should be provided
 * @param  string        $moreparam      		To add more parameters on html input tag
 * @param  string        $keysuffix      		Prefix string to add after name and id of field (can be used to avoid duplicate names)
 * @param  string        $keyprefix      		Suffix string to add before name and id of field (can be used to avoid duplicate names)
 * @param  string        $morecss        		More css (to defined size of field. Old behaviour: may also be a numeric)
 * @param  int           $objectid       		Current object id
 * @param  string        $extrafieldsobjectkey	The key to use to store retrieved data (commonly $object->table_element)
 * @param  int	         $mode                  1=Used for search filters
 * @param  object		 $Step					Step
 * @return string
 */
function aff_show_input_field($extra, $key, $value, $moreparam = '', $keysuffix = '', $keyprefix = '', $morecss = '', $objectid = 0, $extrafieldsobjectkey = '', $mode = 0, $Step)
{
	global $conf, $langs, $form;

	if (!is_object($form)) {
		require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
		$form = new Form($extra->db);
	}

	$out = '';

	if (!preg_match('/options_$/', $keyprefix)) {	// Because we work on extrafields, we add 'options_' to prefix if not already added
		$keyprefix = $keyprefix.'options_';
	}

	if (empty($extrafieldsobjectkey)) {
		dol_syslog(get_class($extra).'::showInputField extrafieldsobjectkey required', LOG_ERR);
		return 'BadValueForParamExtraFieldsObjectKey';
	}

	$label = $extra->attributes[$extrafieldsobjectkey]['label'][$key];
	$type = $extra->attributes[$extrafieldsobjectkey]['type'][$key];
	$size = $extra->attributes[$extrafieldsobjectkey]['size'][$key];
	$default = $extra->attributes[$extrafieldsobjectkey]['default'][$key];
	$computed = $extra->attributes[$extrafieldsobjectkey]['computed'][$key];
	$unique = $extra->attributes[$extrafieldsobjectkey]['unique'][$key];
	$required = $extra->attributes[$extrafieldsobjectkey]['required'][$key];
	$param = $extra->attributes[$extrafieldsobjectkey]['param'][$key];
	$perms = (int) dol_eval($extra->attributes[$extrafieldsobjectkey]['perms'][$key], 1, 1, '2');
	$langfile = $extra->attributes[$extrafieldsobjectkey]['langfile'][$key];
	$list = (string) dol_eval($extra->attributes[$extrafieldsobjectkey]['list'][$key], 1, 1, '2');
	$totalizable = $extra->attributes[$extrafieldsobjectkey]['totalizable'][$key];
	$help = $extra->attributes[$extrafieldsobjectkey]['help'][$key];
	$hidden = (empty($list) ? 1 : 0); // If empty, we are sure it is hidden, otherwise we show. If it depends on mode (view/create/edit form or list, this must be filtered by caller)

	//var_dump('key='.$key.' '.$value.' '.$moreparam.' '.$keysuffix.' '.$keyprefix.' '.$objectid.' '.$extrafieldsobjectkey.' '.$mode);
	//var_dump('label='.$label.' type='.$type.' param='.var_export($param, 1));

	if ($computed) {
		if (!preg_match('/^search_/', $keyprefix)) {
			return '<span class="opacitymedium">'.$langs->trans("AutomaticallyCalculated").'</span>';
		} else {
			return '';
		}
	}

	//
	// 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	if (empty($morecss)) {
		// Add automatic css
		if ($type == 'date') {
			$morecss = 'minwidth100imp';
		} elseif ($type == 'datetime' || $type == 'datetimegmt' || $type == 'link') {
			$morecss = 'minwidth200imp';
		} elseif (in_array($type, array('int', 'integer', 'double', 'price'))) {
			$morecss = 'maxwidth75';
		} elseif ($type == 'password') {
			$morecss = 'maxwidth100';
		} elseif ($type == 'url') {
			$morecss = 'minwidth400';
		} elseif ($type == 'boolean') {
			$morecss = '';
		} elseif ($type == 'radio') {
			$morecss = 'width25';
		} else {
			if (empty($size) || round((float) $size) < 12) {
				$morecss = 'minwidth100';
			} elseif (round((float) $size) <= 48) {
				$morecss = 'minwidth200';
			} else {
				$morecss = 'minwidth400';
			}
		}
		// If css forced in attribute, we use this one
		if (!empty($extra->attributes[$extrafieldsobjectkey]['css'][$key])) {
			$morecss = $extra->attributes[$extrafieldsobjectkey]['css'][$key];
		}
	}

	if ($type == 'sellist') {
		$out = '';  // @phan-suppress-current-line PhanPluginRedundantAssignment
		if (!empty($conf->use_javascript_ajax) && !getDolGlobalString('MAIN_EXTRAFIELDS_DISABLE_SELECT2')) {
			include_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
			$out .= ajax_combobox($keyprefix.$key.$keysuffix, array(), 0);
		}

		$out .= '<select class="flat '.$morecss.' maxwidthonsmartphone" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'" '.($moreparam ? $moreparam : '').'>';
		if (is_array($param['options'])) {
			$param_list = array_keys($param['options']);
			$InfoFieldList = explode(":", $param_list[0]);
			$parentName = '';
			$parentField = '';
			// 0 : tableName
			// 1 : label field name
			// 2 : key fields name (if differ of rowid)
			// 3 : key field parent (for dependent lists)
			// 4 : where clause filter on column or table extrafield, syntax field='value' or extra.field=value
			// 5 : id category type
			// 6 : ids categories list separated by comma for category root
			// 7 : sort by (to be close to common object)
			$keyList = (empty($InfoFieldList[2]) ? 'rowid' : $InfoFieldList[2].' as rowid');


			if (count($InfoFieldList) > 4 && !empty($InfoFieldList[4])) {
				if (strpos($InfoFieldList[4], 'extra.') !== false) {
					$keyList = 'main.'.$InfoFieldList[2].' as rowid';
				} else {
					$keyList = $InfoFieldList[2].' as rowid';
				}
			}
			if (count($InfoFieldList) > 3 && !empty($InfoFieldList[3])) {
				list($parentName, $parentField) = explode('|', $InfoFieldList[3]);
				$keyList .= ', '.$parentField;
			}

			$filter_categorie = false;
			if (count($InfoFieldList) > 5) {
				if ($InfoFieldList[0] == 'categorie') {
					$filter_categorie = true;
				}
			}

			if ($filter_categorie === false) {
				$fields_label = explode('|', $InfoFieldList[1]);
				if (is_array($fields_label)) {
					$keyList .= ', ';
					$keyList .= implode(', ', $fields_label);
				}

				// serem
				// $sqlwhere = '';
				// $sql = "SELECT ".$keyList;
				// $sql .= ' FROM '.$extra->db->prefix().$InfoFieldList[0];
				// if (!empty($InfoFieldList[4])) {
				// 	// can use current entity filter
				// 	if (strpos($InfoFieldList[4], '$ENTITY$') !== false) {
				// 		$InfoFieldList[4] = str_replace('$ENTITY$', (string) $conf->entity, $InfoFieldList[4]);
				// 	}
				// 	// can use SELECT request
				// 	if (strpos($InfoFieldList[4], '$SEL$') !== false) {
				// 		$InfoFieldList[4] = str_replace('$SEL$', 'SELECT', $InfoFieldList[4]);
				// 	}

				// 	// current object id can be use into filter
				// 	if (strpos($InfoFieldList[4], '$ID$') !== false && !empty($objectid)) {
				// 		$InfoFieldList[4] = str_replace('$ID$', (string) $objectid, $InfoFieldList[4]);
				// 	} else {
				// 		$InfoFieldList[4] = str_replace('$ID$', '0', $InfoFieldList[4]);
				// 	}
				// 	//We have to join on extrafield table
				// 	if (strpos($InfoFieldList[4], 'extra.') !== false) {
				// 		$sql .= ' as main, '.$extra->db->prefix().$InfoFieldList[0].'_extrafields as extra';
				// 		$sqlwhere .= " WHERE extra.fk_object=main.".$InfoFieldList[2]." AND ".$InfoFieldList[4];
				// 	} else {
				// 		$sqlwhere .= " WHERE ".$InfoFieldList[4];
				// 	}
				// } else {
				// 	$sqlwhere .= ' WHERE 1=1';
				// }
				// // Some tables may have field, some other not. For the moment we disable it.
				// if (in_array($InfoFieldList[0], array('tablewithentity'))) {
				// 	$sqlwhere .= ' AND entity = '.((int) $conf->entity);
				// }
				// $sql .= $sqlwhere;
				// //print $sql;

				// $sql .= ' ORDER BY '.implode(', ', $fields_label);

				$sql = "SELECT rowid as rowid, label FROM llx_c_affaire_status WHERE fk_step=$Step AND active=1 ORDER BY fk_type";
				// END SEREM

				dol_syslog(get_class($extra).'::showInputField type=sellist', LOG_DEBUG);
				$resql = $extra->db->query($sql);
				if ($resql) {
					$out .= '<option value="0">&nbsp;</option>';
					$num = $extra->db->num_rows($resql);
					$i = 0;
					while ($i < $num) {
						$labeltoshow = '';
						$obj = $extra->db->fetch_object($resql);

						// Several field into label (eq table:code|label:rowid)
						$notrans = false;
						$fields_label = explode('|', $InfoFieldList[1]);
						if (is_array($fields_label) && count($fields_label) > 1) {
							$notrans = true;
							foreach ($fields_label as $field_toshow) {
								$labeltoshow .= $obj->$field_toshow.' ';
							}
						} else {
							$labeltoshow = $obj->{$InfoFieldList[1]};
						}

						if ($value == $obj->rowid) {
							if (!$notrans) {
								foreach ($fields_label as $field_toshow) {
									$translabel = $langs->trans($obj->$field_toshow);
									$labeltoshow = $translabel.' ';
								}
							}
							$out .= '<option value="'.$obj->rowid.'" selected>'.$labeltoshow.'</option>';
						} else {
							if (!$notrans) {
								$translabel = $langs->trans($obj->{$InfoFieldList[1]});
								$labeltoshow = $translabel;
							}
							if (empty($labeltoshow)) {
								$labeltoshow = '(not defined)';
							}

							if (!empty($InfoFieldList[3]) && $parentField) {
								$parent = $parentName.':'.$obj->{$parentField};
							}

							$out .= '<option value="'.$obj->rowid.'"';
							$out .= ($value == $obj->rowid ? ' selected' : '');
							$out .= (!empty($parent) ? ' parent="'.$parent.'"' : '');
							$out .= '>'.$labeltoshow.'</option>';
						}

						$i++;
					}
					$extra->db->free($resql);
				} else {
					print 'Error in request '.$sql.' '.$extra->db->lasterror().'. Check setup of extra parameters.<br>';
				}
			} else {
				require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
				$data = $form->select_all_categories(Categorie::$MAP_ID_TO_CODE[$InfoFieldList[5]], '', 'parent', 64, $InfoFieldList[6], 1, 1);
				$out .= '<option value="0">&nbsp;</option>';
				if (is_array($data)) {
					foreach ($data as $data_key => $data_value) {
						$out .= '<option value="'.$data_key.'"';
						$out .= ($value == $data_key ? ' selected' : '');
						$out .= '>'.$data_value.'</option>';
					}
				}
			}
		}
		$out .= '</select>';
	}
	if (!empty($hidden)) {
		$out = '<input type="hidden" value="'.$value.'" name="'.$keyprefix.$key.$keysuffix.'" id="'.$keyprefix.$key.$keysuffix.'"/>';
	}
	/* Add comments
		if ($type == 'date') $out.=' (YYYY-MM-DD)';
		elseif ($type == 'datetime') $out.=' (YYYY-MM-DD HH:MM:SS)';
		*/
	/*if (!empty($help) && $keyprefix != 'search_options_') {
		$out .= $form->textwithpicto('', $help, 1, 'help', '', 0, 3);
	}*/
	return $out;
}

/**
 * 
 */
function fetchAllStatusOfStep($step, $affaire=null){
	global $db, $langs;
	$langs->load("errors");

	if (is_string($step)) {
		$thisStep = $affaire->getStep(0, '', $step);
	} else if (is_object($step)) {
		$thisStep = $step;
	} else {
		setEventMessages($langs->trans("ErrorWrongValueForParameter", $langs->transnoentities("Step")), null, 'errors');
		return -1;
	}

	if (!is_object($thisStep) || !isset($thisStep->rowid)) {
		setEventMessages($langs->trans("L'étape est incorrect ou n'a pas été trouver dans ce workflow"), null, 'errors');
		return -1;
	}
	
	// Fetch all status of this step
	$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, status_for, active FROM llx_c_affaire_status WHERE fk_step = '$thisStep->rowid' ORDER BY fk_type";
	$resql = $db->query($sql);
	if ($resql) {
		$thisStatusArray = array();
		if ($resql->num_rows > 0) {
			while ($res = $db->fetch_object($resql)) {
				$thisStatusArray[$res->rowid] = $res;
			}
		} else {
			setEventMessages($langs->trans("Il n'y a pas de status pour cette étape"), null, 'mesg');
		}
		return $thisStatusArray;
	} else {
		dol_print_error($db);
		return -1;
	}
}