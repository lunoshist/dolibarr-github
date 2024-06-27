<?php
/**
 *	\file       htdocs/projet/card.php
 *	\ingroup    projet
 *	\brief      Project card
 */

// Load Dolibarr environment
require '../../main.inc.php';

dol_include_once('/affaire/class/affaire.class.php');
dol_include_once('/affaire/lib/affaire_affaire.lib.php');
dol_include_once('/affaire/lib/affaire.lib.php');

$langsLoad = array('affaire', 'companies');
$langs->loadLangs($langsLoad);

$action = GETPOST('action', 'aZ09');
$cancel = GETPOST('cancel', 'alpha');
$confirm = GETPOST('confirm', 'aZ09');

// AFFAIRE
$INFO = array(
	"Workflow" => '<br>Workflow :  ',
	"Affaire" => '<br><br>Affaire :  ',
	"Page" => '<br><br><br>This page :  ',
);
global $urlsToOpen;
$urlsToOpen = $urlsToOpen ?? [];

if (isModEnabled('affaire')) {
	$langs->load('affaire');

	// Workflow
	$workflow_array = array();
	// $workflow = (object)array("rowid"=>2, "label"=>'classique');
	$sql = "SELECT rowid, label FROM llx_c_affaire_workflow_types WHERE label = 'Classique'";
	$resql = $db->query($sql);
	if ($resql) {
		$res = $db->fetch_object($resql);
		$workflow_array["rowid"] = $res->rowid;
		$workflow_array["label"] = $res->label;
		$workflow = (object)$workflow_array;
	} else {
		dol_print_error($db);
	}
	$INFO["Workflow"] .= "<br> > $workflow->label [$workflow->rowid]";

	// Get affaire
	$affaireID = GETPOSTINT('affaire') ?? GETPOSTINT('affaireID');
	
	// Load affaire
	if ($affaireID > 0) {
		$affaire = new Affaire($db);
		$res = $affaire->fetch($affaireID);
		if ($res > 0) {
			if ($affaire->fk_workflow_type != $workflow->rowid) {
				// TODO fetch good workflow
				$path = dol_buildpath('')."?aiffaire=$affaireID";
				header('Location: '.$path);
				exit();
			}
		} else {
			setEventMessages($affaire->error, $affaire->errors, 'errors');
			$action = '';
		}
	}
	$INFO["Affaire"] .= "<br> > $affaire->ref [$affaire->id]";
	$INFO["Banner"]["ref"] = $affaire->ref;

	if ($affaire) {
		// Fetch step of affaire
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_default_status, position, object, active FROM llx_c_affaire_steps WHERE rowid = $affaire->fk_step AND fk_workflow_type = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$affaireStep = $db->fetch_object($resql);
				$defaultStepStatus = $affaireStep->fk_default_status;
				// var_dump($affaireStep);
				// print(json_encode($affaireStep, JSON_PRETTY_PRINT));
				$INFO["Affaire"] .= "<br> > aff_Step: $affaireStep->label_short [$affaireStep->rowid]  default: [$defaultStepStatus]";
				$INFO["Banner"]["step"] = $affaireStep;
			} else {
				setEventMessages($langs->trans("NoSuchStepInThisWorkflow"), null, 'errors');
			}
		} else {
			dol_print_error($db);
		}

		// Fetch status of affaire
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, status_for, active FROM llx_c_affaire_status WHERE rowid = $affaire->fk_status AND (fk_step = $affaire->fk_step OR fk_step = 1 OR fk_step = 2) AND (fk_workflow_type = $affaire->fk_workflow_type OR fk_workflow_type = 1)";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$affaireStatus = $db->fetch_object($resql);
				// var_dump($affaireStatus);
				// print(json_encode($affaireStatus, JSON_PRETTY_PRINT));
				$INFO["Affaire"] .= "<br> > aff_Status: $affaireStatus->label [$affaireStatus->rowid]";
				$INFO["Banner"]["status"] = $affaireStatus;

			} else {
				setEventMessages($langs->trans("NoSuchStatusForThisStepInThisWorkflow"), null, 'errors');
				$INFO["Affaire"] .= "<br> > aff_Status: NoSuchStatusForThisStepInThisWorkflow";
			}
		} else {
			dol_print_error($db);
		}


		// Fetch affaire status of each step
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


		// Fetch this step
		$thisStepName = 'Prod'; // <-- this has to be modofied when dictionnary change

		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_default_status, position, object, active FROM llx_c_affaire_steps WHERE label_short = '$thisStepName' AND fk_workflow_type = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$thisStep = $db->fetch_object($resql);
				$defaultStepStatus = $thisStep->fk_default_status;
				// var_dump($thisStep);
				// print(json_encode($thisStep, JSON_PRETTY_PRINT));
				$INFO["Page"] .= "<br> > Step: $thisStep->label_short [$thisStep->rowid]  default: [$defaultStepStatus]";
			} else {
				setEventMessages($langs->trans("NoSuchStepInThisWorkflow"), null, 'errors');
			}
		} else {
			dol_print_error($db);
		}
		
		// Fetch all status of this step : prod
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, status_for, active FROM llx_c_affaire_status WHERE fk_step = '$thisStep->rowid' AND fk_workflow_type = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			$thisStatusArray = array();
			if ($resql->num_rows > 0) {
				while ($res = $db->fetch_object($resql)) {
					$thisStatusArray[$res->rowid] = $res;
				}
			} else {
				setEventMessages($langs->trans("BeleBele"), null, 'mesg');
			}
		} else {
			dol_print_error($db);
		}


		// Fetch status of affaire for this step
		$fk_status_thisstep = "fk_status_".strtolower($thisStep->label_short);
		$thisStatusRowid = isset($affaireStatusbyStep->{"$fk_status_thisstep"}) ? $affaireStatusbyStep->{"$fk_status_thisstep"} : "' '";
		
		$sql = "SELECT rowid, label, label_short, fk_workflow_type, fk_step, fk_type, status_for, active FROM llx_c_affaire_status WHERE rowid = $thisStatusRowid AND fk_step = '$thisStep->rowid' AND fk_workflow_type = $affaire->fk_workflow_type";
		$resql = $db->query($sql);
		if ($resql) {
			if ($resql->num_rows > 0) {
				$thisStatus = $db->fetch_object($resql);
				// var_dump($thisStatus);
				// print(json_encode($thisStatus, JSON_PRETTY_PRINT));
				$INFO["Page"] .= "<br> > Status : $thisStatus->label [$thisStatus->rowid]";
			} else {
				$thisStatus = null;
			}
		} else {
			dol_print_error($db);
        }

		$project_array = checkProjectExist($affaire);
		if (checkProjectExist($affaire)) {
			if (is_array($project_array)) {
				$action = 'several_project';
			} else if ($project_array > 1) {
				$project = new Project($db);
				$res = $project->fetch($project_array);
			} else if ($project_array < 0) {
				$action = 'problem';
			}
		} else {
			$action = 'no_project';
		}
	}
}

// ACTION
/*
 * Actions
 */

$socid = GETPOSTINT('socid');
$parameters = array('id' => $socid);
$reshook = $hookmanager->executeHooks('doActions', $parameters, $object, $action); // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) {
    setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
}

if (empty($reshook)) {

    if ($action == 'changeStatus') {
        $newStatus = (empty(GETPOSTINT('newStatus'))) ? GETPOST("options_aff_status") : GETPOSTINT('newStatus');
        if ($newStatus == 0) $newStatus = GETPOST('newStatus', 'aZ09');
        if ($newStatus == 'defaultStatus') $newStatus = $defaultStepStatus;

        $error = 0;

        // Change affaire status (llx_affaire_affaire_status & llx_affaire_affaire)
            if (empty($error)) {
                $result = change_status($affaire, $newStatus, $condition='', $step=$thisStep, $previousStatus=$thisStatus ?? '', $workflow);			
                if ($result) {
                    setEventMessages("COULDN'T CHANGE STATUS", null, 'errors');
                    if (is_string($result)) setEventMessages($result, null, 'errors');
                }
            } else {
                setEventMessages($error, null, 'errors');
            }


        $_SESSION['urlsToOpen'] = $urlsToOpen;

        $path = $_SERVER["PHP_SELF"].'?id='.$id;
        $path .= $affaire ? "&affaire=$affaire->id" : '';
        $path .= ($action == 'edit_extras') ? "&action=$action&attribute_name=$attribute_name" : '';
        header('Location: '.$path);
        exit;
    }
}

// VIEW
/*
*	View
*/

$title = $thisStep->label;
llxHeader("", $title);

if (getDolGlobalInt('DEBUG')) {
	print implode("\n", $INFO)."<br><br>";
	print dol_workflow_tabs($affaire, $thisStep, $affaireStatusbyStep, $workflow);
} else {
	print affaireBanner($affaire, $thisStep, $affaireStatusbyStep, $workflow);
}

injectOpenUrlsScript();

// VIEW
/*
 * View
 */

if ($action == 'no_project') {
	print "<br><br>LA PRODUCTION N'A PAS ÉTÉ LANCÉ POUR CETTE AFFAIRE<br><br> On lance la production depuis la commande !";

} else if ($action == 'several_project') {
	setEventMessages("Trop de projets sont associés à l'affaire", null, 'errors');
	
	print "<br><br>IL Y A PLUSIEURS PROJETS :<br><br>";
	print "Plusieurs projet exitent déjà, UNE AFFAIRE CORESPOND À UNE SEULE COMMANDE DONC UN SEUL PROJET, réunissez toutes les commandes en une ou créez une autre affaire<br><br>";
	
	print '<table class="border centpercent tableforfieldcreate">';
	print "<tr>
		<td>REF</td>
		<td>Status</td>
		<td>Date création</td>";
	print "</tr>";

	foreach ($affaire->linkedObjects["project"] as $proj) {
		$picto = $proj->picto;  // @phan-suppress-current-line PhanUndeclaredProperty
		$prefix = 'object_';
		$nophoto = img_picto('No photo', $prefix.$picto);

		print "<tr>
		<td><a href=".$_SERVER["PHP_SELF"].'?affaire='.$affaire->id.'&id='.$proj->id.">".$nophoto.' '.$proj->ref."</a></td>
		<td>".printBagde($proj->array_options["options_aff_status"], 'mini')."</td>
		<td>".dol_print_date($proj->date_creation, 'day')."</td>";
		print "</tr>";
	}

	print '</table>';
} else if ($action == 'problem') {
	print "<br><br>UN PROBLÈME EST SURVENU !";
} else {
	print '<br><br>';
	print printBagde($thisStatus,'big');

	// ACTION BUTTON
	/*
	* Actions Buttons
	*/

	print '<div class="tabsAction">';
	$parameters = array();
	$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $affaire, $action); // Note that $action and $object may have been
	// modified by hook
	if (empty($reshook)) {

		// Change status
		if (isModEnabled('affaire') && $affaire) {
			$arrayofstatusforbutaction = array();

			// Fetch all status for this step
			foreach ($thisStatusArray as $key => $rstatus) {
				$labeltoshow = $rstatus->label;
				if ($rstatus->status_for != 'both') $labeltoshow .= " [".$rstatus->status_for." only]";
				if (getDolGlobalInt('ASK_FOR_CONFIRMATION')) {
					$arrayofstatusforbutaction[$rstatus->rowid] = array("lang"=> 'affaire', "enabled"=> isModEnabled("affaire"), "perm"=> 1, "label"=> $rstatus->label, 'url'=> '/custom/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_'.strtolower($thisStep->label_short).'_stateOfPlay.php?affaire='.$affaire->id.'&action=confirm_changeStatus&newStatus='.$rstatus->rowid.'&status_for='.$rstatus->status_for.'&token='.newToken());
				} else {
					$arrayofstatusforbutaction[$rstatus->rowid] = array("lang"=> 'affaire', "enabled"=> isModEnabled("affaire"), "perm"=> 1, "label"=> $rstatus->label, 'url'=> '/custom/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_'.strtolower($thisStep->label_short).'_stateOfPlay.php?affaire='.$affaire->id.'&action=changeStatus&newStatus='.$rstatus->rowid.'&status_for='.$rstatus->status_for.'&token='.newToken());
				}
			}

			$params = array('backtopage' => $_SERVER['PHP_SELF'].'?socid='.$socid.'&token='.newToken().'&affaire='.$affaire->id);

			print dolGetButtonAction('', $langs->trans("ChangeStatus"), 'default', $arrayofstatusforbutaction, 'changeStatusButton', 1, $params);
		}
	}

	print "</div>";
}

// End of page
llxFooter();
$db->close();