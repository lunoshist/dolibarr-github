<?php

require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';

/**
 *  Class to add and deleta lines even if not draft
 */
class AFF_Commande extends Commande
{
	/**
	 *  Delete an order line
	 *
	 *	@param      User	$user		User object
	 *  @param      int		$lineid		Id of line to delete
	 *  @param		int		$id			Id of object (for a check)
	 *  @return     int        		 	>0 if OK, 0 if nothing to do, <0 if KO
	 */
	public function deleteline($user = null, $lineid = 0, $id = 0)
	{
		// if ($this->statut == self::STATUS_DRAFT) {
			$this->db->begin();

			// Delete line
			$line = new OrderLine($this->db);

			$line->context = $this->context;

			// Load data
			$line->fetch($lineid);

			if ($id > 0 && $line->fk_commande != $id) {
				$this->error = 'ErrorLineIDDoesNotMatchWithObjectID';
				return -1;
			}

			// Memorize previous line for triggers
			$staticline = clone $line;
			$line->oldline = $staticline;

			if ($line->delete($user) > 0) {
				$result = $this->update_price(1);

				if ($result > 0) {
					$this->db->commit();
					return 1;
				} else {
					$this->db->rollback();
					$this->error = $this->db->lasterror();
					return -1;
				}
			} else {
				$this->db->rollback();
				$this->error = $line->error;
				return -1;
			}
		// } else {
		// 	$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
		// 	return -1;
		// }
	}

	/**
	 *	Add an order line into database (linked to product/service or not)
	 *
	 *	@param      string			$desc            	Description of line
	 *	@param      float			$pu_ht    	        Unit price (without tax)
	 *	@param      float			$qty             	Quantite
	 * 	@param    	float			$txtva           	Force Vat rate, -1 for auto (Can contain the vat_src_code too with syntax '9.9 (CODE)')
	 * 	@param		float			$txlocaltax1		Local tax 1 rate (deprecated, use instead txtva with code inside)
	 * 	@param		float			$txlocaltax2		Local tax 2 rate (deprecated, use instead txtva with code inside)
	 *	@param      int				$fk_product      	Id of product
	 *	@param      float			$remise_percent  	Percentage discount of the line
	 *	@param      int				$info_bits			Bits of type of lines
	 *	@param      int				$fk_remise_except	Id remise
	 *	@param      string			$price_base_type	HT or TTC
	 *	@param      float			$pu_ttc    		    Prix unitaire TTC
	 *	@param      int|string		$date_start       	Start date of the line - Added by Matelli (See http://matelli.fr/showcases/patchs-dolibarr/add-dates-in-order-lines.html)
	 *	@param      int|string		$date_end         	End date of the line - Added by Matelli (See http://matelli.fr/showcases/patchs-dolibarr/add-dates-in-order-lines.html)
	 *	@param      int				$type				Type of line (0=product, 1=service). Not used if fk_product is defined, the type of product is used.
	 *	@param      int				$rang             	Position of line
	 *	@param		int				$special_code		Special code (also used by externals modules!)
	 *	@param		int				$fk_parent_line		Parent line
	 *  @param		int				$fk_fournprice		Id supplier price
	 *  @param		int				$pa_ht				Buying price (without tax)
	 *  @param		string			$label				Label
	 *  @param		array			$array_options		extrafields array. Example array('options_codeforfield1'=>'valueforfield1', 'options_codeforfield2'=>'valueforfield2', ...)
	 * 	@param 		string			$fk_unit 			Code of the unit to use. Null to use the default one
	 * 	@param		string		    $origin				Depend on global conf MAIN_CREATEFROM_KEEP_LINE_ORIGIN_INFORMATION can be 'orderdet', 'propaldet'..., else 'order','propal,'....
	 *  @param		int			    $origin_id			Depend on global conf MAIN_CREATEFROM_KEEP_LINE_ORIGIN_INFORMATION can be Id of origin object (aka line id), else object id
	 * 	@param		double			$pu_ht_devise		Unit price in currency
	 * 	@param		string			$ref_ext		    line external reference
	 *  @param		int				$noupdateafterinsertline	No update after insert of line
	 *	@return     int             					>0 if OK, <0 if KO
	 *
	 *	@see        add_product()
	 *
	 *	Les parametres sont deja cense etre juste et avec valeurs finales a l'appel
	 *	de cette methode. Aussi, pour le taux tva, il doit deja avoir ete defini
	 *	par l'appelant par la methode get_default_tva(societe_vendeuse,societe_acheteuse,produit)
	 *	et le desc doit deja avoir la bonne valeur (a l'appelant de gerer le multilangue)
	 */
	public function addline($desc, $pu_ht, $qty, $txtva, $txlocaltax1 = 0, $txlocaltax2 = 0, $fk_product = 0, $remise_percent = 0, $info_bits = 0, $fk_remise_except = 0, $price_base_type = 'HT', $pu_ttc = 0, $date_start = '', $date_end = '', $type = 0, $rang = -1, $special_code = 0, $fk_parent_line = 0, $fk_fournprice = null, $pa_ht = 0, $label = '', $array_options = array(), $fk_unit = null, $origin = '', $origin_id = 0, $pu_ht_devise = 0, $ref_ext = '', $noupdateafterinsertline = 0)
	{
		global $mysoc, $conf, $langs, $user;

		$logtext = "::addline commandeid=$this->id, desc=$desc, pu_ht=$pu_ht, qty=$qty, txtva=$txtva, fk_product=$fk_product, remise_percent=$remise_percent";
		$logtext .= ", info_bits=$info_bits, fk_remise_except=$fk_remise_except, price_base_type=$price_base_type, pu_ttc=$pu_ttc, date_start=$date_start";
		$logtext .= ", date_end=$date_end, type=$type special_code=$special_code, fk_unit=$fk_unit, origin=$origin, origin_id=$origin_id, pu_ht_devise=$pu_ht_devise, ref_ext=$ref_ext";
		dol_syslog(get_class($this).$logtext, LOG_DEBUG);

		// if ($this->statut == self::STATUS_DRAFT) {
			include_once DOL_DOCUMENT_ROOT.'/core/lib/price.lib.php';

			// Clean parameters

			if (empty($remise_percent)) {
				$remise_percent = 0;
			}
			if (empty($qty)) {
				$qty = 0;
			}
			if (empty($info_bits)) {
				$info_bits = 0;
			}
			if (empty($rang)) {
				$rang = 0;
			}
			if (empty($txtva)) {
				$txtva = 0;
			}
			if (empty($txlocaltax1)) {
				$txlocaltax1 = 0;
			}
			if (empty($txlocaltax2)) {
				$txlocaltax2 = 0;
			}
			if (empty($fk_parent_line) || $fk_parent_line < 0) {
				$fk_parent_line = 0;
			}
			if (empty($this->fk_multicurrency)) {
				$this->fk_multicurrency = 0;
			}
			if (empty($ref_ext)) {
				$ref_ext = '';
			}

			$remise_percent = price2num($remise_percent);
			$qty = price2num($qty);
			$pu_ht = price2num($pu_ht);
			$pu_ht_devise = price2num($pu_ht_devise);
			$pu_ttc = price2num($pu_ttc);
			$pa_ht = price2num($pa_ht);
			if (!preg_match('/\((.*)\)/', $txtva)) {
				$txtva = price2num($txtva); // $txtva can have format '5,1' or '5.1' or '5.1(XXX)', we must clean only if '5,1'
			}
			$txlocaltax1 = price2num($txlocaltax1);
			$txlocaltax2 = price2num($txlocaltax2);
			if ($price_base_type == 'HT') {
				$pu = $pu_ht;
			} else {
				$pu = $pu_ttc;
			}
			$label = trim($label);
			$desc = trim($desc);

			// Check parameters
			if ($type < 0) {
				return -1;
			}

			if ($date_start && $date_end && $date_start > $date_end) {
				$langs->load("errors");
				$this->error = $langs->trans('ErrorStartDateGreaterEnd');
				return -1;
			}

			$this->db->begin();

			$product_type = $type;
			if (!empty($fk_product) && $fk_product > 0) {
				$product = new Product($this->db);
				$result = $product->fetch($fk_product);
				$product_type = $product->type;

				if (getDolGlobalString('STOCK_MUST_BE_ENOUGH_FOR_ORDER') && $product_type == 0 && $product->stock_reel < $qty) {
					$langs->load("errors");
					$this->error = $langs->trans('ErrorStockIsNotEnoughToAddProductOnOrder', $product->ref);
					$this->errors[] = $this->error;
					dol_syslog(get_class($this)."::addline error=Product ".$product->ref.": ".$this->error, LOG_ERR);
					$this->db->rollback();
					return self::STOCK_NOT_ENOUGH_FOR_ORDER;
				}
			}
			// Calcul du total TTC et de la TVA pour la ligne a partir de
			// qty, pu, remise_percent et txtva
			// TRES IMPORTANT: C'est au moment de l'insertion ligne qu'on doit stocker
			// la part ht, tva et ttc, et ce au niveau de la ligne qui a son propre taux tva.

			$localtaxes_type = getLocalTaxesFromRate($txtva, 0, $this->thirdparty, $mysoc);

			// Clean vat code
			$reg = array();
			$vat_src_code = '';
			if (preg_match('/\((.*)\)/', $txtva, $reg)) {
				$vat_src_code = $reg[1];
				$txtva = preg_replace('/\s*\(.*\)/', '', $txtva); // Remove code into vatrate.
			}

			$tabprice = calcul_price_total($qty, $pu, $remise_percent, $txtva, $txlocaltax1, $txlocaltax2, 0, $price_base_type, $info_bits, $product_type, $mysoc, $localtaxes_type, 100, $this->multicurrency_tx, $pu_ht_devise);

			/*var_dump($txlocaltax1);
			 var_dump($txlocaltax2);
			 var_dump($localtaxes_type);
			 var_dump($tabprice);
			 var_dump($tabprice[9]);
			 var_dump($tabprice[10]);
			 exit;*/

			$total_ht  = $tabprice[0];
			$total_tva = $tabprice[1];
			$total_ttc = $tabprice[2];
			$total_localtax1 = $tabprice[9];
			$total_localtax2 = $tabprice[10];
			$pu_ht = $tabprice[3];

			// MultiCurrency
			$multicurrency_total_ht  = $tabprice[16];
			$multicurrency_total_tva = $tabprice[17];
			$multicurrency_total_ttc = $tabprice[18];
			$pu_ht_devise = $tabprice[19];

			// Rang to use
			$ranktouse = $rang;
			if ($ranktouse == -1) {
				$rangmax = $this->line_max($fk_parent_line);
				$ranktouse = $rangmax + 1;
			}

			// TODO A virer
			// Anciens indicateurs: $price, $remise (a ne plus utiliser)
			$price = $pu;
			$remise = 0;
			if ($remise_percent > 0) {
				$remise = round(($pu * $remise_percent / 100), 2);
				$price = $pu - $remise;
			}

			// Insert line
			$this->line = new OrderLine($this->db);

			$this->line->context = $this->context;

			$this->line->fk_commande = $this->id;
			$this->line->label = $label;
			$this->line->desc = $desc;
			$this->line->qty = $qty;
			$this->line->ref_ext = $ref_ext;

			$this->line->vat_src_code = $vat_src_code;
			$this->line->tva_tx = $txtva;
			$this->line->localtax1_tx = ($total_localtax1 ? $localtaxes_type[1] : 0);
			$this->line->localtax2_tx = ($total_localtax2 ? $localtaxes_type[3] : 0);
			$this->line->localtax1_type = empty($localtaxes_type[0]) ? '' : $localtaxes_type[0];
			$this->line->localtax2_type = empty($localtaxes_type[2]) ? '' : $localtaxes_type[2];
			$this->line->fk_product = $fk_product;
			$this->line->product_type = $product_type;
			$this->line->fk_remise_except = $fk_remise_except;
			$this->line->remise_percent = $remise_percent;
			$this->line->subprice = $pu_ht;
			$this->line->rang = $ranktouse;
			$this->line->info_bits = $info_bits;
			$this->line->total_ht = $total_ht;
			$this->line->total_tva = $total_tva;
			$this->line->total_localtax1 = $total_localtax1;
			$this->line->total_localtax2 = $total_localtax2;
			$this->line->total_ttc = $total_ttc;
			$this->line->special_code = $special_code;
			$this->line->origin = $origin;
			$this->line->origin_id = $origin_id;
			$this->line->fk_parent_line = $fk_parent_line;
			$this->line->fk_unit = $fk_unit;

			$this->line->date_start = $date_start;
			$this->line->date_end = $date_end;

			$this->line->fk_fournprice = $fk_fournprice;
			$this->line->pa_ht = $pa_ht;

			// Multicurrency
			$this->line->fk_multicurrency = $this->fk_multicurrency;
			$this->line->multicurrency_code = $this->multicurrency_code;
			$this->line->multicurrency_subprice		= $pu_ht_devise;
			$this->line->multicurrency_total_ht 	= $multicurrency_total_ht;
			$this->line->multicurrency_total_tva 	= $multicurrency_total_tva;
			$this->line->multicurrency_total_ttc 	= $multicurrency_total_ttc;

			// TODO Ne plus utiliser
			$this->line->price = $price;

			if (is_array($array_options) && count($array_options) > 0) {
				$this->line->array_options = $array_options;
			}

			$result = $this->line->insert($user);
			if ($result > 0) {
				// Reorder if child line
				if (!empty($fk_parent_line)) {
					$this->line_order(true, 'DESC');
				} elseif ($ranktouse > 0 && $ranktouse <= count($this->lines)) { // Update all rank of all other lines
					$linecount = count($this->lines);
					for ($ii = $ranktouse; $ii <= $linecount; $ii++) {
						$this->updateRangOfLine($this->lines[$ii - 1]->id, $ii + 1);
					}
				}

				// Mise a jour informations denormalisees au niveau de la commande meme
				if (empty($noupdateafterinsertline)) {
					$result = $this->update_price(1, 'auto', 0, $mysoc); // This method is designed to add line from user input so total calculation must be done using 'auto' mode.
				}

				if ($result > 0) {
					$this->db->commit();
					return $this->line->id;
				} else {
					$this->db->rollback();
					return -1;
				}
			} else {
				$this->error = $this->line->error;
				dol_syslog(get_class($this)."::addline error=".$this->error, LOG_ERR);
				$this->db->rollback();
				return -2;
			}
		// } else {
		// 	dol_syslog(get_class($this)."::addline status of order must be Draft to allow use of ->addline()", LOG_ERR);
		// 	return -3;
		// }
	}
}
