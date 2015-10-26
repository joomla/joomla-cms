<?php
/**
 * @package	HikaShop for Joomla!
 * @version	2.6.0
 * @author	hikashop.com
 * @copyright	(C) 2010-2015 HIKARI SOFTWARE. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */
defined('_JEXEC') or die('Restricted access');
?><?php
class plgHikashoppaymentCardSave extends hikashopPaymentPlugin
{
	var $multiple = true;
	var $name = 'cardsave';

	var $accepted_currencies = array(
		'GBP','USD','EUR','AUD','CAD'
	);
	var $sync_currencies = array(
		'GBP' => '826',
		'USD' => '840',
		'EUR' => '978',
		'AUD' => '036',
		'CAD' => '124'
	);

	var $country_codes = array(
		"GBR"=>"826","USA"=>"840","AUS"=>"036","CAN"=>"124","FRA"=>"250","DEU"=>"276",
		"AFG"=>"004","ALA"=>"248","ALB"=>"008","DZA"=>"012","ASM"=>"016","AND"=>"020","AGO"=>"024","AIA"=>"660","ATA"=>"010","ATG"=>"028","ARG"=>"032","ARM"=>"051","ABW"=>"533","AUT"=>"040","AZE"=>"031",
		"BHS"=>"044","BHR"=>"048","BGD"=>"050","BRB"=>"052","BLR"=>"112","BEL"=>"056","BLZ"=>"084","BEN"=>"204","BMU"=>"060","BTN"=>"064","BOL"=>"068","BIH"=>"070","BWA"=>"072","BVT"=>"074","BRA"=>"076",
		"IOT"=>"086","BRN"=>"096","BGR"=>"100","BFA"=>"854","BDI"=>"108","KHM"=>"116","CMR"=>"120","CPV"=>"132","CYM"=>"136","CAF"=>"140","TCD"=>"148","CHL"=>"152","CHN"=>"156","CXR"=>"162","CCK"=>"166",
		"COL"=>"170","COM"=>"174","COD"=>"180","COG"=>"178","COK"=>"184","CRI"=>"188","CIV"=>"384","HRV"=>"191","CUB"=>"192","CYP"=>"196","CZE"=>"203","DNK"=>"208","DJI"=>"262","DMA"=>"212","DOM"=>"214",
		"TMP"=>"626","ECU"=>"218","EGY"=>"818","SLV"=>"222","GNQ"=>"226","ERI"=>"232","EST"=>"233","ETH"=>"231","FLK"=>"238","FRO"=>"234","FJI"=>"242","FIN"=>"246","GUF"=>"254","PYF"=>"258","ATF"=>"260",
		"GAB"=>"266","GMB"=>"270","GEO"=>"268","GHA"=>"288","GIB"=>"292","GRC"=>"300","GRL"=>"304","GRD"=>"308","GLP"=>"312","GUM"=>"316","GTM"=>"320","GGY"=>"831","GIN"=>"324","GNB"=>"624","GUY"=>"328",
		"HTI"=>"332","HMD"=>"334","HND"=>"340","HKG"=>"344","HUN"=>"348","ISL"=>"352","IND"=>"356","IDN"=>"360","IRN"=>"364","IRQ"=>"368","IRL"=>"372","IMN"=>"833","ISR"=>"376","ITA"=>"380","JAM"=>"388",
		"JPN"=>"392","JEY"=>"832","JOR"=>"400","KAZ"=>"398","KEN"=>"404","KIR"=>"296","KOR"=>"410","PRK"=>"408","KWT"=>"414","KGZ"=>"417","LAO"=>"418","LVA"=>"428","LBN"=>"422","LSO"=>"426","LBR"=>"430",
		"LBY"=>"434","LIE"=>"438","LTU"=>"440","LUX"=>"442","MAC"=>"446","MKD"=>"807","MDG"=>"450","MWI"=>"454","MYS"=>"458","MDV"=>"462","MLI"=>"466","MLT"=>"470","MHL"=>"584","MTQ"=>"474","MRT"=>"478",
		"MUS"=>"480","MYT"=>"175","MEX"=>"484","FSM"=>"583","MDA"=>"498","MCO"=>"492","MNG"=>"496","MNE"=>"499","MSR"=>"500","MAR"=>"504","MOZ"=>"508","MMR"=>"104","NAM"=>"516","NRU"=>"520","NPL"=>"524",
		"NLD"=>"528","ANT"=>"530","NCL"=>"540","NZL"=>"554","NIC"=>"558","NER"=>"562","NGA"=>"566","NIU"=>"570","NFK"=>"574","MNP"=>"580","NOR"=>"578","OMN"=>"512","PAK"=>"586","PLW"=>"585","PSE"=>"275",
		"PAN"=>"591","PNG"=>"598","PRY"=>"600","PER"=>"604","PHL"=>"608","PCN"=>"612","POL"=>"616","PRT"=>"620","PRI"=>"630","QAT"=>"634","REU"=>"638","ROM"=>"642","RUS"=>"643","RWA"=>"646","BLM"=>"652",
		"SHN"=>"654","KNA"=>"659","LCA"=>"662","MAF"=>"663","SPM"=>"666","VCT"=>"670","WSM"=>"882","SMR"=>"674","STP"=>"678","SAU"=>"682","SEN"=>"686","SRB"=>"688","SYC"=>"690","SLE"=>"694","SGP"=>"702",
		"SVK"=>"703","SVN"=>"705","SLB"=>"090","SOM"=>"706","ZAF"=>"710","SGS"=>"239","ESP"=>"724","LKA"=>"144","SDN"=>"736","SUR"=>"740","SJM"=>"744","SWZ"=>"748","SWE"=>"752","CHE"=>"756","SYR"=>"760",
		"TWN"=>"158","TJK"=>"762","TZA"=>"834","THA"=>"764","TGO"=>"768","TKL"=>"772","TON"=>"776","TTO"=>"780","TUN"=>"788","TUR"=>"792","TKM"=>"795","TCA"=>"796","TUV"=>"798","UGA"=>"800","UKR"=>"804",
		"ARE"=>"784","UMI"=>"581","URY"=>"858","UZB"=>"860","VUT"=>"548","VAT"=>"336","VEN"=>"862","VNM"=>"704","VGB"=>"092","VIR"=>"850","WLF"=>"876","ESH"=>"732","YEM"=>"887","ZMB"=>"894","ZWE"=>"716"
	);

	var $errorResultText = array(
		00 => 'transaction successful',
		02 => 'card referred',
		05 => 'card declined',
		20 => 'duplicate transaction',
		30 => 'exception',
		99 => 'unknown error'
	);

	function needCC(&$method) {
		if( @$method->payment_params->api == 'direct' || @$method->payment_params->api == 'transparent' ) {
			$method->ask_cc = true;
			$method->ask_owner = true;

			if( $method->payment_params->ask_ccv ) {
				$method->ask_ccv = true;
			}
			return true;
		}
		return false;
	}

	function onBeforeOrderCreate(&$order,&$do) {
		if(parent::onBeforeOrderCreate($order, $do) === true)
			return true;

		if(@$this->payment_params->api != 'direct') {
			return true;
		}
		if(!function_exists('curl_init')){
			$this->app->enqueueMessage('The CardSave payment plugin needs the CURL library installed but it seems that it is not available on your server. Please contact your web hosting to set it up.','error');
			return false;
		}

		$this->ccLoad();

		$address1 = ''; $address2 = ''; $address3 = ''; $address4 = '';

		if(!empty($order->cart->billing_address->address_street)) {
			$address1 = $address2 = $address3 = $address4 = '';
			if(!empty($order->cart->billing_address->address_street2)){
				$address2 = substr($order->cart->billing_address->address_street2,0,100);
			}
			if(strlen($order->cart->billing_address->address_street)>100) {
				$address1 = substr($order->cart->billing_address->address_street,0,100);
				if(empty($address2)) $address2 = @substr($order->cart->billing_address->address_street,100,50);
				if(empty($address3)) $address3 = @substr($order->cart->billing_address->address_street,150,50);
				if(empty($address4)) $address4 = @substr($order->cart->billing_address->address_street,200,50);
			}else{
				$address1 = $order->cart->billing_address->address_street;
			}
		}
		$country_code_2 = @$order->cart->billing_address->address_country->zone_code_3;

		if( isset($order->order_id) )
			$uuid = $order->order_id;
		else
			$uuid = uniqid('');

		$gwId = 1;
		$cpt = 0;
		$domain = $this->payment_params->gw_entrypoint;
		$port =  (int)$this->payment_params->gw_port;

		if( $port == 443 || $port == 0 ) {
			$port = '';
		} else {
			$port = ':' . $port;
		}

		$amount = (int)round($order->cart->full_total->prices[0]->price_value_with_tax * 100);
		$currencyCode = (int)$this->sync_currencies[ $this->currency->currency_code ];

		$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
		$xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
		$xml .= '<soap:Body><CardDetailsTransaction xmlns="https://www.thepaymentgateway.net/"><PaymentMessage>';
		$xml .= '<MerchantAuthentication MerchantID="'.$this->payment_params->merchantid.'" Password="'.$this->payment_params->password.'" />';
		$xml .= '<TransactionDetails Amount="'.$amount.'" CurrencyCode="'.$currencyCode.'">';
		$xml .= '<MessageDetails TransactionType="'.($this->payment_params->instant_capture?'SALE':'PREAUTH').'" />';
		$xml .= '<OrderID>'.$uuid.'</OrderID>';
		$xml .= '</TransactionDetails><CardDetails><CardName>'.$this->cc_owner.'</CardName><CardNumber>'.$this->cc_number.'</CardNumber>';
		$xml .= '<ExpiryDate Month="'.$this->cc_month.'" Year="'.$this->cc_year.'"/>';
		if( $this->payment_params->ask_ccv ) {
			$xml .= '<CV2>'.$this->cc_CCV.'</CV2>';
		}
		$xml .= '</CardDetails><CustomerDetails><BillingAddress><Address1>'.$address1.'</Address1>';
		if( !empty($adress2) ) $xml .= '<Address2>'.$address2.'</Address2>';
		if( !empty($adress3) ) $xml .= '<Address3>'.$address3.'</Address3>';
		if( !empty($adress4) ) $xml .= '<Address4>'.$address4.'</Address4>';
		$xml .= '<City>'.substr(@$order->cart->billing_address->address_city, 0, 50).'</City><State>'.substr(@$order->cart->billing_address->address_state->zone_name, 0, 50).'</State>';
		$xml .= '<PostCode>'.substr(@$order->cart->billing_address->address_post_code, 0, 50).'</PostCode><CountryCode>'.$this->country_codes[$country_code_2].'</CountryCode>';
		$xml .= '</BillingAddress><EmailAddress>'.substr($this->user->user_email, 0, 100).'</EmailAddress></CustomerDetails>';
		$xml .= '</PaymentMessage></CardDetailsTransaction></soap:Body></soap:Envelope>';

		$session = null;

		do {
			$soapSuccess = false;
			$url = 'https://gw'.$gwId.'.'.$domain.$port.'/';

			$session = curl_init();

			if( $session === false ) {
				$do = false;
				break;
			}

			$headers = array(
				'SOAPAction:https://www.thepaymentgateway.net/CardDetailsTransaction',
				'Content-Type: text/xml; charset = utf-8',
				'Connection: close'
			);

			curl_setopt($session, CURLOPT_HEADER, false);
			curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($session, CURLOPT_POST, true);
			curl_setopt($session, CURLOPT_URL, $url);
			curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
			curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

			$ret = curl_exec($session);
			$err = curl_errno($session);
			$retHead = curl_getinfo($session);

			curl_close($session);
			$session = null;

			$history = new stdClass();
			$email = new stdClass();

			if( $err == 0 ) {
				$status = null;
				$soapStatus = null;

				if( preg_match('#<StatusCode>([0-9]+)</StatusCode>#iU', $ret, $soapStatus) ) {
					$status = (int)$soapStatus[1];
					$auth = null;
					$crossref = null;
					if( preg_match('#<AuthCode>([a-zA-Z0-9]+)</AuthCode>#iU', $ret, $auth) ) {
						$auth = $auth[1];
					}
					if( preg_match('#<TransactionOutputData.*CrossReference="([a-zA-Z0-9]+)".*>#iU', $ret, $crossref) ) {
						$crossref = $crossref[1];
					}

					if( $status == 0 && $soapStatus[1] != '0' ) {
						$status = 50;
					}

					if( $status != 50 ) {
						$soapSuccess = true;
						switch( $status ) {
							case 0:
								$history->amount = $order->cart->full_total->prices[0]->price_value_with_tax . $this->currency->currency_code;
								$history->data = 'UUID: ' . $uuid . "\n" . 'CrossReference: ' . $crossref . "\n" . ob_get_clean();

								$order_status = $this->payment_params->verified_status;
								$history->notified = 1;
								$payment_status = 'confirmed';

								$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
								$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE','',HIKASHOP_LIVE);
								$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

								$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','CardSave','Accepted');
								$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

								$this->modifyOrder($order,$order_status,$history,$email);

								break;

							case 3:
								if( preg_match('#<ThreeDSecureOutputData>.*<PaREQ>(.+)</PaREQ>.*<ACSURL>(.+)</ACSURL>.*</ThreeDSecureOutputData>#iU', $ret, $soap3DSec) ) {
									$PaREQ = $soap3DSec[1];
									$ACSurl = $soap3DSec[2];
								} else {
									$this->app->enqueueMessage('Incorrect 3DSecure data.');
									$do = false;
									break;
								}

								$data = array(
									'UUID' => $uuid,
									'XREF' => $crossref
								);
								$history->notified = 0;
								$history->amount = $order->cart->full_total->prices[0]->price_value_with_tax . $this->currency->currency_code;
								$history->data = serialize($data);
								$history->type = '3dsecure';

								$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_ref', $crossref);
								$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_url', $ACSurl);
								$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_req', $PaREQ);

								$this->modifyOrder($order,null,$history,false);

								break;

							case 5:
								$this->app->enqueueMessage('Transaction declined.');
								$do = false;
								break;

							case 20:
								if( preg_match('#<PreviousTransactionResult>.*<StatusCode>([0-9]+)</StatusCode>#iU', $ret, $soapStatus2) ) {
									if( $soapStatus2[1] == '0' ) {
										$this->app->enqueueMessage('Transaction already validate.');
									} else if( preg_match('#<Message>(.*)</Message>.*</PreviousTransactionResult>#iU', $ret, $msg) ) {
										$this->app->enqueueMessage($msg[1]);
										$do = false;
									} else {
										$this->app->enqueueMessage('Duplicate transaction');
										$do = false;
									}
								} else {
									$this->app->enqueueMessage('Duplicate transaction.');
									$do = false;
								}
								break;

							case 30:
							default:
								if( preg_match('#<Message>(.*)</Message>#iU', $ret, $msg) ) {
									$msg = $msg[1];
								} else {
									$msg = '';
								}
								$this->app->enqueueMessage('CardSave Error ('.$status.') :' . $msg);
								$do = false;
								break;
						}
					}
				}
			}


			if( $session ) {
				curl_close($session);
			}

			if( !$soapSuccess ) {
				$cpt++;
				if( $cpt >= 2 ) {
					$cpt = 0;
					$gwId++;
					if( $gwId > 3 ) {
						$this->app->enqueueMessage('Impossible to contact the CardSave payment gateway.');
						$do = false;
						$soapSuccess = true;
						break;
					}
				}
			}

		} while( !$soapSuccess && $gwId < 4 && $cpt < 3 );

		$this->ccClear();

		return true;
	}

	function onAfterOrderConfirm(&$order,&$methods,$method_id) {
		parent::onAfterOrderConfirm($order, $methods, $method_id);
		if( $this->payment_params->api == 'hosted' || $this->payment_params->api == 'transparent' ) {

			$viewType='end';

			$httpsHikashop = HIKASHOP_LIVE;
			if( $this->payment_params->api != 'hosted' || $this->payment_params->hosted_mode == 'POST' ) {
				$httpsHikashop = str_replace('http://','https://', HIKASHOP_LIVE);
			}
			if( $this->payment_params->debug ) {
				$httpsHikashop = str_replace('https://','http://', HIKASHOP_LIVE);
			}

			$server_url = $httpsHikashop.'index.php';
			$notify_url_p = 'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cardsave&tmpl=component&lang='.$this->locale;
			$return_url_p = 'option=com_hikashop&ctrl=checkout&task=notify&notif_payment=cardsave&tmpl=component&cardsave_return=1&lang='.$this->locale;

			$country_code_2 = @$order->cart->billing_address->address_country->zone_code_3;

			$customerName = trim(@$order->cart->billing_address->address_firstname . ' ' . @$order->cart->billing_address->address_lastname);

			$address1 = ''; $address2 = ''; $address3 = ''; $address4 = '';
			if(!empty($order->cart->billing_address->address_street)) {
				if(strlen($order->cart->billing_address->address_street)>100) {
					$address1 = substr($order->cart->billing_address->address_street,0,100);
					$address2 = @substr($order->cart->billing_address->address_street,100,50);
					$address3 = @substr($order->cart->billing_address->address_street,150,50);
					$address4 = @substr($order->cart->billing_address->address_street,200,50);
				}else{
					$address1 = $order->cart->billing_address->address_street;
				}
			}

			if( $this->payment_params->api == 'transparent' ) {

				$this->ccLoad();

				$vars = array(
					"MerchantID" => $this->payment_params->merchantid,
					"Password" => $this->payment_params->password,
					"Amount" => round($order->cart->full_total->prices[0]->price_value_with_tax * 100),
					"CurrencyCode" => $this->sync_currencies[ $this->currency->currency_code ],
					"OrderID" => $order->order_id,
					"TransactionType" => $this->payment_params->instant_capture?'SALE':'PREAUTH',
					"TransactionDateTime" => date('Y-m-d H:i:s O'),
					"CallbackURL" => $server_url . '?' . $return_url_p,
					"OrderDescription" => $order->order_number,
				);

				$vars['HashDigest'] = $this->cardSaveHash($this->payment_params->hash_method, $vars, $this->payment_params->sharedkey);
				unset( $vars['Password'] );

				$vars2 = array(
					"CustomerName" => substr($customerName, 0, 100),
					"Address1" => $address1,
					"Address2" => $address2,
					"Address3" => $address3,
					"Address4" => $address4,
					"City" => substr(@$order->cart->billing_address->address_city, 0, 50),
					"State" => substr(@$order->cart->billing_address->address_state->zone_name, 0, 50),
					"PostCode" => substr(@$order->cart->billing_address->address_post_code, 0, 50),
					"CountryCode" => @$this->country_codes[$country_code_2],

					"CardName" => $this->cc_owner,
					"CardNumber" => $this->cc_number,
					"ExpiryDateMonth" => $this->cc_month,
					"ExpiryDateYear" => $this->cc_year
				);

				if( $this->payment_params->ask_ccv ) {
					$vars2["CV2"] = $this->cc_CCV;
				}

				$vars = array_merge($vars, $vars2);

			} else {

				$vars = Array(
					"MerchantID" => $this->payment_params->merchantid,
					"Password" => $this->payment_params->password,
					"Amount" => round($order->cart->full_total->prices[0]->price_value_with_tax * 100),
					"CurrencyCode" => $this->sync_currencies[ $this->currency->currency_code ],
					"OrderID" => $order->order_id,
					"TransactionType" => $this->payment_params->instant_capture?'SALE':'PREAUTH',
					"TransactionDateTime" => date('Y-m-d H:i:s O'),
					"CallbackURL" => $server_url . '?' . $return_url_p,
					"OrderDescription" => $order->order_number,

					"CustomerName" => substr($customerName, 0, 100),
					"Address1" => $address1,
					"Address2" => $address2,
					"Address3" => $address3,
					"Address4" => $address4,
					"City" => substr(@$order->cart->billing_address->address_city, 0, 50),
					"State" => substr(@$order->cart->billing_address->address_state->zone_name, 0, 50),
					"PostCode" => substr(@$order->cart->billing_address->address_post_code, 0, 50),
					"CountryCode" => @$this->country_codes[$country_code_2],
					"CV2Mandatory" => $this->payment_params->cv2mandatory?'true':'false',
					"Address1Mandatory" => $this->payment_params->address1mandatory?'true':'false',
					"CityMandatory" => $this->payment_params->citymandatory?'true':'false',
					"PostCodeMandatory" => $this->payment_params->postcodemandatory?'true':'false',
					"StateMandatory" => $this->payment_params->statemandatory?'true':'false',
					"CountryMandatory" => $this->payment_params->countrymandatory?'true':'false',

					"ResultDeliveryMethod" => $this->payment_params->hosted_mode,
					"ServerResultURL" => $server_url,
					"PaymentFormDisplaysResult" => 'false',
					"ServerResultURLCookieVariables" => '',
					"ServerResultURLFormVariables" => '',
					"ServerResultURLQueryStringVariables" => $notify_url_p
				);

				$vars['HashDigest'] = $this->cardSaveHash($this->payment_params->hash_method, $vars, $this->payment_params->sharedkey);
				unset( $vars['Password'] );
			}

		} else {
			$ref = $this->app->getUserState( HIKASHOP_COMPONENT.'.ThreeDS_ref' );
			if( !empty($ref) ) {
				$viewType = 'threedsecure';

				$vars = array(
					'req' => $this->app->getUserState( HIKASHOP_COMPONENT.'.ThreeDS_req' ),
					'ref' => $this->app->getUserState( HIKASHOP_COMPONENT.'.ThreeDS_ref' ),
					'url' => $this->app->getUserState( HIKASHOP_COMPONENT.'.ThreeDS_url' ),
					'ret' => HIKASHOP_LIVE . 'index.php?option=com_hikashop&ctrl=checkout&task=threedsecure&3dsecure_payment=cardsave&orderid='.$order->order_id
				);

				$this->display = 'iframe';
				if( !empty($this->payment_params->threedsec_display) ) {
					$this->display = $this->payment_params->threedsec_display;
				}
			} else {
				$viewType = 'thankyou';
				$this->removeCart = true;
			}
		}
		$this->vars = $vars;

		return $this->showPage($viewType);
	}

	function onPaymentNotification(&$statuses){

		$customer = isset($_GET['cardsave_return']);
		if( !$customer ) {
			$order_id = (int)@$_POST['OrderID'];
		} else {
			$order_id = (int)@$_REQUEST['OrderID'];
		}

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if(empty($dbOrder)){
			echo "Could not load any order for your notification ".$order_id;
			if( !$customer ) {
				$msg = ob_get_clean();
				echo 'StatusCode=30&Message='.$msg;
				ob_start();
			}
			return false;
		}
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if( !$customer ) {
			$vars = array(
				'MerchantID' => @$_POST['MerchantID'],
				'Password' => $this->payment_params->password,
				'StatusCode' => @$_POST['StatusCode'],
				'Message' => @$_POST['Message'],
				'PreviousStatusCode' => @$_POST['PreviousStatusCode'],
				'PreviousMessage' => @$_POST['PreviousMessage'],
				'CrossReference' => @$_POST['CrossReference'],
				'Amount' => @$_POST['Amount'],
				'CurrencyCode' => @$_POST['CurrencyCode'],
				'OrderID' => @$_POST['OrderID'],
				'TransactionType' => @$_POST['TransactionType'],
				'TransactionDateTime' => @$_POST['TransactionDateTime'],
				'OrderDescription' => @$_POST['OrderDescription'],
				'CustomerName' => @$_POST['CustomerName'],
				'Address1' => @$_POST['Address1'],
				'Address2' => @$_POST['Address2'],
				'Address3' => @$_POST['Address3'],
				'Address4' => @$_POST['Address4'],
				'City' => @$_POST['City'],
				'State' => @$_POST['State'],
				'PostCode' => @$_POST['PostCode'],
				'CountryCode' => @$_POST['CountryCode'],
			);
		} else {
			$vars = array(
				'MerchantID' => @$_REQUEST['MerchantID'],
				'Passxword' => $this->payment_params->password,
				'CrossReference' => @$_REQUEST['CrossReference'],
				'OrderID' => @$_REQUEST['OrderID']
			);
		}

		if( !$customer ) {
			$vars['Calculated_HashDigest'] = $this->cardSaveHash($this->payment_params->hash_method, $vars, $this->payment_params->sharedkey);
		}

		if($this->payment_params->debug){
			echo print_r($vars,true)."\n\n\n";
		}
		$order_status = $dbOrder->order_status;
		$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=edit&order_id='.$order_id;
		$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
		$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

		$history = new stdClass();
		$email = new stdClass();

		if( $vars['MerchantID'] != $this->payment_params->merchantid ) {
			if( !$customer ) {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','CardSave').'invalid merchant id';
				$email->body = JText::sprintf("Hello,\r\n A CardSave notification was refused because the response from the merchant identifier was invalid")."\r\n\r\n".$order_text;

				$this->modifyOrder($order_id,null,false,$email);

				$msg = ob_get_clean();
				echo 'StatusCode=30&Message='.$msg;
				ob_start();
			} else {
				if($this->payment_params->debug) { echo 'merchantid is incorrect'; }
			}
			return false;
		}

		if( !$customer ) {
			if( !in_array($vars['CurrencyCode'], $this->sync_currencies) ) {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','CardSave').'invalid currency';
				$email->body = JText::sprintf("Hello,\r\n A CardSave notification was refused because the response from the currency was invalid")."\r\n\r\n".$order_text;

				$this->modifyOrder($order_id,null,false,$email);

				$msg = ob_get_clean();
				echo 'StatusCode=30&Message='.$msg;
				ob_start();
				return false;
			}
			if( $_POST['HashDigest'] != $vars['Calculated_HashDigest'] ) {
				$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','CardSave').'invalid hash';
				$email->body = JText::sprintf("Hello,\r\n A CardSave notification was refused because the response from the hash was invalid")."\r\n\r\n".$order_text;

				$this->modifyOrder($order_id,null,false,$email);

				$msg = ob_get_clean();
				echo 'StatusCode=30&Message=' . $msg;
				ob_start();
				return false;
			}
		}

		$httpsHikashop = HIKASHOP_LIVE;
		if( $this->payment_params->debug ) {
			$httpsHikashop = str_replace('https://','http://', HIKASHOP_LIVE);
		}

		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id;
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order';

		if( $customer ) {
			if( $dbOrder->order_status == $this->payment_params->invalid_status ) {
				$this->app->enqueueMessage('Transaction declined.');
				$this->app->redirect($cancel_url);
			} else {
				$db = JFactory::getDBO();
				$db->setQuery("SELECT * FROM ". hikashop_table('history') ." WHERE history_order_id=". $dbOrder->order_id." AND history_new_status=".$db->Quote($this->payment_params->verified_status)." ORDER BY history_created DESC;");
				$histories = $db->loadObjectList();
				foreach( $histories as $history ) {
					$data = $history->history_data;
					if( strpos($data, "\n--\n") !== false ) {
						$data = trim(substr($data, 0, strpos($data, "\n--\n")));
						$this->app->enqueueMessage($data);
						break;
					}
				}
				$this->app->redirect($return_url);
			}
		}

		$history = new stdClass();
		$history->notified = 0;
		$history->amount = ($vars['Amount']/100). array_search($vars['CurrencyCode'], $this->sync_currencies);
		$history->data = $vars['Message'] . "\n--\n" . 'CrossReference: ' . $vars['CrossReference'] . "\n" . ob_get_clean();
		ob_start();

		$completed = ($vars['StatusCode'] == '0');

		if( !$completed ) {
			$i = (int)$vars['StatusCode'];
			if( !isset($this->errorResultText[$i]) ) $i = 99;

			$order_status = $this->payment_params->invalid_status;
			$history->data .= "\n\n" . 'payment with code '.$vars['StatusCode'].' - '.$this->errorResultText[$i];

			$order_text = $vars['StatusCode'] . ' - ' . $this->errorResultText[$i]."\r\n\r\n".$order_text;
			$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave',$vars['StatusCode'])).' '.JText::_('STATUS_NOT_CHANGED')."\r\n\r\n".$order_text;
		 	$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','CardSave',$vars['StatusCode'],$dbOrder->order_number);

			$this->modifyOrder($order_id,$order_status,$history,$email);

			echo 'StatusCode='.$vars['StatusCode'].'&Message='.$this->errorResultText[$i] . "\n" . $msg;
			ob_start();
			return false;
		}


		$orderPrice = round($dbOrder->order_full_price * 100);
		$orderCurrency = $this->sync_currencies[ $this->currency->currency_code ];

		if( $orderPrice != $vars['Amount'] || $orderCurrency != $vars['CurrencyCode'] ) {
			$order_status = $this->payment_params->invalid_status;

			$email->subject = JText::sprintf('NOTIFICATION_REFUSED_FOR_THE_ORDER','CardSave').JText::_('INVALID_AMOUNT');
			$body = str_replace('<br/>',"\r\n",JText::sprintf('AMOUNT_RECEIVED_DIFFERENT_FROM_ORDER','CardSave',$history->history_amount,$orderPrice.$this->currency->currency_code))."\r\n\r\n".$order_text;

			$this->modifyOrder($order_id,$order_status,$history,$email);

			$msg = ob_get_clean();
			echo 'StatusCode=30&Message=' . $msg;
			ob_start();
			return false;
		}

		$order_status = $this->payment_params->verified_status;
		$vars['payment_status']='Accepted';
		$history->notified = 1;

		$email->subject = JText::sprintf('PAYMENT_NOTIFICATION_FOR_ORDER','CardSave',$vars['payment_status'],$dbOrder->order_number);
		$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave',$vars['payment_status'])).' '.JText::sprintf('ORDER_STATUS_CHANGED',$statuses[$order_status])."\r\n\r\n".$order_text;

		$this->modifyOrder($order_id,$order_status,$history,$email);

		$msg = ob_get_clean();
		echo 'StatusCode='.$vars['StatusCode'].'&Message=' . $msg;
		ob_start();
		return true;
	}

	function onThreeDSecure(&$statues){

		if( !isset($_GET['orderid']) || (int)$_GET['orderid'] == 0 ) return false;

		$order_id = (int)$_GET['orderid'];
		$order_status = '';

		$dbOrder = $this->getOrder($order_id);
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		$this->loadOrderData($dbOrder);
		if($this->payment_params->debug){
			echo print_r($dbOrder,true)."\n\n\n";
		}

		$httpsHikashop = HIKASHOP_LIVE;
		if( $this->payment_params->debug ) {
			$httpsHikashop = str_replace('https://','http://', HIKASHOP_LIVE);
		}

		$old_status=$dbOrder->order_status;

		$return_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id;
		$cancel_url = $httpsHikashop.'index.php?option=com_hikashop&ctrl=order&task=cancel_order';

		if( !isset($_POST['PaRes']) || !isset($_POST['MD']) ) {
			$this->app->enqueueMessage('Error while processing ThreeDSecure parameters.');
			$this->app->redirect($cancel_url);
			return false;
		}

		$do = false;
		$res = $_POST['PaRes'];
		$ref = $_POST['MD'];

		$gwId = 1;
		$cpt = 0;
		$domain = $this->payment_params->gw_entrypoint;
		$port =  (int)$this->payment_params->gw_port;

		if( $port == 443 || $port == 0 ) {
			$port = '';
		} else {
			$port = ':' . $port;
		}

		$xml = '<'.'?xml version="1.0" encoding="utf-8"?'.'>';
		$xml .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
		$xml .= '<soap:Body><ThreeDSecureAuthentication xmlns="https://www.thepaymentgateway.net/">';
		$xml .= '<ThreeDSecureMessage><MerchantAuthentication MerchantID="'.$this->payment_params->merchantid.'" Password="'.$this->payment_params->password.'"/>';
		$xml .= '<ThreeDSecureInputData CrossReference="'.$ref.'">';
		$xml .= '<PaRES>'.$res.'</PaRES></ThreeDSecureInputData></ThreeDSecureMessage></ThreeDSecureAuthentication>';
		$xml .= '</soap:Body></soap:Envelope>';

		$session = null;
		do {
			$soapSuccess = false;
			$url = 'https://gw'.$gwId.'.'.$domain.$port.'/';

			$session = curl_init();
			if( $session === false ) {
				$this->app->redirect($cancel_url);
				return false;
			}

			$headers = array(
				'SOAPAction:https://www.thepaymentgateway.net/ThreeDSecureAuthentication',
				'Content-Type: text/xml; charset = utf-8',
				'Connection: close'
			);

			curl_setopt($session, CURLOPT_HEADER, false);
			curl_setopt($session, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($session, CURLOPT_POST, true);
			curl_setopt($session, CURLOPT_URL, $url);
			curl_setopt($session, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($session, CURLOPT_ENCODING, 'UTF-8');
			curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);

			$history = new stdClass();
			$email = new stdClass();

			$ret = curl_exec($session);
			$err = curl_errno($session);
			$retHead = curl_getinfo($session);

			curl_close($session);
			$session = null;

			if( $err == 0 ) {
				$status = null;
				$soapStatus = null;

				if( preg_match('#<StatusCode>([0-9]+)</StatusCode>#iU', $ret, $soapStatus) ) {
					$status = (int)$soapStatus[1];
					$auth = null;
					$crossref = null;
					if( preg_match('#<AuthCode>([a-zA-Z0-9]+)</AuthCode>#iU', $ret, $auth) ) {
						$auth = $auth[1];
					}
					if( preg_match('#<TransactionOutputData\sCrossReference="([a-zA-Z0-9]+)"\s>#iU', $ret, $crossref) ) {
						$crossref = $crossref[1];
					}

					if( preg_match('#<TransactionDetails Amount="([0-9]+)" CurrencyCode="([0-9]+)">#iU', $ret, $amount) ) {
						$currency = $amount[1];
						$amount = $amount[2];
					} else {
						$amount = null;
					}

					if( $status == 0 && $soapStatus[1] != '0' ) {
						$status = 50;
					}

					if( $status != 50 ) {
						$soapSuccess = true;

						$history->notified = 0;
						if( $amount != null ) {
							$history->amount = $amount . array_search($this->currency, $this->sync_currencies);
						}
						$ohistory->data = ob_get_clean();
						ob_start();

						$url = HIKASHOP_LIVE.'administrator/index.php?option=com_hikashop&ctrl=order&task=listing';
						$order_text = "\r\n".JText::sprintf('NOTIFICATION_OF_ORDER_ON_WEBSITE',$dbOrder->order_number,HIKASHOP_LIVE);
						$order_text .= "\r\n".str_replace('<br/>',"\r\n",JText::sprintf('ACCESS_ORDER_WITH_LINK',$url));

						switch( $status ) {
							case 0:
								$do = true;
								$order_status = $this->payment_params->verified_status;
								$history->notified = 1;
								$payment_status = 'confirmed';

								$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','CardSave','Accepted');
								$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

								$this->modifyOrder($order_id,$order_status,$history,$email);

								break;

							case 5:
								$order_status = $this->payment_params->invalid_status;
								$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','CardSave','Transaction declined');

								$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave','Declined')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

								$this->modifyOrder($order_id,$order_status,$history,$email);

								$this->app->enqueueMessage('Transaction declined.');
								break;

							case 20:
								if( preg_match('#<PreviousTransactionResult>.*<StatusCode>([0-9]+)</StatusCode>#iU', $ret, $soapStatus2) ) {
									if( $soapStatus2[1] == '0' ) {
										$do = true;
										$this->app->enqueueMessage('Transaction already validate.');

										if( $old_status != $this->payment_params->verified_status ) {
											$order_status = $this->payment_params->verified_status;
											$history->notified = 1;
											$payment_status = 'confirmed';
											$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','CardSave','Accepted');
											$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave','Accepted')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order_status)."\r\n\r\n".$order_text;

											$this->modifyOrder($order_id,$order_status,$history,$email);
										}

									} else if( preg_match('#<Message>(.*)</Message>.*</PreviousTransactionResult>#iU', $ret, $msg) ) {
										$this->app->enqueueMessage($msg[1]);
									} else {
										$this->app->enqueueMessage('Duplicate transaction');
									}
								} else {
									$this->app->enqueueMessage('Duplicate transaction.');
								}
								break;

							case 30:
							default:
								if( preg_match('#<Message>(.*)</Message>#iU', $ret, $msg) ) {
									$msg = $msg[1];
								} else {
									$msg = '';
								}
								$order_status = $this->payment_params->invalid_status;
								$email->subject = JText::sprintf('PAYMENT_NOTIFICATION','CardSave','Transaction error ('.$status.')');

								$order_text = 'CardSave Error ('.$status.') : ' . $msg . "\r\n\r\n" . $order_text;
								$email->body = str_replace('<br/>',"\r\n",JText::sprintf('PAYMENT_NOTIFICATION_STATUS','CardSave','Error')).' '.JText::sprintf('ORDER_STATUS_CHANGED',$order->order_status)."\r\n\r\n".$order_text;

								$this->modifyOrder($order_id,$order_status,$history,$email);

								$this->app->enqueueMessage('CardSave Error ('.$status.') :' . $msg);
								break;
						}
					}
				}
			}

			if( $session ) {
				curl_close($session);
			}

			if( !$soapSuccess ) {
				$cpt++;
				if( $cpt >= 2 ) {
					$cpt = 0;
					$gwId++;
					if( $gwId > 3 ) {
						$this->app->enqueueMessage('Impossible to contact the CardSave payment gateway.');
						$soapSuccess = true;
						break;
					}
				}
			}


		} while( !$soapSuccess && $gwId < 4 && $cpt < 3 );

		$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_ref','');
		$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_req','');
		$this->app->setUserState( HIKASHOP_COMPONENT.'.ThreeDS_url','');

		if( $do ) {
			$this->app->redirect($return_url);
		} else {
			$this->app->redirect($cancel_url);
		}
		return true;
	}

	function onHistoryDisplay(&$histories) {
		foreach($histories as $history) {
			if($history->history_payment_method == 'cardsave' && !empty($history->history_data)) {
				$history->history_data = str_replace("\n","<br/>", trim($history->history_data) );
			}
		}
	}

	function onPaymentConfiguration(&$element){
		parent::onPaymentConfiguration($element);

		$obj = $element;
		$fill = '';

		if(empty($obj->payment_params->merchantid)){
			$fill = JText::_('MERCHANT_NUMBER');
		}
		if(empty($obj->payment_params->password)){
			$fill = JText::_('HIKA_PASSWORD');
		}
		if(empty($obj->payment_params->sharedkey)){
			$fill = JText::_('SHARED_KEY');
		}
		if(!empty($fill)){
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::sprintf('ENTER_INFO_REGISTER_IF_NEEDED','CardSave',$fill,'CardSave','http://www.cardsave.net/hikashop/'));
		}
	}

	function getPaymentDefaultValues(&$element) {
		$element->payment_name='CARDSAVE';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='VISA,Maestro,MasterCard,JCB';

		$element->payment_params->hosted_mode='SERVER';
		$element->payment_params->cv2mandatory=true;
		$element->payment_params->address1mandatory=true;
		$element->payment_params->citymandatory=true;
		$element->payment_params->postcodemandatory=true;
		$element->payment_params->statemandatory=true;
		$element->payment_params->countrymandatory=true;
		$element->payment_params->gw_entrypoint='cardsaveonlinepayments.com';
		$element->payment_params->gw_port=4430;
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->pending_status='created';
		$element->payment_params->verified_status='confirmed';
	}

	function onPaymentConfigurationSave(&$element){
		$element->payment_params->hosted_mode = 'SERVER';
		return true;
	}

	function cardSaveHash($hash_method, &$data, $key) {
		$str = '';
		foreach($data as $k=>$v) {
			if($str!='') $str.= '&';
			$str.= $k.'='.$v;
		}
		switch( $hash_method ) {
			case 'hmacsha1':
				return $this->hmacsha1($str,$key);

			case 'hmacmd5':
				return $this->hmacmd5($str,$key);

			case 'md5':
				$str = 'PreSharedKey='.$key.'&'. $str;
				return md5($str);

			case 'sha1':
			default:
				$str = 'PreSharedKey='.$key.'&'. $str;
				return sha1($str);
		}
	}

	function hmacsha1($data,$key) {
		if( function_exists('mhash') ) {
			return mhash(MHASH_SHA1, $data, $key);
		}

		if( !function_exists('sha1') ) {
			die('SHA1 function is not present');
		}
		if (strlen($key)>64)
			$key = pack('H*', sha1($key));
		$key = str_pad($key,64,chr(0x00));
		$ipad = str_repeat(chr(0x36),64);
		$opad = str_repeat(chr(0x5c),64);
		return pack('H*', sha1( ($key ^ $opad) . pack('H*', sha1(($key ^ $ipad) . $data)) ));
	}

	function hmacmd5($data,$key) {
		if( function_exists('mhash') ) {
			return mhash(MHASH_MD5, $data, $key);
		}

		if( !function_exists('md5') ) {
			die('MD5 function is not present');
		}
		if (strlen($key)>64)
			$key = pack('H*', md5($key));
		$key = str_pad($key,64,chr(0x00));
		$ipad = str_repeat(chr(0x36),64);
		$opad = str_repeat(chr(0x5c),64);
		return pack('H*', md5( ($key ^ $opad) . pack('H*', md5(($key ^ $ipad) . $data)) ));
	}
}
