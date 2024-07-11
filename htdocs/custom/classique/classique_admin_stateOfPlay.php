<?php
/**
 * Copyright (C) 2024	   Lucas Noirie			 <lunoshist@users.noreply.github.com>
 * 
 *	\file       htdocs/custom/classique/classique_admin_stateOfPlay.php
 * 	\ingroup 	affaire
 *	\brief      Admin card
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
	$modName = 'Classique';
	$workflow = fetchWorkflow($modName);
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



	if ($affaire) {
		// Fetch step of affaire
		$affaireStep = $affaire->getStep();
		$defaultStepStatus = $affaireStep->fk_default_status;
		$INFO["Affaire"] .= "<br> > aff_Step: $affaireStep->label_short [$affaireStep->rowid]  default: [$defaultStepStatus]";

		// Fetch status of affaire
		$affaireStatus = $affaire->getStatus();
		$INFO["Affaire"] .= "<br> > aff_Status: $affaireStatus->label [$affaireStatus->rowid]";

		// Fetch affaire status of each step
		$affaireStatusbyStep = $affaire->getAllStatus();



		// Fetch this step
		$thisStepName = 'Admin'; // <-- this has to be modofied when dictionnary change
		$thisStep = fetchStep(0, $thisStepName, $workflow);
		$defaultStepStatus = $thisStep->fk_default_status;
		$INFO["Page"] .= "<br> > Step: $thisStep->label_short [$thisStep->rowid]  default: [$defaultStepStatus]";

		// Fetch status of affaire for this step
		$thisStatus = $affaireStatusbyStep[strtolower($thisStep->label_short)];
		$INFO["Page"] .= "<br> > Status : ".($thisStatus->label ?? '')." [".($thisStatus->rowid ?? '')."]";

		// Fetch all status of this step 
		$thisStatusArray = fetchAllStatusOfStep($thisStep, $affaire->fk_workflow_type);
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
        if (!empty(GETPOSTINT('newStatus'))) {
			$newStatus = GETPOSTINT('newStatus');
		} else if (!empty(GETPOST("options_aff_status"))) {
			$newStatus = GETPOST("options_aff_status");
		} else if (!empty(GETPOST('newStatus')) && GETPOST('newStatus') == 'defaultStatus'){
			$newStatus = $defaultStepStatus;
		} else {
			$newStatus = '';
		}

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

        $path = $_SERVER["PHP_SELF"].'?affaire='.$affaire->id;
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
	print dol_workflow_tabs($affaire, $thisStep, $workflow);
} else {
	print affaireBanner($affaire, $thisStep, $workflow);
}

injectOpenUrlsScript();

// VIEW
/*
 * View
 */

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

// End of page
llxFooter();
$db->close();