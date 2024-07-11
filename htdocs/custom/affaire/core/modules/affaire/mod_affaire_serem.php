<?php
/* Copyright (C) 2005-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2014 		Charles-Fr BENKE 	<charles.fr@benke.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *  \file       htdocs/core/modules/affaire/mod_affaire_serem.php
 *  \ingroup    affaire
 *  \brief      File of class to manage customer order numbering rules
 */
dol_include_once('/affaire/core/modules/affaire/modules_affaire.php');

/**
 *	Class to manage affaire numbering rules
 */
class mod_affaire_serem extends ModeleNumRefAffaire
{
	var $version='serem 19.0.1';		// 'development', 'experimental', 'dolibarr'
	var $prefix='AFF';
	var $error='';
	var $nom='AFFaire SEREM';


    /**
     *  Return description of numbering module
     *
     *  @return     string      Text with description
     */
    function info($langs)
    {
    	global $langs;
      	return $langs->trans("SimpleNumRefModelDesc",$this->prefix);
    }


	/**
	 *  Renvoi un exemple de numerotation
	 *
	 *  @return     string      Example
	 */
	function getExample()
	{
		$yy = date('y');
		$ddd = sprintf("%03s",date('z')+1);

		return $this->prefix.$yy.$ddd."01-SOCNAME";
	}


	/**
	 *  Test si les numeros deje en vigueur dans la base ne provoquent pas de
	 *  de conflits qui empechera cette numerotation de fonctionner.
	 *
	 *  @return     boolean     false si conflit, true si ok
	 */
	function canBeActivated($object)
	{
		global $conf,$langs;

		$aryyddd=''; $max='';

		$posindice=8;
		$sql = "SELECT MAX(SUBSTRING(ref FROM ".$posindice.")) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."affaire_affaire";
		$sql.= " WHERE ref LIKE '".$this->prefix."____-%'";
		$sql.= " AND entity = ".$conf->entity;

		$resql=$db->query($sql);
		if ($resql)
		{
			$row = $db->fetch_row($resql);
			if ($row) { $aryyddd = substr($row[0],0,7); $max=$row[0]; }
		}
		if ($aryyddd && ! preg_match('/'.$this->prefix.'[0-9][0-9][0-3][0-9][0-9]/i',$aryyddd))
		{
			$langs->load("errors");
			$this->error=$langs->trans('ErrorNumRefModel', $max);
			return false;
		}

		return true;
	}

	/**
	 * 	Return next free value
	 *
	 *  @param	Societe		$objsoc     Object thirdparty
	 *  @param  Object		$object		Object we need next value for
	 *  @return string      			Value if KO, <0 if KO
	 */
	function getNextValue($object)
	{
		global $db,$conf;

		$yy = date("y");
		$ddd = sprintf("%03s",date("z") +1);

		// D'abord on recupere la valeur max
		$sql = "SELECT MAX(SUBSTRING(ref, 9, 2)) as max";
		$sql.= " FROM ".MAIN_DB_PREFIX."affaire_affaire";
		$sql.= " WHERE left(ref, 8) = '".$this->prefix.$yy.$ddd."'";

		$resql=$db->query($sql);
		if ($resql)
		{
			$obj = $db->fetch_object($resql);
			if ($obj) $max = intval($obj->max);			
			else $max=0;
		}
		else
		{
			dol_syslog("mod_affaire_serem::getNextValue sql=".$sql);
			return -1;
		}

		//$date=time();
		$num = sprintf("%02s",$max+1);
		
		// on récupère le nom du client
		// $object->fetch_thirdparty();
		// //var_dump($object->client);
		// // on vire les espaces
		// $nomclient= str_replace(' ', '', $object->thirdparty->nom);
		// dol_syslog("mod_affaire_serem::getNextValue return ".$this->prefix.$yy.$ddd.$num."-".substr($nomclient,0,7));
		// return $this->prefix.$yy.$ddd.$num."-".substr($nomclient,0,7);
		dol_syslog("mod_affaire_serem::getNextValue return ".$this->prefix.$yy.$ddd.$num);
		return $this->prefix.$yy.$ddd.$num;
	}


	/**
	 *  Return next free value
	 *
	 *  @param	Societe		$objsoc     Object third party
	 * 	@param	string		$objforref	Object for number to search
	 *  @return string      			Next free value
	 */
	function affaire_get_num($objforref)
	{
		return $this->getNextValue($objforref);
	}

}
?>
