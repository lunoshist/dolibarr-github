<?php
/*
 * Copyright (C) 2014-2024      Philippe BERAUD SEREM
 * Copyright (C) 2024           Lucas Noirie
 * 
 */

/**
 *  \file       /serem/page/projet/planDeChargeFab.php
 *  \ingroup    serem
 *  \brief      Affiche les projet et taches en cours pour les techniciens et les ingénieurs
 */

include "../../../main.inc.php";
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.product.class.php';




/*
 * Actions
 */

$form=new Form($db);

$title = $langs->trans('PLAN DE CHARGE');

$action=GETPOST('action');

set_time_limit(0);

llxHeader('',$title);

print load_fiche_titre($title, '', 'project');

/*
 * Actions
 */
if($action==$langs->trans("Enregistrer"))
{
	majStatuts();
}
		
		
/*
 * View
 */


return_active_order();

llxFooter();

$db->close();


/* 
 * SECTION SUB FUNCTION
 */

function majStatuts()
{
	global $db;	
	
	$idx=GETPOST('nb_poste');
	
	for($i=0;$i<$idx;$i++)
	{
		$sql = 'SELECT *';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task_extrafields';
		$sql.= ' WHERE fk_object = '.GETPOST('statut_idx'.$i);
		$resql = $db->query($sql);
		$obj = $db->fetch_object($resql);
		if($obj->rowid!=null)
		{
			$sql='UPDATE '.MAIN_DB_PREFIX.'projet_task_extrafields';
			$sql.= ' SET statut_fab = '.GETPOST('statut_fab'.$i);
			$sql.= ' WHERE fk_object = '.GETPOST('statut_idx'.$i);
			$result=$db->query($sql);
		}
		else
		{
			$sql='INSERT INTO '.MAIN_DB_PREFIX.'projet_task_extrafields (fk_object,statut_fab)';
			$sql.= ' VALUES ('.GETPOST('statut_idx'.$i).','.GETPOST('statut_fab'.$i).')';
			$result=$db->query($sql);
		}
		
	}
	
}
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
function isSelectedOption($option,$db)
{
	if($option==$db)return "selected";
	else return "";
}

function extrafield2Tab($extrafield) //a:1:{s:7:"options";a:6:{i:0;s:17:"Att. mise en fab.";i:1;s:11:"En cours...";i:2;s:17:"Att. info. client";i:3;s:16:"Att. fournisseur";i:4;s:9:"Terminée";s:0:"";N;}}
{
	global $db;
	
	$sql = 'SELECT *';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'extrafields';
	$sql.= ' WHERE elementtype="projet_task" AND name="'.$extrafield.'"';
	$resql = $db->query($sql);
	if($resql)
	{
		$obj = $db->fetch_object($resql);
		$liste=explode('{',$obj->param);
		$liste=$liste[2];
		$liste=explode('i:',$liste);
		
		for($i=1;$i<sizeof($liste);$i++)$tab[$i-1]=explode('"',$liste[$i])[1];
		return $tab;
	}
	else
	{
		print "error extrafield2Tab";
	}
}

/**
 *    Return REQUETE SQL
 *
 *    @param    string	$htmlname			HTML field name
 *    @return	string
 */
function sqllistproj($specificcondition,$CmdOrPropal="commande")
{
    $sql = 'SELECT prod.ref AS r_prod,
            prod.label AS label_prod,
			cust.nom,
			user.firstname,
			tache.rowid AS id_tache,
			tache.ref AS r_tache,
			projet.rowid AS id_projet,
			projet.ref AS r_projet,
			source.rowid AS id_source,
			source.ref AS ref_source,
			source.date_livraison,
			poste.description,
			poste.qty,
			statut_fab,
			statut_adm,
			poste.rowid AS id_poste';
    
    $sql.= " FROM ".
        MAIN_DB_PREFIX.$CmdOrPropal." AS source LEFT JOIN ".MAIN_DB_PREFIX."projet AS projet ON source.fk_projet=projet.rowid,".
        MAIN_DB_PREFIX."societe AS cust,".
        MAIN_DB_PREFIX."user AS user,".
        MAIN_DB_PREFIX."element_contact AS chef,".
        //MAIN_DB_PREFIX."commandedet_extrafields AS poste_statut RIGHT JOIN ".MAIN_DB_PREFIX."commandedet AS poste ON fk_object=poste.rowid LEFT JOIN ".MAIN_DB_PREFIX."projet_task AS tache ON tache.note_private=poste.rowid LEFT JOIN ".MAIN_DB_PREFIX."product AS prod ON poste.fk_product=prod.rowid";
    MAIN_DB_PREFIX."projet_task_extrafields AS poste_statut ".
    "RIGHT JOIN ".MAIN_DB_PREFIX."projet_task AS tache ON fk_object=tache.rowid ".
    "LEFT JOIN ".MAIN_DB_PREFIX.$CmdOrPropal."det AS poste ON poste.rowid=tache.note_private ".
    "LEFT JOIN ".MAIN_DB_PREFIX."product AS prod ON poste.fk_product=prod.rowid";
    
    $sql.= " WHERE ".
        $specificcondition." AND ".
        "poste.product_type=0 AND ".
        "projet.rowid=source.fk_projet AND ".
        "user.rowid=chef.fk_socpeople AND ".
        "chef.fk_c_type_contact=180 AND ".
        "tache.rowid=chef.element_id AND ".
        "projet.fk_statut<=1 AND ".
        "projet.fk_statut>=0 AND ".
        //"source.fk_statut<3 AND ".
        //"source.fk_statut>-1 AND ".
        "source.fk_soc=cust.rowid AND ".
        "poste.fk_".$CmdOrPropal."=source.rowid ".
        "ORDER BY source.date_livraison, ".
        "projet.ref";
    "ORDER BY source.date_livraison, projet.ref";
        
    return $sql;
}
	
/**
 *    Return list of active order
 *
 *    @param    string	$htmlname			HTML field name
 *    @return	void
 */
function return_active_order($htmlname="ListActiveOrder")
{
	global $db,$langs;

	$statut_fab_option=extrafield2Tab("statut_fab");
	$k=0;
		
	
	print '<table class="noborder" width="100%">';
		print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("CLIENT").'</td>';
			print '<td align="center">'.$langs->trans("QTE").'</td>';
			print '<td>'.$langs->trans("PRODUIT").'</td>';
			print '<td align="center">'.$langs->trans("CMD-PROPAL").'</td>';
			print '<td align="center">'.$langs->trans("RESPONSABLE").'</td>';
			print '<td align="center">'.$langs->trans("LIVRAISON").'</td>';
			print '<td align="center">'.$langs->trans("STATUT FAB.").'</td>';
		print "</tr>";
	

		print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
		print '<input type="hidden" id="token" name="token" value="'.newToken().'" />';

		// Parametres pour changer le code d'execution ci dessous dans la boucle
		$TitleAff=array("AFF PROPAL","AFF CMD","REPA PROPAL","REPA CMD","ETUDES / AVANCES","MAINT PROPAL","MAINT CMD");
		$CondAff=array("cust.nom!=\"SEREM\"","cust.nom!=\"SEREM\"","(prod.ref=\"REPA_MAT\" || prod.ref=\"REPA_MO\")","(prod.ref=\"REPA_MAT\" || prod.ref=\"REPA_MO\")","cust.nom=\"SEREM\"","cust.nom!=\"SEREM\"","cust.nom!=\"SEREM\"");
		$CmdeorproalAff=array("propal","commande","propal","commande","commande","propal","commande");
		$LinkAff=array("comm/propal","commande","comm/propal","commande","commande","comm/propal","commande");
		$DescriptionAff=array("r_prod","r_prod","description","description","description","r_prod","r_prod");
		
		for($numAff=0;$numAff<7;$numAff++){
		    print '<tr>';
				print '<td colspan=1 align="center">';
				    print '<div class="titre"><h3><i>--------- '.$TitleAff[$numAff].' -------</div>';
				print '</td>';
				print '<td colspan=6 align="left">';
					print '<div class="tabsAction">';
						print '<input type=submit name=action value='.$langs->trans("Enregistrer").'>';
					print '</div>';
				print '</td>';
			print '</tr>';
		
			$sql = sqllistproj($CondAff[$numAff],$CmdeorproalAff[$numAff]);
 			
        	$resql = $db->query($sql);
        	if ($resql)
        	{
        		$num = $db->num_rows($resql); print "<br>";
        		$class="pair";
        		$source_previous="";
        		$aujourdhui=Date("Y-m-d");
        		for($i=0;$i<$num;$i++)
        		{
        			$obj = $db->fetch_object($resql);
        			
        			$source=$obj->ref_source;
        
        			if($obj->statut_fab==NULL)$obj->status_fab=1;
        			
        			if($obj->r_prod==NULL)
        			{
						$descr=explode("\n",$obj->description);
        				$maint_text = strpos($descr[0], 'MAINT-');
        			} else {
						$maint_text = strpos($obj->r_prod, 'MAINT-');
					}
        			
        			// Condition d'affichage de la ligne
        			$PrintAff=FALSE;
					
					$obj->r_prod = $obj->r_prod ?? "";

        			if ($numAff==0 || $numAff==1) { // CMDE
            			if(($obj->r_prod!="REPA_MO")&&($obj->r_prod!="REPA_MAT")&&($obj->statut_fab<4)&&($maint_text===false))
            			    $PrintAff=TRUE;
        			} else if ($numAff==2 || $numAff==3) {  // REPA
        			    if( $obj->r_prod!="REPA_MO" && $obj->statut_fab<4)
        			        $PrintAff=TRUE;
        			} else if ($numAff==2 || $numAff==3 || $numAff==4) {  // ETUDE
        			    if($obj->statut_fab<4  && (substr($obj->r_prod,0,2)!="R_") && (substr($obj->r_prod,0,2)!="A_") )
        			        $PrintAff=TRUE;
        			} else if ($numAff==5 || $numAff==6) { // MAINT
            		    if(($maint_text!==false)&&($obj->statut_fab<4))
            		        $PrintAff=TRUE;
            		}
        		
        			if ($PrintAff)
        			{						
        				$client_aff="";
        				$projet_aff="";
        				$color="black";			
        				
        				if($source!=$source_previous)
        				{
        					if($class=="pair")$class="impair";
        					else $class="pair";
        					$client_aff=$obj->nom;
        					$source_num=explode("-",$obj->ref_source);
        					$source_num=$source_num[0];
        					//$projet_aff="<a href=\"/dolibarr/htdocs/projet/card.php?id=".$obj->id_projet."\">".$obj->r_projet."</a> ";	
        					// $projet_aff="<a href=\"/dolibarr/htdocs/".$LinkAff[$numAff]."/card.php?id=".$obj->id_source."\">".$source_num."</a>";
							$projet_aff="<a href=\"".dol_buildpath("/$LinkAff[$numAff]/card.php?id=$obj->id_source", 1)."\">".$source_num."</a>";
							// ex : '/dolibarr/htdocs/comm/propal/card.php?id=4549'
        				}
        			
        				if(($obj->r_prod==NULL)||($obj->r_prod=="PROJ_MAT")||($obj->r_prod=="PROJ_MO"))
        				{
        					$descr=explode("\n",$obj->description);
        					$obj->r_prod=$descr[0];//"DIVERS...";
        			
        				}
        				
        				$description = explode("</a>", $obj->description);
        				if(sizeof($description)>1)$obj->description=$description[1];
        				else $obj->description=$description[0];

        				// Eviter les champs vides
        				if ($obj->r_prod==="") $obj->r_prod=$obj->description;
        				if ($obj->description==="") $obj->description=$obj->label_prod;
        				
        				
        				$datetime1 = date_create($aujourdhui);
        				$datetime2 = date_create($obj->date_livraison);
        				$interval = date_diff($datetime1, $datetime2);
        				$ecart=$interval->format('%R%a');
        				$livraison_aff=$obj->date_livraison;
        				if($ecart<=7)$color="orange";				
        				if($ecart<=0)$color="red";
						// serem : resolve bug php7->php8
        				// change : $livraison_aff=strftime('%d-%m-%Y', strtotime($obj->date_livraison));	
						$livraison_aff = date('d-m-Y', strtotime($obj->date_livraison));
						// END SEREM				
						
        				print "<tr class=\"".$class."\">";
            				print "<td class=\"nowrap\">".$client_aff."</td><td align=\"left\">".$obj->qty."</td>";
            				print "<td title=\"".$obj->description."\">";
            				// MODIF 503->802 if($obj->id_tache!="")print "<a href=\"/dolibarr/htdocs/projet/tasks/time.php?id=".$obj->id_tache."\"><img src=\"/dolibarr/htdocs/theme/eldy/img/object_technic.png\" border=\"0\" title=\"Pointer des heures sur la tache ".$obj->r_tache."\"></a>";
            				// MODIF saisie non active if($obj->id_tache!="")print "<a href=\"/dolibarr/htdocs/projet/tasks/time.php?id=".$obj->id_tache."&withproject=1"."\"><img src=\"/dolibarr/htdocs/theme/eldy/img/object_technic.png\" border=\"0\" title=\"Pointer des heures sur la tache ".$obj->r_tache."\"></a>";
            				$projet_aff="<a href=\"".dol_buildpath("/$LinkAff[$numAff]/card.php?id=$obj->id_source", 1)."\">".$source_num."</a>";
							// if($obj->id_tache!="")print "<a href=\"/dolibarr/htdocs/projet/tasks/time.php?withproject=1&id=".$obj->id_tache."&action=createtime"."\"><img src=\"/dolibarr/htdocs/theme/eldy/img/object_technic.png\" border=\"0\" title=\"Pointer des heures sur la tache ".$obj->r_tache."\"></a>";
							if($obj->id_tache!="")print "<a href=\"".dol_buildpath("/projet/tasks/time.php?withproject=1&id=$obj->id_tache&action=createtime", 1)."\"><img src=\"".dol_buildpath("/theme/eldy/img/object_technic.png", 1)."\" border=\"0\" title=\"Pointer des heures sur la tache ".$obj->r_tache."\"></a>";
            				if ($DescriptionAff[$numAff]=="r_prod"){
            				    print  "	&nbsp".$obj->r_prod."<input type=\"hidden\" name=\"statut_idx".$k."\" value=\"".$obj->id_tache."\"></td>";
            				} elseif ($DescriptionAff[$numAff]=="description"){
                                print  "	&nbsp".$obj->description."<input type=\"hidden\" name=\"statut_idx".$k."\" value=\"".$obj->id_tache."\"></td>";
                            }
                            print "<td style=\"color:".$color."\" align=\"center\">".$projet_aff."</td>";
                            print "<td style=\"color:".$color."\" align=\"center\">".$obj->firstname."</td>";
            				print "<td style=\"color:".$color."\" align=\"center\">".$livraison_aff."</td>";
            				print "<td align=\"center\" class=\"nowrap\">";
                                print "<select style=COLOR:".colorOfStatut($obj->statut_fab,"fab")." name=\"statut_fab".$k."\" class=\"flat \" class=\"mar\">";
                                    for($j=0;$j<sizeof($statut_fab_option);$j++)print "<option value=\"".$j."\" ".isSelectedOption($j,$obj->statut_fab).">".$statut_fab_option[$j]."</option>";
                				print "	</select>";
                			print "</td>";
                		print "</tr>";
        				$source_previous=$source;
        				$k++;
        			}
        			
        				
        		}
        		
        	}
        	else
        	{
        		//dol_print_error($this->db);
        		print "error0";
        		return -1;
        	}
	
		}
	
		//print '<input type="hidden" name=nb_poste value='.$k.'>'; //Pour le passage du nombre de ligne à maj du status
		print '<input type="hidden" name="nb_poste" value="'.$k.'" />';
	
		print '</form>';	
		
	print "</table>";
	
}
?>