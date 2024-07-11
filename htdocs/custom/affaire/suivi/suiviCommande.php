<?php
/*
 * Copyright (C) 2014-2024      Philippe BERAUD SEREM
 * Copyright (C) 2024           Lucas Noirie
 * 
 */

/**
 *  \file       /affaire/suivi/suiviPropal.php
 *  \ingroup    serem
 *  \brief      Affiche les affaires à l'étape de propal
 */

include "../../../main.inc.php";


dol_include_once('/affaire/class/affaire.class.php');
dol_include_once('/affaire/lib/affaire_affaire.lib.php');
dol_include_once('/affaire/lib/affaire.lib.php');
require_once DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';

global $db,$langs;


/*
 * Actions
 */

if($action==$langs->trans("Enregistrer"))
{
	// Do something
}
		
		
/*
 * View
 */
$form=new Form($db);

$title = $langs->trans('SUIVI DES COMMANDES');

$action=GETPOST('action');

set_time_limit(0);

llxHeader('',$title);

print load_fiche_titre($title, '', 'project');

// SQL TO FETCH AFFAIRE
$array_of_affaire = fetchAffaire();


foreach ($array_of_affaire as $stepID => $arrayOFAFFAIRE) {
	$step = fetchStep($stepID);
	print load_fiche_titre($step->label, '', '');

	switch (strtolower($step->label_short)) {
		case 'cmde':
			// PRINT TABLE
			print '<table class="noborder" width="100%">';
			// Filter
			// print filter
			$array_of_affaire_filtered = $arrayOFAFFAIRE;


			// Title
			print '<tr class="liste_titre">';
				print '<td align="nowrap">'.$langs->trans("CLIENT").'</td>';
				print '<td>'.$langs->trans("AFFAIRE").'</td>';
				print '<td align="left">'.$langs->trans("LIVRAISION").'</td>';
				print '<td align="center"></td>';
				print '<td align="center">'.$langs->trans("CMDE").'</td>';
				print '<td align="center">'.$langs->trans("PROD").'</td>';
				print '<td align="center">'.$langs->trans("EXPE").'</td>';
				print '<td align="center">'.$langs->trans("FACT").'</td>';
				print '<td align="center">'.$langs->trans("ADMIN").'</td>';
				print '<td align="center">'.$langs->trans("NOTE").'</td>';
			print "</tr>";

			// Lines
			foreach ($array_of_affaire_filtered as $statusID => $array_affaires){
				$status = fetchStatus($statusID);
				print '<tr>';
					print '<td colspan=1 align="center">';
						print '<div class="titre"><h3><i>---  '.$status->label.'  ---</div>';
					print '</td>';
				print '</tr>';
			
				
				foreach ($array_affaires as $affaireID) {
					// Affaire
					$affaire = new Affaire($db);
					$res = $affaire->fetch($affaireID);
					$ret = $affaire->fetch_thirdparty();
					$all_status = $affaire->getAllStatus();

					$workflow = fetchWorkflow('', $affaire->fk_workflow_type);

					$cmdeID = checkCommandeExist($affaire);
					if (is_numeric($cmdeID) && $cmdeID > 0) {
						$cmde = new Commande($db);
						$extrafields = new ExtraFields($db);
						$extrafields->fetch_name_optionals_label($cmde->table_element);
						$res = $cmde->fetch($cmdeID);
						$ret = $cmde->fetch_thirdparty();
					}
						print '<tr>';
							print '<td class="nowrap">'.(isset($cmde) ? $cmde->thirdparty->getNomUrl() : $affaire->thirdparty->getNomUrl() ).'</td>';
							print '<td>'.$affaire->getNomUrl(1).'<span class="opacitymedium"> - '.(isset($cmde) ? (isset($cmde->ref_customer) ? $cmde->ref_customer : $cmde->ref_client) : 'CMDE INEXISTANTE').'</span></td>';
							print '<td align="left">'.(isset($cmde) ? (isset($cmde->delivery_date) ? dol_print_date($cmde->delivery_date, 'day') : dol_print_date($cmde->date_livraison, 'day')) : 'CMDE INEXISTANTE').'</td>';
							print '<td align="center"></td>';
							print '<td align="center">'.printBagde($all_status["cmde"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["prod"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["expe"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["facture"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["admin"], 'small', true).'</td>';
							print '<td align="center">'.$affaire->note_private.'</td>';
						print "</tr>";
					
				}
			}

			print "</table>";

			print '<br><br><br><br>';
			break;
		
		default:
			// PRINT TABLE
			print '<table class="noborder" width="100%">';
			// Filter
			// print filter
			$array_of_affaire_filtered = $arrayOFAFFAIRE;


			// Title
			print '<tr class="liste_titre">';
				print '<td align="nowrap">'.$langs->trans("CLIENT").'</td>';
				print '<td>'.$langs->trans("AFFAIRE").'</td>';
				print '<td align="left">'.$langs->trans("LIVRAISION").'</td>';
				print '<td align="center"></td>';
				print '<td align="center">'.$langs->trans("CMDE").'</td>';
				print '<td align="center">'.$langs->trans("PROD").'</td>';
				print '<td align="center">'.$langs->trans("EXPE").'</td>';
				print '<td align="center">'.$langs->trans("FACT").'</td>';
				print '<td align="center">'.$langs->trans("ADMIN").'</td>';
				print '<td align="center">'.$langs->trans("NOTE").'</td>';
			print "</tr>";

			// Lines
			foreach ($array_of_affaire_filtered as $statusID => $array_affaires){
				$status = fetchStatus($statusID);
				print '<tr>';
					print '<td colspan=1 align="center">';
						print '<div class="titre"><h3><i>---  '.$status->label.'  ---</div>';
					print '</td>';
				print '</tr>';
			
				
				foreach ($array_affaires as $affaireID) {
					// Affaire
					$affaire = new Affaire($db);
					$res = $affaire->fetch($affaireID);
					$ret = $affaire->fetch_thirdparty();
					$all_status = $affaire->getAllStatus();

					$workflow = fetchWorkflow('', $affaire->fk_workflow_type);

					$cmdeID = checkCommandeExist($affaire);
					if (is_numeric($cmdeID) && $cmdeID > 0) {
						$cmde = new Commande($db);
						$extrafields = new ExtraFields($db);
						$extrafields->fetch_name_optionals_label($cmde->table_element);
						$res = $cmde->fetch($cmdeID);
						$ret = $cmde->fetch_thirdparty();
					}
						print '<tr>';
							print '<td class="nowrap">'.(isset($cmde) ? $cmde->thirdparty->getNomUrl() : $affaire->thirdparty->getNomUrl() ).'</td>';
							print '<td>'.$affaire->getNomUrl(1).'<span class="opacitymedium"> - '.(isset($cmde) ? (isset($cmde->ref_customer) ? $cmde->ref_customer : $cmde->ref_client) : 'CMDE INEXISTANTE').'</span></td>';
							print '<td align="left">'.(isset($cmde) ? (isset($cmde->delivery_date) ? dol_print_date($cmde->delivery_date, 'day') : dol_print_date($cmde->date_livraison, 'day')) : 'CMDE INEXISTANTE').'</td>';
							print '<td align="center"></td>';
							print '<td align="center">'.printBagde($all_status["cmde"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["prod"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["expe"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["facture"], 'small', true).'</td>';
							print '<td align="center">'.printBagde($all_status["admin"], 'small', true).'</td>';
							print '<td align="center">'.$affaire->note_private.'</td>';
						print "</tr>";
					
				}
			}

			print "</table>";

			print '<br><br><br><br>';
			break;
	}
}

llxFooter();

$db->close();

/**
 *    REQUETE SQL to fetch all affaire in step of propal
 *
 *    @return	array
 */
function fetchAffaire() {
	global $db;

	$array_of_affaire = array();

	$workflows = fetchWorkflow();
	foreach($workflows as $workflow) {
		$stepaffaire = empty(getDolGlobalString('STEP_AFFAIRE_FOR_WORKFLOW_'.$workflow->rowid)) ? 'affaire' : getDolGlobalString('STEP_AFFAIRE_FOR_WORKFLOW_'.$workflow->rowid);
		$stepAffaire = fetchStep(0, $stepaffaire, $workflow);
		$steppropal = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid);
		$stepPropal = fetchStep(0, $steppropal, $workflow);
		$sql = 'SELECT rowid, fk_step, fk_status FROM '.MAIN_DB_PREFIX.'affaire_affaire WHERE fk_workflow_type='.$workflow->rowid.' AND fk_step != '.$stepPropal->rowid.' AND fk_step != '.$stepAffaire->rowid;

		$resql = $db->query($sql);
		if ($resql) {
			while ($affaire = $db->fetch_object($resql)) {
				if (!isset($array_of_affaire[$affaire->fk_step])) {
					$array_of_affaire[$affaire->fk_step] = array();
				}
				if (!isset($array_of_affaire[$affaire->fk_step][$affaire->fk_status])) {
					$array_of_affaire[$affaire->fk_step][$affaire->fk_status] = array();
				}
				$array_of_affaire[$affaire->fk_step][$affaire->fk_status][] = $affaire->rowid; 
			}
		} else {
			dol_print_error($db);
		}
	}

    return $array_of_affaire;
}