<?php
/**
 * Copyright (C) 2024	   Lucas Noirie			 <lunoshist@users.noreply.github.com>
 * 
 *	\file       htdocs/custom/classique/classique_prod_stateOfPlay.php
 * \ingroup 	affaire
 *	\brief      Project card
 */

// Load Dolibarr environment
require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';

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
		$thisStepName = 'Prod'; // <-- this and the name of the file have to be modofied when dictionnary change
		$thisStep = fetchStep(0, $thisStepName, $workflow);
		$defaultStepStatus = $thisStep->fk_default_status;
		$INFO["Page"] .= "<br> > Step: $thisStep->label_short [$thisStep->rowid]  default: [$defaultStepStatus]";

		// Fetch status of affaire for this step
		$thisStatus = $affaireStatusbyStep[strtolower($thisStep->label_short)];
		$INFO["Page"] .= "<br> > Status : ".($thisStatus->label ?? '')." [".($thisStatus->rowid ?? '')."]";

		// Fetch all status of this step 
		$thisStatusArray = fetchAllStatusOfStep($thisStep, $affaire->fk_workflow_type);



		// Fetch object project
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


	// Project
	print "PROJET  :  ".$project->getNomUrl(1);
	print '<br>';
	
	// Taches et leur status
	$taskstatic = new Task($db);
	// $usert = null, $userp = null, $projectid = 0, $socid = 0, $mode = 0, $filteronproj = '', $filteronprojstatus = '-1', $morewherefilter = '', $filteronprojuser = 0, $filterontaskuser = 0, $extrafields = null, $includebilltime = 0, $search_array_options = array(), $loadextras = 0, $loadRoleMode = 1, $sortfield = '', $sortorder = ''
	$task_array = $taskstatic->getTasksArray(0, 0, $project->id, 0, 0, '', '', '', '', '', null, 0, array(), 1);
	foreach ($task_array as $task) {
		print '<br><br><div style="padding-left: 15px">';
		print $task->getNomUrl(1)." - ".$task->label." : <span style=COLOR:".colorOfStatut($task->array_options["options_statut_fab"],"fab").">".statut_fab_label($task->array_options["options_statut_fab"])."</span>";
		print '</div>';
	}
}

// End of page
llxFooter();
$db->close();


function colorOfStatut($statut,$type)
{	
	switch($statut)
	{
		case 0:
			return "red";
		case 2:
		case 3:
			return "orange";
		case 1:
		case 4:
			return "green";
			
	}
	
}

function statut_fab_label($option) //a:1:{s:7:"options";a:6:{i:0;s:17:"Att. mise en fab.";i:1;s:11:"En cours...";i:2;s:17:"Att. info. client";i:3;s:16:"Att. fournisseur";i:4;s:9:"Terminée";s:0:"";N;}}
{
	global $db;
	
	$sql = 'SELECT *';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'extrafields';
	$sql.= ' WHERE elementtype="projet_task" AND name="statut_fab"';
	$resql = $db->query($sql);
	if($resql)
	{
		$obj = $db->fetch_object($resql);
		$liste=explode('{',$obj->param);
		$liste=$liste[2];
		$liste=explode('i:',$liste);
		
		for($i=1;$i<sizeof($liste);$i++)$tab[$i-1]=explode('"',$liste[$i])[1];
		return $tab[$option];
	}
	else
	{
		print "error extrafield2Tab";
	}
}