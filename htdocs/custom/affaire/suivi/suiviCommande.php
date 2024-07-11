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


// PRINT TABLE
print '<table class="noborder" width="100%">';
	// Filter
	// print filter
	$array_of_affaire_filtered = $array_of_affaire;


	// Title
	print '<tr class="liste_titre">';
		print '<td>'.$langs->trans("AFFAIRE").'</td>';
		print '<td align="left">'.$langs->trans("PROPAL").'</td>';
		print '<td align="nowrap">'.$langs->trans("CLIENT").'</td>';
		print '<td>'.$langs->trans("REF CLIENT").'</td>';
		print '<td align="center">'.$langs->trans("CRÉATION").'</td>';
		print '<td align="center">'.$langs->trans("RELANCE").'</td>';
		print '<td align="center">'.$langs->trans("PRIX HT").'</td>';
		print '<td align="center">'.$langs->trans("STATUS").'</td>';
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

			$workflow = fetchWorkflow('', $affaire->fk_workflow_type);

			print '<tr style="background: rgb(117 190 218 / 15%);">';
				print '<td>'.$affaire->getNomUrl().'</td>';
				print '<td align="left"></td>';
				print '<td class"nowrap"></td>';
				print '<td align="center"></td>';
				print '<td align="center"></td>';
				print '<td align="center"></td>';
				print '<td align="center"></td>';
				print '<td align="center"></td>';
				print '<td align="center">'.$affaire->note_private.'</td>';
			print "</tr>";


			// Propal
			$array_of_propal = checkPropalExist($affaire);
			if (is_string($array_of_propal) && $array_of_propal > 0) {
				$array_of_propal = array($array_of_propal);
			}
			foreach ($array_of_propal as $propalID) {
				$propal = new Propal($db);
				$extrafields = new ExtraFields($db);
				$extrafields->fetch_name_optionals_label($propal->table_element);
				$res = $propal->fetch($propalID);
				$ret = $propal->fetch_thirdparty();


				$picto = $propal->picto;  // @phan-suppress-current-line PhanUndeclaredProperty
				$prefix = 'object_';
				$nophoto = img_picto('No photo', $prefix.$picto);
				$steplabel = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid);
				$propal_page = '/'.strtolower($workflow->label).'/'.strtolower($workflow->label).'_'.$steplabel.'_stateOfPlay.php?affaire='.$affaire->id.'&id='.$propal->id.">".$nophoto.' '.$propal->ref;
				$path = dol_buildpath($propal_page, 1);
				
				print '<tr class="'.($pair ? 'pair' : 'impair').'">';
					print '<td></td>';
					print '<td align="left"><a href='.$path."</a></td>";
					// print '<td align="left">'.$propal->getNomUrl().'</td>';
					print '<td class="nowrap">'.$propal->thirdparty->getNomUrl().'</td>';
					print '<td align="left">'.(isset($propal->ref_customer) ? $propal->ref_customer : $propal->ref_client).'</td>';
					print '<td align="center">'.dol_print_date($propal->date_creation, 'day').'</td>';
					print '<td align="center">'.dol_print_date($propal->array_options["options_daterelance"], 'day').'</td>';
					print '<td align="center">'.price2num($propal->total_ht).'</td>';
					print '<td align="center">'.printBagde($propal->array_options["options_aff_status"], 'small').'</td>';
					print '<td align="center">'.$propal->note_private.'</td>';
				print "</tr>";
			}
		}

		print '<tr><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>';
	}

	print "</table>";


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
		$stepaffaire = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid);
		$steppropal = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$workflow->rowid);
		$stepAffaire = fetchStep(0, $stepaffaire, $workflow);
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