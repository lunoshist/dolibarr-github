<?php
/* Copyright (C) 2024 Philippe BERAUD <pberaud@serem-electronics.com>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
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
 * \file    core/triggers/interface_99_modAffaire_AffaireTriggers.class.php
 * \ingroup affaire
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modAffaire_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for Affaire module
 */
class InterfaceAffaireTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		parent::__construct($db);
		$this->family = "demo";
		$this->description = "Affaire triggers.";
		$this->version = self::VERSIONS['dev'];
		$this->picto = 'affaire@affaire';
	}

	/**
	 * Function called when a Dolibarr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		Return integer <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (!isModEnabled('affaire')) {
			return 0; // If module is not enabled, we do nothing
		}

        dol_include_once('/affaire/class/affaire.class.php');
        dol_include_once('/affaire/lib/affaire_affaire.lib.php');
        dol_include_once('/affaire/lib/affaire.lib.php');  

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		}

		$affaireID = getLinkedAff($object);
		if ($affaireID) {
			$affaire = new Affaire($object->db);
			$res = $affaire->fetch($affaireID);

			// Change the status
			switch ($action) {
				// Users
				//case 'USER_CREATE':
				//case 'USER_MODIFY':
				//case 'USER_NEW_PASSWORD':
				//case 'USER_ENABLEDISABLE':
				//case 'USER_DELETE':

				// Actions
				//case 'ACTION_MODIFY':
				//case 'ACTION_CREATE':
				//case 'ACTION_DELETE':

				// Groups
				//case 'USERGROUP_CREATE':
				//case 'USERGROUP_MODIFY':
				//case 'USERGROUP_DELETE':

				// Companies
				//case 'COMPANY_CREATE':
				//case 'COMPANY_MODIFY':
				//case 'COMPANY_DELETE':

				// Contacts
				//case 'CONTACT_CREATE':
				//case 'CONTACT_MODIFY':
				//case 'CONTACT_DELETE':
				//case 'CONTACT_ENABLEDISABLE':

				// Products
				//case 'PRODUCT_CREATE':
				//case 'PRODUCT_MODIFY':
				//case 'PRODUCT_DELETE':
				//case 'PRODUCT_PRICE_MODIFY':
				//case 'PRODUCT_SET_MULTILANGS':
				//case 'PRODUCT_DEL_MULTILANGS':

				//Stock movement
				//case 'STOCK_MOVEMENT':

				//MYECMDIR
				//case 'MYECMDIR_CREATE':
				//case 'MYECMDIR_MODIFY':
				//case 'MYECMDIR_DELETE':

				// Sales orders
				case 'ORDER_CREATE':
					// TODO mettre le bon client pour l'affaire
				//case 'ORDER_MODIFY':
				case 'ORDER_SETDRAFT':
				case 'ORDER_UNVALIDATE':
					if (getDolGlobalInt('WORKFLOW_2_ORDER_DRAFT_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_DRAFT_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'ORDER_VALIDATE':	
				case 'ORDER_REOPEN':
					if (getDolGlobalInt('WORKFLOW_2_ORDER_VALIDATED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_VALIDATED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				//case 'ORDER_SENTBYMAIL':
				case 'ORDER_BILLED':
				case 'ORDER_CLASSIFY_BILLED':		// TODO Replace it with ORDER_BILLED
					if ($object->statut == Commande::STATUS_CLOSED) {
						$action = 'ORDER_CLOSE_FOR_REAL';
						return $this->orderCloseForReal($action, $object, $user, $langs, $conf);
					} else if (getDolGlobalInt('WORKFLOW_2_ORDER_BILLED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_BILLED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'ORDER_CLASSIFY_UNBILLED':	// TODO Replace it with ORDER_UNBILLED
					if (getDolGlobalInt('WORKFLOW_2_ORDER_UNBILLED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_UNBILLED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'ORDER_CLOSE':		// ~ ORDER_SHIPPED
					if ($object->billed) {
						$action = 'ORDER_CLOSE_FOR_REAL';
						return $this->orderCloseForReal($action, $object, $user, $langs, $conf);
					} else if (getDolGlobalInt('WORKFLOW_2_ORDER_SHIPPED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_CLOSED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'ORDER_CLOSE_FOR_REAL':
					return $this->orderCloseForReal($action, $object, $user, $langs, $conf);
				case 'ORDER_CANCEL':
					if (getDolGlobalInt('WORKFLOW_2_ORDER_CANCELED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_CANCELED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la order
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'ORDER_DELETE':
					// Check conditions (pour changer le status object, et celui étape)
					// A- checkConditions($arg, $affaire)
					// B- Ne pas enlever le status s'il y a une autre commande
					if (!empty(checkCommandeExist($affaire)) && checkCommandeExist($affaire) != $object->id) {
						return 0;
					}
					
					// Si l'on arrive là on peut enlever le status de l'étape
					$steplabel = empty(getDolGlobalString('STEP_SALE_ORDER_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'cmde' : getDolGlobalString('STEP_SALE_ORDER_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
					$error = change_status($affaire, 'no_status', '', $steplabel);
					if (!$error) {
						return 0;
					} else {
						return -1;
					}
				//case 'LINEORDER_INSERT':
				//case 'LINEORDER_UPDATE':
				//case 'LINEORDER_DELETE':

				// Supplier orders
				//case 'ORDER_SUPPLIER_CREATE':
				//case 'ORDER_SUPPLIER_MODIFY':
				//case 'ORDER_SUPPLIER_VALIDATE':
				//case 'ORDER_SUPPLIER_DELETE':
				//case 'ORDER_SUPPLIER_APPROVE':
				//case 'ORDER_SUPPLIER_CLASSIFY_BILLED':		// TODO Replace with ORDER_SUPPLIER_BILLED
				//case 'ORDER_SUPPLIER_CLASSIFY_UNBILLED':		// TODO Replace with ORDER_SUPPLIER_UNBILLED
				//case 'ORDER_SUPPLIER_REFUSE':
				//case 'ORDER_SUPPLIER_CANCEL':
				//case 'ORDER_SUPPLIER_SENTBYMAIL':
				//case 'ORDER_SUPPLIER_RECEIVE':
				//case 'LINEORDER_SUPPLIER_DISPATCH':
				//case 'LINEORDER_SUPPLIER_CREATE':
				//case 'LINEORDER_SUPPLIER_UPDATE':
				//case 'LINEORDER_SUPPLIER_DELETE':

				// Proposals
				//case 'PROPAL_CREATE':
				//case 'PROPAL_MODIFY':
				case 'PROPAL_SET_DRAFT':
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_DRAFT_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_DRAFT_STATUS');
						$status = fetchStatus($statusID);
			
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)

						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)

						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'PROPAL_VALIDATE':
				case 'PROPAL_REOPEN':
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_VALIDATED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_VALIDATED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre l'étape à validé si il reste des propal en brouillon
						$array_of_propal = checkPropalExist($affaire);
						if (is_array($array_of_propal)) {
							foreach ($array_of_propal as $key => $propal) {
								if  ($propal == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_propal_extrafields` WHERE `fk_object` = ".$propal;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type <= $status->fk_type) {
										setEventMessages('Une propal est toujours en brouillon', null, 'warnings');
										return 0;
									}
								}
							}
						}
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				//case 'PROPAL_SENTBYMAIL':
				case 'PROPAL_BILLED':
				case 'PROPAL_CLASSIFY_BILLED':		// TODO Replace it with PROPAL_BILLED
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_BILLED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_BILLED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre l'étape à traité si une autre proposition est en cours
						$array_of_propal = checkPropalExist($affaire);
						if (is_array($array_of_propal)) {
							foreach ($array_of_propal as $key => $propal) {
								if  ($propal == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_propal_extrafields` WHERE `fk_object` = ".$propal;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type < 200) {
										setEventMessages('Une propal est toujours en cours', null, 'errors');
										return -1;
									}
								}
							}
						}
			
						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				//case 'PROPAL_CLASSIFY_UNBILLED':		// TODO Replace it with PROPAL_UNBILLED
				case 'PROPAL_CLOSE_SIGNED':
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_SIGNED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_SIGNED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre à signé si une autre proposition est signé
						$array_of_propal = checkPropalExist($affaire);
						if (is_array($array_of_propal)) {
							foreach ($array_of_propal as $key => $propal) {
								if  ($propal == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_propal_extrafields` WHERE `fk_object` = ".$propal;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type == 200) {
										setEventMessages('Une propal est déjà signée', null, 'errors');
										return -1;
									}
								}
							}
						}
			
						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'PROPAL_CLOSE_REFUSED':
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_NOTSIGNED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_NOTSIGNED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre l'étape à non-signé si une autre proposition est signé
						$array_of_propal = checkPropalExist($affaire);
						if (is_array($array_of_propal)) {
							foreach ($array_of_propal as $key => $propal) {
								if  ($propal == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_propal_extrafields` WHERE `fk_object` = ".$propal;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type == 200) {
										// setEventMessages('Une propal est déjà signée !', null, 'mesgs');
										return -1;
									}
								}
							}
						}
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'PROPAL_CANCEL':
					if (getDolGlobalInt('WORKFLOW_2_PROPAL_CANCELED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROPAL_CANCELED_STATUS');
						$status = fetchStatus($statusID);

						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la propal
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROPAL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'PROPAL_DELETE':
					// Check conditions (pour changer le status object, et celui étape)
					// A- checkConditions($arg, $affaire)
					// B- Ne pas enlever le status s'il y a une autre proposition
					if (!empty(checkPropalExist($affaire)) && checkPropalExist($affaire) != $object->id) {
						return 0;
					}
					
					// Si l'on arrive là on peut enlever le status de l'étape
					$steplabel = empty(getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'propal' : getDolGlobalString('STEP_PROPAL_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
					$error = change_status($affaire, 'no_status', '', $steplabel);
					if (!$error) {
						return 0;
					} else {
						return -1;
					}
				//case 'LINEPROPAL_INSERT':
				//case 'LINEPROPAL_UPDATE':
				//case 'LINEPROPAL_DELETE':

				// SupplierProposal
				//case 'SUPPLIER_PROPOSAL_CREATE':
				//case 'SUPPLIER_PROPOSAL_MODIFY':
				//case 'SUPPLIER_PROPOSAL_VALIDATE':
				//case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
				//case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
				//case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
				//case 'SUPPLIER_PROPOSAL_DELETE':
				//case 'LINESUPPLIER_PROPOSAL_INSERT':
				//case 'LINESUPPLIER_PROPOSAL_UPDATE':
				//case 'LINESUPPLIER_PROPOSAL_DELETE':

				// Contracts
				//case 'CONTRACT_CREATE':
				//case 'CONTRACT_MODIFY':
				//case 'CONTRACT_ACTIVATE':
				//case 'CONTRACT_CANCEL':
				//case 'CONTRACT_CLOSE':
				//case 'CONTRACT_DELETE':
				//case 'LINECONTRACT_INSERT':
				//case 'LINECONTRACT_UPDATE':
				//case 'LINECONTRACT_DELETE':

				// Bills
				//case 'BILL_CREATE':
				//case 'BILL_MODIFY':
				case 'BILL_UNVALIDATE':		// ~ BILL_SET_DRAFT
					if (getDolGlobalInt('WORKFLOW_2_INVOICE_DRAFT_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_INVOICE_DRAFT_STATUS');
						$status = fetchStatus($statusID);
			
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la facture
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'BILL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
												
						// Si l'on arrive là on peut mettre le status de l'étape à payé
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'BILL_UNPAYED':		// ~ BILL_REOPEN
				case 'BILL_VALIDATE':
					if (getDolGlobalInt('WORKFLOW_2_INVOICE_VALIDATED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_INVOICE_VALIDATED_STATUS');
						$status = fetchStatus($statusID);
			
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la facture
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'BILL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
												
						// Si l'on arrive là on peut mettre le status de l'étape à payé
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				//case 'BILL_SENTBYMAIL':
				case 'BILL_PAYED':
					if (getDolGlobalInt('WORKFLOW_2_INVOICE_PAID_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_INVOICE_PAID_STATUS');
						$status = fetchStatus($statusID);
			
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la facture
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'BILL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre l'étape à terminé si il reste des factures en cours
						$array_of_fact = checkFactureExist($affaire);
						if (is_array($array_of_fact)) {
							foreach ($array_of_fact as $key => $fact) {
								if  ($fact->id == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_facture_extrafields` WHERE `fk_object` = ".$fact;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type < 200) {
										setEventMessages('Une facture est toujours en cours', null, 'warnings');
										return 0;
									}
								}
							}
						}
						
						// Si l'on arrive là on peut mettre le status de l'étape à payé
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'BILL_CANCEL':
					if (getDolGlobalInt('WORKFLOW_2_INVOICE_ABANDONED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_INVOICE_ABANDONED_STATUS');
						$status = fetchStatus($statusID);
			
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
			
						// 1 - Mettre à jour le status de la facture
						$object->oldcopy = dol_clone($object, 2);
			
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'BILL_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
			
			
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre le status de l'étape à abandonnée s'il y a une autre facture
						if (!empty(checkFactureExist($affaire)) && checkFactureExist($affaire) != $object->id) {
							return 0;
						}
												
						// Si l'on arrive là on peut mettre le status de l'étape à payé
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'BILL_DELETE':
					// Check conditions (pour changer le status object, et celui étape)
					// A- checkConditions($arg, $affaire)
					// B- Ne pas enlever le status s'il y a une autre facture
					if (!empty(checkFactureExist($affaire)) && checkFactureExist($affaire) != $object->id) {
						return 0;
					}
					
					// Si l'on arrive là on peut enlever le status de l'étape
					$steplabel = empty(getDolGlobalString('STEP_EXPE_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'expe' : getDolGlobalString('STEP_EXPE_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
					$error = change_status($affaire, 'no_status', '', $steplabel);
					if (!$error) {
						return 0;
					} else {
						return -1;
					}
				//case 'LINEBILL_INSERT':
				//case 'LINEBILL_UPDATE':
				//case 'LINEBILL_DELETE':

				// Recurring Bills
				//case 'BILLREC_MODIFY':
				//case 'BILLREC_DELETE':
				//case 'BILLREC_AUTOCREATEBILL':
				//case 'LINEBILLREC_MODIFY':
				//case 'LINEBILLREC_DELETE':

				//Supplier Bill
				//case 'BILL_SUPPLIER_CREATE':
				//case 'BILL_SUPPLIER_UPDATE':
				//case 'BILL_SUPPLIER_DELETE':
				//case 'BILL_SUPPLIER_PAYED':
				//case 'BILL_SUPPLIER_UNPAYED':
				//case 'BILL_SUPPLIER_VALIDATE':
				//case 'BILL_SUPPLIER_UNVALIDATE':
				//case 'LINEBILL_SUPPLIER_CREATE':
				//case 'LINEBILL_SUPPLIER_UPDATE':
				//case 'LINEBILL_SUPPLIER_DELETE':

				// Payments
				//case 'PAYMENT_CUSTOMER_CREATE':
				//case 'PAYMENT_SUPPLIER_CREATE':
				//case 'PAYMENT_ADD_TO_BANK':
				//case 'PAYMENT_DELETE':

				// Online
				//case 'PAYMENT_PAYBOX_OK':
				//case 'PAYMENT_PAYPAL_OK':
				//case 'PAYMENT_STRIPE_OK':

				// Donation
				//case 'DON_CREATE':
				//case 'DON_UPDATE':
				//case 'DON_DELETE':

				// Interventions
				//case 'FICHINTER_CREATE':
				//case 'FICHINTER_MODIFY':
				//case 'FICHINTER_VALIDATE':
				//case 'FICHINTER_CLASSIFY_BILLED':			// TODO Replace it with FICHINTER_BILLED
				//case 'FICHINTER_CLASSIFY_UNBILLED':		// TODO Replace it with FICHINTER_UNBILLED
				//case 'FICHINTER_DELETE':
				//case 'LINEFICHINTER_CREATE':
				//case 'LINEFICHINTER_UPDATE':
				//case 'LINEFICHINTER_DELETE':

				// Members
				//case 'MEMBER_CREATE':
				//case 'MEMBER_VALIDATE':
				//case 'MEMBER_SUBSCRIPTION':
				//case 'MEMBER_MODIFY':
				//case 'MEMBER_NEW_PASSWORD':
				//case 'MEMBER_RESILIATE':
				//case 'MEMBER_DELETE':

				// Categories
				//case 'CATEGORY_CREATE':
				//case 'CATEGORY_MODIFY':
				//case 'CATEGORY_DELETE':
				//case 'CATEGORY_SET_MULTILANGS':


				// Project tasks
				//case 'TASK_CREATE':
				case 'TASK_DELETE':
					// Delete link with object
					$res = $object->deleteObjectLinked();
					if ($res < 0) {
						return -1;
					} else {
						return 0;
					}
				case 'TASK_MODIFY':
					dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

					$project = new Project($object->db);
					if ($project->fetch($object->fk_project) > 0) {
						if ($project->statut == Project::STATUS_VALIDATED) {
							$project->getLinesArray(null); // this method does not return <= 0 if fails
							$projectCompleted = array_reduce(
								$project->lines,
								/**
								 * @param bool $allTasksCompleted
								 * @param Task $task
								 * @return bool
								 */
								static function ($allTasksCompleted, $task) {
									return $allTasksCompleted && $task->progress >= 100;
								},
								1
							);
							if ($projectCompleted) {
								$action = 'PROD_FINISHED';
							} else {
								return 0;
							}
						}
					}
				// Production
				case 'PROD_FINISHED':
					if (getDolGlobalInt('WORKFLOW_2_PROD_FINISHED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROD_FINISHED_STATUS');
						$status = fetchStatus($statusID);
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}

				// Projects
				//case 'PROJECT_CREATE':
				//case 'PROJECT_MODIFY':
				case 'PROJECT_CLOSE':
					if (getDolGlobalInt('WORKFLOW_2_PROJECT_CLOSED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_PROJECT_CLOSED_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'PROJECT_DELETE':
					// Delete linked object
					$res = $object->deleteObjectLinked();
					if ($res < 0) {
						return -1;
					}

					// Si l'on arrive là on peut enlever le status de l'étape
					$steplabel = empty(getDolGlobalString('STEP_PROD_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'prod' : getDolGlobalString('STEP_PROD_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
					$error = change_status($affaire, 'no_status', '', $steplabel);
					if (!$error) {
						return 0;
					} else {
						return -1;
					}

				// Task time spent
				//case 'TASK_TIMESPENT_CREATE':
				//case 'TASK_TIMESPENT_MODIFY':
				//case 'TASK_TIMESPENT_DELETE':
				//case 'PROJECT_ADD_CONTACT':
				//case 'PROJECT_DELETE_CONTACT':
				//case 'PROJECT_DELETE_RESOURCE':

				// Shipping
				//case 'SHIPPING_CREATE':
				//case 'SHIPPING_MODIFY':
				case 'SHIPPING_SET_DRAFT':
				case 'SHIPMENT_UNVALIDATE':
					if (getDolGlobalInt('WORKFLOW_2_EXPEDITION_DRAFT_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_EXPEDITION_DRAFT_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'SHIPPING_REOPEN':
				case 'SHIPPING_VALIDATE':
					if (getDolGlobalInt('WORKFLOW_2_EXPEDITION_VALIDATED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_EXPEDITION_VALIDATED_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				//case 'SHIPPING_SENTBYMAIL':
				case 'SHIPPING_BILLED':
					if (getDolGlobalInt('WORKFLOW_2_EXPEDITION_BILLED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_EXPEDITION_BILLED_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'SHIPPING_CLOSED':
					if (getDolGlobalInt('WORKFLOW_2_EXPEDITION_CLOSED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_EXPEDITION_CLOSED_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						// B- Ne pas mettre l'étape à clôturé si une autre expedition est en cours
						$array_of_expe = checkExpeExist($affaire);
						if (is_array($array_of_expe)) {
							foreach ($array_of_expe as $key => $expe) {
								if  ($expe == $object->id) {
									continue;
								}
								$sql = "SELECT aff_status FROM `llx_expedition_extrafields` WHERE `fk_object` = ".$expe;
								$resql = $object->db->query($sql);
								if ($resql) {
									$res = $object->db->fetch_object($resql);
									$rStatus = fetchStatus($res->aff_status);
			
									if ($rStatus->fk_type < 200) {
										// setEventMessages('Une expe est toujours en cours !', null, 'mesgs');
										return -1;
									}
								}
							}
						}
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'SHIPPING_CANCEL':
					if (getDolGlobalInt('WORKFLOW_2_EXPEDITION_CANCELED_STATUS')) {
						dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
						
						$statusID = getDolGlobalInt('WORKFLOW_2_EXPEDITION_CANCELED_STATUS');
						$status = fetchStatus($statusID);
		
						// Check conditions (pour changer le status object, et celui étape)
						// A- checkConditions($arg, $affaire)
		
						// 1 - Mettre à jour le status du project
						$object->oldcopy = dol_clone($object, 2);
		
						$object->array_options["options_aff_status"] = $statusID;
						$result = $object->updateExtraField('aff_status', 'PROJECT_MODIFY');
						if ($result < 0) {
							setEventMessages($object->error, $object->errors, 'errors');
							return -1;
						}
		
		
						// 2 - Mettre à jour le status de l'étape
						// Check conditions (pour changer le status étape)
						// A- checkConditions($arg, $affaire)
						
						// Si l'on arrive là on peut mettre à jour le status de l'étape
						$error = change_status($affaire, $status, '', '', '', '', $object);
						if (!$error) {
							return 0;
						} else {
							return -1;
						}
					} else {
						break;
					}
				case 'SHIPPING_DELETE':
					// Check conditions (pour changer le status object, et celui étape)
					// A- checkConditions($arg, $affaire)
					// B- Ne pas enlever le status s'il y a une autre expedition
					if (!empty(checkExpeExist($affaire)) && checkExpeExist($affaire) != $object->id) {
						return 0;
					}
					
					// Si l'on arrive là on peut enlever le status de l'étape
					$steplabel = empty(getDolGlobalString('STEP_EXPE_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'expe' : getDolGlobalString('STEP_EXPE_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
					$error = change_status($affaire, 'no_status', '', $steplabel);
					if (!$error) {
						return 0;
					} else {
						return -1;
					}

				// and more...

				default:
					dol_syslog("Trigger '".$this->name."' for action '".$action."' launched by ".__FILE__.". id=".$object->id);
					break;
			}
		}

		return 0;
	}

	public function orderCloseForReal($action, $object, User $user, Translate $langs, Conf $conf) {
		$affaireID = getLinkedAff($object);
		if ($affaireID) {
			$affaire = new Affaire($object->db);
			$res = $affaire->fetch($affaireID);
		
			if (getDolGlobalInt('WORKFLOW_2_ORDER_CLOSED_STATUS')) {
				dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);			
				
				$statusID = getDolGlobalInt('WORKFLOW_2_ORDER_CLOSED_STATUS');
				$status = fetchStatus($statusID);

				// Check conditions (pour changer le status object, et celui étape)
				// A- checkConditions($arg, $affaire)
				// B- Ne pas changer le status s'il est inférieur à 200 (Ex:att régul)
				$steplabel = empty(getDolGlobalString('STEP_SALE_ORDER_FOR_WORKFLOW_'.$affaire->fk_workflow_type)) ? 'cmde' : getDolGlobalString('STEP_SALE_ORDER_FOR_WORKFLOW_'.$affaire->fk_workflow_type);
				$conditions = "TYPE:$steplabel:>=:200";
				$valid = checkConditions($conditions, $affaire);
				if (!$valid) {
					return 0;
				}

				// 1 - Mettre à jour le status de la order
				$object->oldcopy = dol_clone($object, 2);

				$object->array_options["options_aff_status"] = $statusID;
				$result = $object->updateExtraField('aff_status', 'ORDER_MODIFY');
				if ($result < 0) {
					setEventMessages($object->error, $object->errors, 'errors');
					return -1;
				}


				// 2 - Mettre à jour le status de l'étape
				// Check conditions (pour changer le status étape)
				// A- checkConditions($arg, $affaire)
				
				// Si l'on arrive là on peut mettre à jour le status de l'étape
				$error = change_status($affaire, $status, '', '', '', '', $object);
				if (!$error) {
					return 0;
				} else {
					return -1;
				}
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}
}
