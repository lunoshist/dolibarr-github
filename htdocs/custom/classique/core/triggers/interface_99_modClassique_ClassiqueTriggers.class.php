<?php
/* Copyright (C) 2024 Lucas NOIRIE <lnoirie@serem-electronics.com>
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
 * \file    core/triggers/interface_99_modClassique_ClassiqueTriggers.class.php
 * \ingroup classique
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modClassique_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for Classique module
 */
class InterfaceClassiqueTriggers extends DolibarrTriggers
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
		$this->description = "Classique triggers.";
		$this->version = self::VERSIONS['dev'];
		$this->picto = 'classique@classique';
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
		if (!isModEnabled('classique')) {
			return 0; // If module is not enabled, we do nothing
		}

		// Workflow
		$modName = 'Classique';
		$workflow = fetchWorkflow($modName);


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

		// Classify billed proposal (when order is classified billed)
		if ($action == 'ORDER_CLASSIFY_BILLED') {
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
			if (isModEnabled("propal") && !empty($conf->classique->enabled) && getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_ORDER_CLASSIFIED_BILLED_AUTOMATICLY_CLASSIFY_PROPAL_AS_BILLED')) {
				$object->fetchObjectLinked('', 'propal', $object->id, $object->element);
				if (!empty($object->linkedObjects)) {
					$totalonlinkedelements = 0;
					foreach ($object->linkedObjects['propal'] as $element) {
						if ($element->statut == Propal::STATUS_SIGNED || $element->statut == Propal::STATUS_BILLED) {
							$totalonlinkedelements += $element->total_ht;
						}
					}
					dol_syslog("Amount of linked proposals = ".$totalonlinkedelements.", of order = ".$object->total_ht.", egality is ".json_encode($totalonlinkedelements == $object->total_ht));
					if ($this->shouldClassify($conf, $totalonlinkedelements, $object->total_ht)) {
						foreach ($object->linkedObjects['propal'] as $element) {
							$ret = $element->classifyBilled($user);
						}
					}
				}
				return $ret;
			}
		}

		// Classify billed order (when bill is validated)
		if ($action == 'BILL_VALIDATE') {
			// Classify billed the order
			if (isModEnabled('order') && getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_INVOICE_AMOUNT_CLASSIFY_BILLED_ORDER')) {
				$object->fetchObjectLinked('', 'commande', $object->id, $object->element);
				if (!empty($object->linkedObjects)) {
					$totalonlinkedelements = 0;
					foreach ($object->linkedObjects['commande'] as $element) {
						if ($element->statut == Commande::STATUS_VALIDATED || $element->statut == Commande::STATUS_SHIPMENTONPROCESS || $element->statut == Commande::STATUS_CLOSED) {
							$totalonlinkedelements += $element->total_ht;
						}
					}
					dol_syslog("Amount of linked orders = ".$totalonlinkedelements.", of invoice = ".$object->total_ht.", egality is ".json_encode($totalonlinkedelements == $object->total_ht));
					if ($this->shouldClassify($conf, $totalonlinkedelements, $object->total_ht)) {
						foreach ($object->linkedObjects['commande'] as $element) {
							$ret = $element->classifyBilled($user);
						}
					}
				}
			}

			// Classify billed the order if several bill
			if (isModEnabled('order') && getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_SUM_INVOICES_AMOUNT_CLASSIFY_BILLED_ORDER')) {
				$object->fetchObjectLinked('', 'commande', $object->id, $object->element);
				if (!empty($object->linkedObjects['commande']) && count($object->linkedObjects['commande']) == 1) {	// If the invoice has only 1 source order
					$orderLinked = reset($object->linkedObjects['commande']);
					$orderLinked->fetchObjectLinked($orderLinked->id, '', $orderLinked->element);
					if (count($orderLinked->linkedObjects['facture']) >= 1) {
						$totalHTInvoices = 0;
						$areAllInvoicesValidated = true;
						foreach ($orderLinked->linkedObjects['facture'] as $key => $invoice) {
							if ($invoice->statut == Facture::STATUS_VALIDATED || $object->id == $invoice->id) {
								$totalHTInvoices += (float) $invoice->total_ht;
							} else if ($invoice->type != Facture::TYPE_PROFORMA) {
								$areAllInvoicesValidated = false;
								break;
							}
						}
						if ($areAllInvoicesValidated) {
							$isSameTotal = (price2num($totalHTInvoices, 'MT') == price2num($orderLinked->total_ht, 'MT'));
							dol_syslog("Amount of linked invoices = ".$totalHTInvoices.", of order = ".$orderLinked->total_ht.", isSameTotal = ".(string) $isSameTotal, LOG_DEBUG);
							if ($isSameTotal) {
								$ret = $orderLinked->classifyBilled($user);
								if ($ret < 0) {
									return $ret;
								}
							}
						}
					}
				}
			}
		}

		// Classify shipped the order (when shipment is validated or closed)
		if (($action == 'SHIPPING_VALIDATE') || ($action == 'SHIPPING_CLOSED')) {
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

			if (isModEnabled('order') && isModEnabled("shipping") && !empty($conf->workflow->enabled) &&
				(
					(getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_SHIPMENT_VALIDATED_AUTOMATICLY_CLASSIFY_ORDER_SHIPPED') && ($action == 'SHIPPING_VALIDATE')) ||
					(getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_SHIPMENT_CLOSED_AUTOMATICLY_CLASSIFY_ORDER_SHIPPED') && ($action == 'SHIPPING_CLOSED'))
				)
			) {
				$qtyshipped = array();
				$qtyordred = array();

				// The original sale order is id in $object->origin_id
				// Find all shipments on sale order origin

				if (in_array($object->origin, array('order', 'commande')) && $object->origin_id > 0) {
					require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
					$order = new Commande($this->db);
					$ret = $order->fetch($object->origin_id);
					if ($ret < 0) {
						$this->setErrorsFromObject($order);
						return $ret;
					}
					$ret = $order->fetchObjectLinked($order->id, 'commande', null, 'shipping');
					if ($ret < 0) {
						$this->setErrorsFromObject($order);
						return $ret;
					}
					//Build array of quantity shipped by product for an order
					if (is_array($order->linkedObjects) && count($order->linkedObjects) > 0) {
						foreach ($order->linkedObjects as $type => $shipping_array) {
							if ($type != 'shipping' || !is_array($shipping_array) || count($shipping_array) == 0) {
								continue;
							}
							/** @var Expedition[] $shipping_array */
							foreach ($shipping_array as $shipping) {
								if ($shipping->status <= 0 || !is_array($shipping->lines) || count($shipping->lines) == 0) {
									continue;
								}

								foreach ($shipping->lines as $shippingline) {
									if (isset($qtyshipped[$shippingline->fk_product])) {
										$qtyshipped[$shippingline->fk_product] += $shippingline->qty;
									} else {
										$qtyshipped[$shippingline->fk_product] = $shippingline->qty;
									}
								}
							}
						}
					}

					//Build array of quantity ordered to be shipped
					if (is_array($order->lines) && count($order->lines) > 0) {
						foreach ($order->lines as $orderline) {
							// Exclude lines not qualified for shipment, similar code is found into calcAndSetStatusDispatch() for vendors
							if (!getDolGlobalString('STOCK_SUPPORTS_SERVICES') && $orderline->product_type > 0) {
								continue;
							}
							if (isset($qtyordred[$shippingline->fk_product])) {
								$qtyordred[$orderline->fk_product] += $orderline->qty;
							} else {
								$qtyordred[$orderline->fk_product] = $orderline->qty;
							}
						}
					}
					//dol_syslog(var_export($qtyordred,true),LOG_DEBUG);
					//dol_syslog(var_export($qtyshipped,true),LOG_DEBUG);
					//Compare array
					$diff_array = array_diff_assoc($qtyordred, $qtyshipped);
					if (count($diff_array) == 0) {
						//No diff => mean everything is shipped
						$ret = $order->setStatut(Commande::STATUS_CLOSED, $object->origin_id, $object->origin, 'ORDER_CLOSE');
						if ($ret < 0) {
							$this->setErrorsFromObject($order);
							return $ret;
						}
					}
				}
			}
		}

		// Classify closed the project (when the order is classified shipped)
		if ($action == 'ORDER_CLOSE') {
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

			if (getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_ORDER_CLASSIFIED_SHIPPED_AUTOMATICLY_CLASSIFY_PROJECT_CLOSED')) {
				$project = new Project($object->db);
				if ($project->fetch($object->fk_project) > 0) {
					if ($project->statut == Project::STATUS_VALIDATED) {
						if ($project->setClose($user) <= 0) {
							return -1;
						} else {
							return 0;
						}
					}
				} else {
					return -1;
				}
			}			
		}

		// Classify closed the project (when all task are done)
		if ($action == 'TASK_MODIFY') {
			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

			if (getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_ALL_TASKS_DONE_AUTOMATICLY_CLASSIFY_PROJECT_CLOSED')) {
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
							if ($project->setClose($user) <= 0) {
								return -1;
							} else {
								return 0;
							}
						}
					}
				} else {
					return -1;
				}
			}			
		}

		return 0;
	}

	/**
	 * @param Conf $conf                  Dolibarr settings object
	 * @param float $totalonlinkedelements  Sum of total amounts (excl VAT) of
	 *                                      invoices linked to $object
	 * @param float $object_total_ht        The total amount (excl VAT) of the object
	 *                                      (an order, a proposal, a bill, etc.)
	 * @return bool  True if the amounts are equal (rounded on total amount)
	 *               True if the module is configured to skip the amount equality check
	 *               False otherwise.
	 */
	private function shouldClassify($conf, $totalonlinkedelements, $object_total_ht) {
		// Workflow
		$modName = 'Classique';
		$workflow = fetchWorkflow($modName);

		// if the configuration allows unmatching amounts, allow classification anyway
		if (getDolGlobalString('WORKFLOW_'.$workflow->rowid.'_CLASSIFY_IF_AMOUNTS_ARE_DIFFERENTS')) {
			return true;
		}
		// if the amount are same, allow classification, else deny
		return (price2num($totalonlinkedelements, 'MT') == price2num($object_total_ht, 'MT'));
	}
}
