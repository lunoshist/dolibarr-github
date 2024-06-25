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
 * \file    lib/affaire_affaire.lib.php
 * \ingroup affaire
 * \brief   Library files with common functions for Affaire
 */

/**
 * Prepare array of tabs for Affaire
 *
 * @param	Affaire	$object		Affaire
 * @return 	array					Array of tabs
 */
function affairePrepareHead($object)
{
	global $db, $langs, $conf;

	$langs->load("affaire@affaire");

	$showtabofpageworkflow = 1;
	$showtabofpagecard = 1;
	$showtabofpagecontact = 1;
	$showtabofpagenote = 1;
	$showtabofpagedocument = 1;
	$showtabofpageagenda = 1;

	$h = 0;
	$head = array();

	if ($showtabofpageworkflow) {
		$head[$h][0] = dol_buildpath("/affaire/affaire_workflow.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Workflow");
		$head[$h][2] = 'Workflow';
		$h++;
	}

	if ($showtabofpagecard) {
		$head[$h][0] = dol_buildpath("/affaire/affaire_card.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Affaire");
		$head[$h][2] = 'card';
		$h++;
	}
	if ($showtabofpagecontact) {
		$head[$h][0] = dol_buildpath("/affaire/affaire_contact.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Contacts");
		$head[$h][2] = 'contact';
		$h++;
	}

	if ($showtabofpagenote) {
		if (isset($object->fields['note_public']) || isset($object->fields['note_private'])) {
			$nbNote = 0;
			if (!empty($object->note_private)) {
				$nbNote++;
			}
			if (!empty($object->note_public)) {
				$nbNote++;
			}
			$head[$h][0] = dol_buildpath('/affaire/affaire_note.php', 1).'?id='.$object->id;
			$head[$h][1] = $langs->trans('Notes');
			if ($nbNote > 0) {
				$head[$h][1] .= (!getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER') ? '<span class="badge marginleftonlyshort">'.$nbNote.'</span>' : '');
			}
			$head[$h][2] = 'note';
			$h++;
		}
	}

	if ($showtabofpagedocument) {
		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
		require_once DOL_DOCUMENT_ROOT.'/core/class/link.class.php';
		$upload_dir = $conf->affaire->dir_output."/affaire/".dol_sanitizeFileName($object->ref);
		$nbFiles = count(dol_dir_list($upload_dir, 'files', 0, '', '(\.meta|_preview.*\.png)$'));
		$nbLinks = Link::count($db, $object->element, $object->id);
		$head[$h][0] = dol_buildpath("/affaire/affaire_document.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans('Documents');
		if (($nbFiles + $nbLinks) > 0) {
			$head[$h][1] .= '<span class="badge marginleftonlyshort">'.($nbFiles + $nbLinks).'</span>';
		}
		$head[$h][2] = 'document';
		$h++;
	}

	if ($showtabofpageagenda) {
		$head[$h][0] = dol_buildpath("/affaire/affaire_agenda.php", 1).'?id='.$object->id;
		$head[$h][1] = $langs->trans("Events");
		$head[$h][2] = 'agenda';
		$h++;
	}

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	//$this->tabs = array(
	//	'entity:+tabname:Title:@affaire:/affaire/mypage.php?id=__ID__'
	//); // to add new tab
	//$this->tabs = array(
	//	'entity:-tabname:Title:@affaire:/affaire/mypage.php?id=__ID__'
	//); // to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'affaire@affaire');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'affaire@affaire', 'remove');

	return $head;
}

/**
 * Show a form to select a project
 *
 * @param 	int 		$page 				Page
 * @param 	int 		$socid 				Id third party (-1=all, 0=only projects not linked to a third party, id=projects not linked or linked to third party id)
 * @param 	string 		$selected 			Id preselected project
 * @param 	string 		$htmlname 			Name of select field
 * @param 	int 		$discard_closed 	Discard closed projects (0=Keep,1=hide completely except $selected,2=Disable)
 * @param 	int 		$maxlength 			Max length
 * @param 	int 		$forcefocus 		Force focus on field (works with javascript only)
 * @param 	int 		$nooutput 			No print is done. String is returned.
 * @param 	string 		$textifnoproject 	Text to show if no project
 * @param 	string 		$morecss 			More CSS
 * @return	string                      	Return html content
 */
function form_affaire($page, $socid, $selected = '', $htmlname = 'projectid', $discard_closed = 0, $maxlength = 20, $forcefocus = 0, $nooutput = 0, $textifnoproject = '', $morecss = '')
{
	// phpcs:enable
	global $langs, $db;

	require_once dol_buildpath('/core/lib/project.lib.php');
	require_once dol_buildpath('/affaire/class/htlm.formaffaire.class.php');

	$out = '';

	$formproject = new FormAffaires($db);

	$langs->load("project");
	if ($htmlname != "none") {
		$out .= '<form method="post" action="' . $page . '">';
		$out .= '<input type="hidden" name="action" value="classin">';
		$out .= '<input type="hidden" name="token" value="' . newToken() . '">';
		$out .= $formproject->select_affaires($socid, $selected, $htmlname, $maxlength, 0, 1, $discard_closed, $forcefocus, 0, 0, '', 1, 0, $morecss);
		$out .= '<input type="submit" class="button smallpaddingimp" value="' . $langs->trans("Modify") . '">';
		$out .= '</form>';
	} else {
		$out .= '<span class="project_head_block">';
		if ($selected) {
			$projet = new Affaire($db);
			$projet->fetch($selected);
			$out .= $projet->getNomUrl(0, '', 1);
		} else {
			$out .= '<span class="opacitymedium">' . $textifnoproject . '</span>';
		}
		$out .= '</span>';
	}

	if (empty($nooutput)) {
		print $out;
		return '';
	}
	return $out;
}