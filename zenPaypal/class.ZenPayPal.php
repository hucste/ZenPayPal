<?php
class ZenPayPal {

	private $custom_datas;
	private $options = array(); 	// array to list PayPal's options...
	private $price_list = array();	// array to get price list
	private $zenpaypal = array(); 	// array to set all informations
	private $zp_album;	// container for $_zp_current_album;
	private $zp_image;	// container for $_zp_current_image;

	public function __construct() {
		$this->setConstants();
		$this->setArrays();
		$this->getCustomDatas();
	}

	public function __destruct() {
	}

	/***
	*
	* Get Album Custom Data and Image Custom Datas
	* @return array
	*
	***/
	private function getCustomDatas() {
		$datas = array();

		if(!empty($this->zenpaypal['album']['customdata'])) {
			$datas['album'] = $this->zenpaypal['album']['customdata'];
		}
		if(!empty($this->zenpaypal['image']['customdata'])) {
			$datas['image'] = $this->zenpaypal['image']['customdata'];
		}

		if(!empty($datas) && is_array($datas)) {

			foreach($datas as $key => $value) {

				if(strpos($value, ' ') === true) str_replace(' ', "\r", $value);

				$array = explode("\r", $value);

				foreach($array as $val) {
					$val = trim($val);

					if(preg_match('|^zenpaypal:|', $val)) {

						$string = substr($val, 10);

						if(strcmp($string, 'nopaypal') == 0) {
							$this->custom_datas[$key]['nopaypal'] = 1;
						}
						else {
							$array2 = explode(':', $string);
							$this->custom_datas[$key][$array2[0]] = str_replace('_', ' ',$array2[1]);
						}
					}
				}
				unset($val,$string,$array,$array2);
			}
			unset($datas);
		}
	}

	public function getOptions() {
		return $this->options;
	}

	public function getPluginVersion() {
		return ZPP_Plugin_Version;
	}

	/**
	* Parses a price list element string and returns a pricelist array
	*
	* @param string $prices A text string of price list elements in the form
	*	<size>;<media>;<price>;<quantity>|<size>;<media>;<price>| ...
	* @return array
	*/
	private function getPriceList($prices) {
		$i = $ok = 0;

		if(!empty($prices) && preg_match('|;q|', $prices)) $ok = 1;

		$array = explode('|', trim($prices));

		if(!empty($array) && is_array($array)) {

			foreach($array as $value) {

				$x = explode(ZPP_separator_list, trim($value));

				$price_list['size'][$i] = $x[0];
				$price_list['media'][$i] = $x[1];
				$price_list['price'][$i] = $x[2];

				if(!empty($x[3])) $price_list['quantity'][$i] = substr($x[3], 1);
				elseif(!empty($ok)) $price_list['quantity'][$i] = getOption('zenPaypal_quantity_max');

				$i++;

				unset($x);

			}
			unset($value);

		}
		unset($array);

		$this->price_list = $price_list;
	}

    private function isSSL() {

        if ( isset($_SERVER['HTTPS']) ) {
            if ( 'on' == strtolower($_SERVER['HTTPS']) ) return true;

            if ( '1' == $_SERVER['HTTPS'] ) return true;
        }
        elseif ( isset($_SERVER['SERVER_PORT']) && ( '443' == $_SERVER['SERVER_PORT'] ) ) {
            return true;
        }
        return false;

    }

	public function printJS() {

		if(!empty($this->zenpaypal['image'])) {

			if(!empty($this->custom_datas['album']['nopaypal'])) $nopaypal = 1;
			if(!empty($this->custom_datas['image']['nopaypal'])) $nopaypal = 1;

			if(empty($nopaypal)) {

				if(!empty($this->custom_datas['image']['pricelist'])) {
					$this->getPriceList($this->custom_datas['image']['pricelist']);
				}
				elseif(!empty($this->custom_datas['album']['pricelist'])) {
					$this->getPriceList($this->custom_datas['album']['pricelist']);
				}
				else $this->getPriceList(getOption('zenPaypal_pricelist'));

				if( !empty($this->zenpaypal)
					&& !empty($this->price_list)
					&& is_array($this->price_list) )
				{
					$desc = explode('_', getOption('zenPaypal_item_desc'));

					$item_name = $html = $html2 = '';

					foreach($this->price_list['size'] as $key => $value) {
						$size = $value;
						$media = $this->price_list['media'][$key];
						$amount = $this->price_list['price'][$key];

						if(!empty($desc[0]) && !empty($this->zenpaypal['album']['title']) ) $item_name = $this->zenpaypal['text']['item']['album'].$this->zenpaypal['album']['title'];
						if(!empty($desc[1]) && !empty($this->zenpaypal['image']['title']) ) $item_name .= $this->zenpaypal['text']['item']['image'].$this->zenpaypal['image']['title'];

						if(!empty($desc[2])) {
							switch($desc[2]) {
								case 'Filename':
									if(!empty($this->zenpaypal['image']['filename'])) {
										$item_name .= $this->zenpaypal['text']['item']['filename'].$this->zenpaypal['image']['filename'];
									}
								break;
								case 'Media': $item_name .= $this->zenpaypal['text']['item']['media'].$media; break;
								case 'Size': $item_name .= $this->zenpaypal['text']['item']['size'].$size; break;
							}
						}

						if(!empty($desc[3])) {
							switch($desc[3]) {
								case 'Media': $item_name .= $this->zenpaypal['text']['item']['media'].$media; break;
								case 'Size': $item_name .= $this->zenpaypal['text']['item']['size'].$size; break;
							}
						}

						$html2 .= "\t".'if (form_PayPal.os0.value == "'.$size.' '.$media.'") {'."\n";
						$html2 .= "\t"."\t"."\t".'form_PayPal.amount.value = '.$amount.';'."\n";
						$html2 .= "\t"."\t"."\t".'form_PayPal.item_name.value = "'.$item_name.'";'."\n";
						$html2 .= "\t"."\t"."\t".'if($("#quantity :selected").length > 0) {'."\n";
						$html2 .= "\t"."\t"."\t"."\t".'form_Paypal.quantity.value = $("#quantity :selected").val();'."\n";
						$html2 .= "\t"."\t"."\t".'} '."\n";
						$html2 .= "\t"."\t".'}'."\n";
					}
					unset($size,$media,$amount,$item_name,$desc);

					if(isset($this->price_list['quantity'])) {
						$html .= 'os0_value = $(this).val();'."\n";
						$html .= '$("#quantity").append("<optgroup label=\"Quantity\">");'."\n";

						foreach($this->price_list['quantity'] as $key => $value) {
							$quantity = $value;
							$media = $this->price_list['media'][$key];
							$size = $this->price_list['size'][$key];

							$html .= 'if (os0_value == "'.$size.' '.$media.'") {'."\n";

							for($x = 1; $x <= $quantity; $x++) {
								$html .= '$("#quantity").append(new Option('.$x.','.$x.'));'."\n";
							}
							unset($x);

							$html .= '}'."\n";
						}
						unset($quantity,$media,$size);

						$html .= '$("#quantity").append("</optgroup>");'."\n";
					}

				$script_js = '<!-- ZenPayPal version: '.ZPP_Plugin_Version.' -->
				<script type="text/javascript">
				// <!-- <![CDATA[';

				if(!empty($html)) {
					$script_js .= '
					$(window).load(function() {

					jQuery.fn.changeQuantity = function() {
						$(this).emptyQuantity();
						if($("#os0").length > 0) {
							'.$html.'
						}
						else {
							return false;
						}
					}

					jQuery.fn.emptyQuantity = function () {
						$("#quantity").empty();
					}

					$("#os0").change( function() {
						$(this).changeQuantity();
					});

					$("#os0").click( function() {
						$(this).changeQuantity();
					});

					$("#quantity").mouseover( function() {
						$("#os0").changeQuantity();
					});

				});
				';
				}

				if(!empty($html2)) {
					$script_js .= '
					function zenPayPalOrder(form_PayPal) {
						'.$html2.'
					}
					';
				}
				$script_js .= '// ]]> -->
				</script>';

				if(!empty($script_js)) echo $script_js;
				unset($html,$html2,$script_js);
				}

			}
		}
	}

	public function printLinkCSS() {

		if(!empty($this->zenpaypal['image'])) {
			echo '<link rel="stylesheet" href="'.ZPP_PATH_CSS.'" type="text/css" />'."\n";
		}

	}

	public function printPayPalButton() {

		if(!empty($this->zenpaypal['image'])) {

			if(!empty($this->custom_datas['album']['nopaypal'])) $nopaypal = 1;
			if(!empty($this->custom_datas['image']['nopaypal'])) $nopaypal = 1;

			if(empty($nopaypal)) {

				// to obtain price list
				if(!empty($this->custom_datas['image']['pricelist'])) {
					$this->getPriceList($this->custom_datas['image']['pricelist']);
				}
				elseif(!empty($this->custom_datas['album']['pricelist'])) {
					$this->getPriceList($this->custom_datas['album']['pricelist']);
				}
				else $this->getPriceList($this->zenpaypal['option']['pricelist']);

				// to viewing price list
				if(isset($this->custom_datas['image']['viewing_pricelist'])) {
					$view_pricelist = $this->custom_datas['image']['viewing_pricelist'];
				}
				elseif(isset($this->custom_datas['album']['viewing_pricelist'])) {
				$view_pricelist = $this->custom_datas['album']['viewing_pricelist'];
				}
				else $view_pricelist = $this->zenpaypal['option']['viewing_pricelist'];

				if(!empty($this->price_list) && is_array($this->price_list)) {

					$i = 0;
					$sizes = $media = $price = $qties = array();

					foreach($this->price_list['size'] as $key => $value) {
						$sizes[$i] = $value;
						$media[$i] = $this->price_list['media'][$key];
						$price[$i] = $this->price_list['price'][$key];

						if(!empty($this->price_list['quantity'])) {
							$qties[$i] = $this->price_list['quantity'][$key];
						}
						else $qties[$i] = $this->zenpaypal['option']['quantity_max'];

						$i++;
					}

					// try to obtain PKCS7 Encrypted Information
					if(!empty($this->zenpaypal['option']['use_button_encrypted']) &&
						preg_match('/(-----BEGIN PKCS7-----)(.*)(-----END PKCS7-----)/', $this->zenpaypal['option']['encrypted_information'], $matches)
					) {
						$zpp['input_value_encrypted'] = $matches[0];
					}

					//  define protocol
					if(!empty($this->zenpaypal['option']['sandbox'])) {
						$PayPal['proto'] = 'https://www.sandbox.paypal.com/';
					}
					else $PayPal['proto'] = 'https://www.paypal.com/';

					$PayPal['action'] = $PayPal['proto'].'cgi-bin/webscr';

					switch($this->zenpaypal['option']['cmd']) {
						case '_cart' : $PayPal['button_name'] = 'cart'; break;
						case '_xclick' : $PayPal['button_name'] = 'buynow'; break;
					}

					// define source of PayPal's button and image
					$PayPal['btn_src'] = $PayPal['proto'].$this->zenpaypal['option']['locale'].'/i/btn/btn_'.$PayPal['button_name'].'_'.$this->zenpaypal['option']['button_size'].'.gif';
					$PayPal['img_src'] = $PayPal['proto'].$this->zenpaypal['option']['locale'].'/i/scr/pixel.gif';

					if(!empty($this->zenpaypal['option']['use_button_encrypted'])) {
						$PayPal['cmd'] = '_s-xclick';
					}
					else $PayPal['cmd'] = $this->zenpaypal['option']['cmd'];

                    if(!empty($this->zenpaypal['option']['text_paypal_title'])) $h2 = $this->zenpaypal['option']['text_paypal_title'];
                    if(is_null($h2)) $h2 = $this->zenpaypal['text']['PayPal']['h2'];

                    if(!empty($this->zenpaypal['option']['text_paypal_label_on0'])) $label_on0 = $this->zenpaypal['option']['text_paypal_label_on0'];
                    if(is_null($label_on0)) $label_on0 = $this->zenpaypal['text']['PayPal']['on0'];

                    if(!empty($this->zenpaypal['option']['text_paypal_label_os0'])) $label_os0 = $this->zenpaypal['option']['text_paypal_label_os0'];
                    if(is_null($label_os0)) $label_os0 = $this->zenpaypal['text']['PayPal']['os0'];

                    if(!empty($this->zenpaypal['option']['text_paypal_label_qty'])) $label_qty = $this->zenpaypal['option']['text_paypal_label_qty'];
                    if(is_null($label_qty)) $label_qty = $this->zenpaypal['text']['PayPal']['label_qty'];

                    if(!empty($this->zenpaypal['option']['text_paypal_img_alt'])) $img_alt = $this->zenpaypal['option']['text_paypal_img_alt'];
                    if(is_null($img_alt)) $img_alt = $this->zenpaypal['text']['PayPal']['img_alt'];

					// build html
					$html = '
					<div id="zenPaypal">
					<h2>'.$h2.'</h2>
					<form target="PayPal" action="'.$PayPal['action'].'" method="post" name="form_PayPal" id="form_PayPal">
					<input type="hidden" name="cmd" value="'.$PayPal['cmd'].'"/>';
                    unset($h2);

					if(!empty($zpp['input_value_encrypted'])) {
						$html .= "\n".'<input type="hidden" name="encrypted" value="'.$zpp['input_value_encrypted'].'" />';
					}

					$html .= '
					<input type="hidden" name="add" value="1"/>
					<input type="hidden" name="display" value="1"/>
					<input type="hidden" name="on0"	id="on0" value="Size"/>
					<p>
					<label for="os0">'.$label_on0.'</label>
					<select name="os0" id="os0">
						<optgroup label="'.$label_os0.'"/>'."\n";
                    unset($label_on0,$label_os0);

					foreach ($sizes as $key => $value) {
						$size = trim($value);
                        $format = $media[$key];
						$prix = trim($price[$key]);

						$option_value = $size.' '.$format;
						$option_text = $option_value.' ('.$prix.' '.$this->zenpaypal['option']['currency_symbol'].')';

						if(!empty($_POST['os0']) && $_POST['os0'] == $option_value) {
							$html .= '<option value="'.$option_value.'" selected="selected">'.$option_text."</option>\n";
						}
						else $html .= '<option value="'.$option_value.'">'.$option_text."</option>\n";

						unset($option_value, $option_text);
					}
					unset($key, $value);

					$html .= '</optgroup>
					</select>
					</p>
					<p>
					<label>'.$label_qty.'</label>
					<select name="quantity" id="quantity">'."\n";

					if(empty($price_list['quantity'])) {

						$html .= '<optgroup label="'.$label_qty.'"/>'."\n";

						for( $i = 1; $i <= $this->zenpaypal['option']['quantity_max']; $i++ ) {
							if(!empty($_POST['quantity']) && $_POST['quantity'] == $i) {
								$html .= '<option value="'.$i.'" selected="selected">'.$i.'</option>'."\n";
							}
							else $html .= '<option value="'.$i.'">'.$i.'</option>'."\n";
						}

						$html .= '</optgroup>'."\n";
					}
                    unset($label_qty);

					$html .= '
					</select>
					</p>
					<input type="hidden" name="item_name" />
					<input type="hidden" name="amount" />'."\n";

					if(!empty($this->zenpaypal['option']['business']))	$zenPaypal['business'] = $this->zenpaypal['option']['business'];

					$zenPaypal['cancel_return'] = $this->zenpaypal['website'].htmlspecialchars(@getImageURL());
					if(!empty($this->zenpaypal['option']['cbt']))	$zenPaypal['cbt'] = $this->zenpaypal['option']['cbt'];
					if(!empty($this->zenpaypal['option']['charset']))	$zenPaypal['charset'] = $this->zenpaypal['option']['charset'];
					if(!empty($this->zenpaypal['option']['cpp_header_image']))	$zenPaypal['cpp_header_image'] = $this->zenpaypal['website'].'/'.$this->zenpaypal['option']['cpp_header_image'];
					if(!empty($this->zenpaypal['option']['cpp_headerback_color']))	$zenPaypal['cpp_headerback_color'] = $this->zenpaypal['option']['cpp_headerback_color'];
					if(!empty($this->zenpaypal['option']['cpp_headerborder_color']))	$zenPaypal['cpp_headerborder_color'] = $this->zenpaypal['option']['cpp_headerborder_color'];
					if(!empty($this->zenpaypal['option']['cpp_payflow_color']))	$zenPaypal['cpp_payflow_color'] = $this->zenpaypal['option']['cpp_payflow_color'];
					if(!empty($this->zenpaypal['option']['currency_code']))	$zenPaypal['currency_code'] = $this->zenpaypal['option']['currency_code'];
					if(!empty($this->zenpaypal['option']['cs']))	$zenPaypal['cs'] = $this->zenpaypal['option']['cs'];

					if(!empty($this->zenpaypal['option']['image_url'])) $zenPaypal['image_url'] = $this->zenpaypal['website'].'/'.$this->zenpaypal['option']['image_url'];

					if(!empty($this->zenpaypal['option']['lc']))	$zenPaypal['lc'] = $this->zenpaypal['option']['lc'];

					if(!empty($this->zenpaypal['option']['no_note']))	$zenPaypal['no_note'] = $this->zenpaypal['option']['no_note'];
					if(!empty($this->zenpaypal['option']['no_shipping']))	$zenPaypal['no_shipping'] = $this->zenpaypal['option']['no_shipping'];

					if(!empty($this->zenpaypal['option']['page_style']))	{
						$zenPaypal['page_style'] = $this->zenpaypal['option']['page_style'];

						switch($zenPaypal['page_style']) {
							case 'custom': $info['page_style'] = $this->zenpaypal['option']['page_style_name']; break;
							case 'primary': break;
							case 'paypal': unset($zenPaypal['page_style']); break;
						}
					}
					$zenPaypal['return'] = $this->zenpaypal['website'].htmlspecialchars(@getAlbumURL());
					//$zenPaypal['rm'] = $this->zenpaypal['option']['rm'];

					if(isset($this->custom_datas['image']['shipping'])) {
						$zenPaypal['shipping'] = $this->custom_datas['image']['shipping'];
					}
					elseif(isset($this->custom_datas['album']['shipping'])) {
						$zenPaypal['shipping'] = $this->custom_datas['album']['shipping'];
					}
					else {
						if(!empty($this->zenpaypal['option']['shipping']))	$zenPaypal['shipping'] = $this->zenpaypal['option']['shipping'];
					}

					if(isset($this->custom_datas['image']['tax'])) {
						$zenPaypal['tax'] = $this->custom_datas['image']['tax'];
					}
					elseif(isset($this->custom_datas['album']['tax'])) {
						$zenPaypal['tax'] = $this->custom_datas['album']['tax'];
					}
					else {
						if(!empty($this->zenpaypal['option']['tax']))	$zenPaypal['tax'] = $this->zenpaypal['option']['tax'];
					}

					if(!empty($this->zenpaypal['option']['use_tax_rate']))	$zpp['tax_rate'] = $this->zenpaypal['option']['use_tax_rate'];

					if(!empty($zenPaypal)) {
						foreach($zenPaypal as $key => $value) {
							if($key != 'debug') { 	// manage tax, tax_rate, tax_cart !!!
								if(!empty($zpp['tax_rate']) && $key == 'tax') {
									$html .= "\t\t".'<input type="hidden" name="tax_rate" value="'.$value.'"/>'."\n";
								}
								else $html .= "\t\t".'<input type="hidden" name="'.$key.'" value="'.$value.'"/>'."\n";
							}
						}
						unset($key, $value);
					}

					$html .= '<br />
					<input type="image" border="0" name="submit" class="btn_zenPaypal"
						src="'.$PayPal['btn_src'].'"
						onClick="zenPayPalOrder(this.form)"
						alt="'.$img_alt.'"/>
					<img alt="'.$img_alt.'" border="0" width="1" height="1" src="'.$PayPal['img_src'].'"/>
					</form>'."\n";
                    unset($img_alt);

					// if viewing price list : yes.
					if(!empty($view_pricelist)) $html .= $this->printPriceList();

					$html .= '
					</div>'."\n";
				}

				if(!empty($html)) echo $html;
				unset($html);
			}
		}
	}

	public function printPayPalLogo() {

		$this->setPayPalLogo();

        if(!empty($this->zenpaypal['option']['text_logo_title'])) $h3 = $this->zenpaypal['option']['text_logo_title'];
        if(is_null($h3)) $h3 = $this->zenpaypal['text']['PayPal_Logo']['h3'];

        if(!empty($this->zenpaypal['option']['text_logo_img_alt'])) $img_alt = $this->zenpaypal['option']['text_logo_img_alt'];
        if(is_null($img_alt)) $img_alt = $this->zenpaypal['text']['PayPal_Logo']['img_alt'];

		$html = '<div class="menu">';
		$html .= '<h3>'.$h3.'</h3>';
		$html .= '<!-- PayPal Logo -->';
		$html .= '<p style="text-align:center;">';
		$html .= '<a href="#" onclick="'.$this->zenpaypal['PayPal_Logo']['js_code'].'">';
		$html .= '<img src="'.$this->zenpaypal['PayPal_Logo']['img_src'].'" border="0" alt="'.$img_alt.'">';
		$html .= '</a></p><!-- PayPal Logo -->';
		$html .= '</div>';
        unset($h3,$img_alt);

		echo $html;

		unset($html);
	}

	/***
	*
	* Prints a link that will expose the zenPaypal Price list table
	*
	***/
	private function printPriceList() {

		if(!$this->price_list) {
			$this->getPriceList($this->zenpaypal['option']['pricelist']);
		}

		$text = $this->zenpaypal['option']['pricelist_html_text'];
		if(is_null($text)) $text = gettext('Price List');

		$data_id = $this->zenpaypal['option']['pricelist_html_id'].'_data';

		$HTML_Tag = $this->zenpaypal['option']['pricelist_html_tag'];
		if(!empty($HTML_Tag)) {
			$HTML_Tag_Start = '<'.$HTML_Tag.'>';
			$HTML_Tag_End = '</'.$HTML_Tag.'>';
		}

        if(!empty($this->zenpaypal['option']['pricelist_table_caption'])) $caption = $this->zenpaypal['option']['pricelist_table_caption'];
        if(is_null($caption)) $caption = $this->zenpaypal['text']['Price_List']['caption'];

        if(!empty($this->zenpaypal['option']['pricelist_thead_media'])) $th_media = $this->zenpaypal['option']['pricelist_thead_media'];
        if(is_null($th_media)) $th_media = $this->zenpaypal['text']['Price_List']['media'];

        if(!empty($this->zenpaypal['option']['pricelist_thead_price'])) $th_price = $this->zenpaypal['option']['pricelist_thead_price'];
        if(is_null($th_price)) $th_price = $this->zenpaypal['text']['Price_List']['price'];

        if(!empty($this->zenpaypal['option']['pricelist_thead_qty'])) $th_qty = $this->zenpaypal['option']['pricelist_thead_qty'];
        if(is_null($th_qty)) $th_qty = $this->zenpaypal['text']['Price_List']['quantity'];

        if(!empty($this->zenpaypal['option']['pricelist_thead_size'])) $th_size = $this->zenpaypal['option']['pricelist_thead_size'];
        if(is_null($th_size)) $th_size = $this->zenpaypal['text']['Price_List']['size'];


		$html = $HTML_Tag_Start.'<a href="javascript: toggle('."'".$data_id."'".');">'.$text."</a>".$HTML_Tag_End."\n";
		$html .=  '<div id="'.$data_id.'" style="display: none;">'."\n";
		$html .= '<table>'."\n";
		$html .= '<caption>'.$caption.'</caption>'."\n";
		$html .= '<thead>'."\n";
		$html .= '<tr>'."\n";
		$html .= '<th>'.$th_size.'</th>'."\n";
		$html .= '<th>'.$th_media.'</th>'."\n";
		$html .= '<th>'.$th_price.'</th>'."\n";
		$html .= '<th>'.$th_qty.'</th>'."\n";
		$html .= '</tr>'."\n";
		$html .= '</thead>'."\n";
		$html .= '<tbody>'."\n";
        unset($text,$data_id,$HTML_Tag,$caption,$th_media,$th_price,$th_qty,$th_size);

		foreach($this->price_list['size'] as $key => $value) {
			$size = str_replace(' ', '&nbsp;', $value);
			$media = str_replace(' ', '&nbsp;', $this->price_list['media'][$key]);
			$price = $this->price_list['price'][$key].'&nbsp;'.$this->zenpaypal['option']['currency_symbol'];
			if(!empty($this->price_list['quantity'])) {
				$qty = $this->price_list['quantity'][$key];
			}
			else $qty = $this->zenpaypal['option']['quantity_max'];

			$html .= '<tr>'."\n";
			$html .= '<td class="size">'.$size.'</td>'."\n";
			$html .= '<td class="media">'.$media.'</td>'."\n";
			$html .= '<td class="price">'.$price.'</td>'."\n";
			if(!empty($qty)) $html .= '<td class="qty">'.$qty.'</td>'."\n";
			$html .= '</tr>'."\n";
		}
		unset($size,$media,$price,$qty,$key,$value);

		$html .= '</tbody>'."\n";
		$html .= '</table>'."\n";
		$html .= '</div>'."\n";

		return $html;
        unset($html);
	}

	private function setArrays() {

		$this->setArrayOptions();

		$this->setArrayZenPayPal();

	}

	private function setArrayOptions() {
		require_once('array.options.php');
	}

	private function setArrayZenPayPal() {
		global $_zp_current_album, $_zp_current_image;

		// build indice album
		if($_zp_current_album) {
			$zpp['album']['customdata'] = $_zp_current_album->getCustomData();
			$zpp['album']['title'] = $_zp_current_album->getTitle();
		}

		// build indice image
		if($_zp_current_image) {
			$zpp['image']['customdata'] = $_zp_current_image->getCustomData();
			$zpp['image']['filename'] = $_zp_current_image->getFileName();
			$zpp['image']['title'] = $_zp_current_image->getTitle();
		}

		// build indice option
		foreach($this->options['key'] as $value) {
			$zpp['option'][$value] = getOption('zenPaypal_'.$value);
		}

        if(!empty($zpp['option']['currency_code'])) $zpp['option']['currency_symbol'] = $this->setCurrencySymbol($zpp['option']['currency_code']);
		$zpp['option']['locale'] = getOption('locale');
		if(empty($zpp['option']['locale'])) $zpp['option']['locale'] = 'en_US';
		$zpp['option']['short_locale'] = substr($zpp['option']['locale'], 0, 2);
		$zpp['option']['lc'] = substr($zpp['option']['locale'], -2);

		// build indice text to item in JS.
		$zpp['text']['item'] = array (
			'album' => gettext('Album: '),
			'image' => gettext('; Image: '),
			'filename' => gettext('; File: '),
			'media' => gettext('; Media: '),
			'size' => gettext('; Size: '),
		);

		// build indice text of PayPal's text
		$zpp['text']['PayPal'] = array (
			'h2' => gettext('Buy with PayPal'),
			'img_alt' => gettext('Buy with PayPal: a solution fast, free and secure!'),
			'label_qty' => gettext('Quantity'),
			'on0' => gettext('Choose size, media.'),
			'os0' => gettext('Size'),
		);

		// build indice text of PayPal Logo
		$zpp['text']['PayPal_Logo'] = array (
			'h3' => gettext('This gallery use PayPal'),
			'img_alt' => gettext('PayPal&apos;s Logo'),
		);

		// build indice text of Price List
		$zpp['text']['Price_List'] = array (
			'caption' => gettext('Informations object&apos;s prices'),
			'media' => gettext('media'),
			'price' => gettext('price'),
			'quantity' => gettext('quantity'),
			'size' => gettext('size'),
		);

        $zpp['url'] = parse_url($this->setScheme().'://'.$_SERVER['SERVER_NAME']);
        $zpp['website'] = $zpp['url']['scheme'].'://'.$zpp['url']['host'];

		$this->zenpaypal = $zpp;
	}

	private function setConstants() {

		define('ZPP_Plugin_Version', trim(file_get_contents(dirname(__FILE__).'/version')));

		define('ZPP_PATH_CSS', FULLWEBPATH.'/'.USER_PLUGIN_FOLDER.'/zenPaypal/zenPaypal.css');

		define('ZPP_separator_list',';');
		define('ZPP_pricelist_default','4x6'.ZPP_separator_list.'Paper Matte Thin'.ZPP_separator_list.'5.75|8x10'.ZPP_separator_list.'Xtrem Glossy'.ZPP_separator_list.'20.00|11x14'.ZPP_separator_list.'Paper'.ZPP_separator_list.'15.35');

	}

    private function setCurrencySymbol($code) {

        $currency = array(

            'code' => array (
			'AUD',
			'BRL',
			'CAD',
			'CHF',
			'CZK',
			'DKK',
			'EUR',
			'GBP',
			'HKD',
			'HUF',
			'ILS',
			'JPY',
			'MYR',
			'MXN',
			'NOK',
			'NZD',
			'PHP',
			'PLN',
			'SGD',
			'SEK',
			'THB',
			'TWD',
			'USD',
            ),

            'symbol' => array (
			'$A',
			'R$',
			'$CA',
			'CHF',
			'Kč',
			'DKK',
			'€',
			'£',
			'$HK',
			'HUF',
			'₪',
			'¥',
			'MYR',
			'MXN',
			'NOK',
			'NZ$',
			'₱',
			'PLN',
			'S$',
			'SEK',
			'฿',
			'NT$',
			'$',
            ),

        );

        $key = array_search($code, $currency['code']);
        return $currency['symbol'][$key];
        unset($key);

    }

    private function setScheme() {

        if( $this->isSSL() ) return 'https';
        else return 'http';

    }

	private function setPayPalLogo() {

		$this->zenpaypal['PayPal_Logo']['URL'] = 'https://www.paypal.com/'.$this->zenpaypal['option']['short_locale'].'/cgi-bin/webscr?cmd=xpt/Marketing/popup/OLCWhatIsPayPal-outside';
		$this->zenpaypal['PayPal_Logo']['js_code'] = "javascript:window.open('".$this->zenpaypal['PayPal_Logo']['URL']."','olcwhatispaypal','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=400, height=350');";
		$this->zenpaypal['PayPal_Logo']['img_src'] = 'https://www.paypal.com/'.$this->zenpaypal['option']['locale'].'/i/logo/PayPal_mark_60x38.gif';

	}

}
?>
