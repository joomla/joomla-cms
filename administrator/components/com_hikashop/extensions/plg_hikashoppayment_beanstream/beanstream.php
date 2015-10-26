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
class plgHikashoppaymentBeanstream extends hikashopPaymentPlugin
{
	var $accepted_currencies = array( 'USD', 'CAD' ); //The merchant must have one Beanstream gateway merchant ID and administrator account for each processing currency.
	var $name = 'beanstream';
	var $doc_form = 'beanstream';
	var $multiple = true;

	var $pluginConfig = array(
		'currency' => array( 'Payment mode', 'list',array('USD' => 'USD','CAD' => 'CAD') ),
		'merchant' => array('MERCHANT_NUMBER','input'),
		'hash_method' => array( 'HASH_METHOD','list',array('NONE' => 'None', 'MD5' => 'MD5','SHA1' => 'SHA1') ),
		'hash' => array('Hash','input'), //Hash parametered on Bean's account
		'notify_url' => array('NOTIFY_URL_DEFINE','html',''),
		'debug' => array('DEBUG', 'boolean','0'),
		'invalid_status' => array('INVALID_STATUS', 'orderstatus'),
		'verified_status' => array('VERIFIED_STATUS', 'orderstatus')
	);


	function __construct(&$subject, $config)
	{
		$this->pluginConfig['notify_url'][2] = HIKASHOP_LIVE.'beanstream_params.php';
		return parent::__construct($subject, $config);
	}


	function onAfterOrderConfirm(&$order,&$methods,$method_id) //On the checkout
	{
		parent::onAfterOrderConfirm($order,$methods,$method_id);

		if (empty($this->payment_params->merchant))
		{
			$this->app->enqueueMessage('You have to configure a merchant id on the Beanstream plugin payment parameters first','error');
			return false;
		}
		else
		{
			$amout =round($order->cart->full_total->prices[0]->price_value_with_tax,2);

			$notify_url = HIKASHOP_LIVE.'beanstream_params_u.php';

			$vars = array(
				'merchant_id' => $this->payment_params->merchant,
				'trnOrderNumber' => $order->order_id,
				'trnAmount' => $amout,
			);

			if ($this->payment_params->hash_method != 'NONE' && !empty($this->payment_params->hash))
				$vars['hashValue'] = $this->beanstream_signature($this->payment_params->hash,$vars);
			elseif (empty($this->payment_params->hash))
			{
				$this->app->enqueueMessage('You have to configure a hash on the Beanstream plugin payment parameters first','error');
				return false;
			}

			$vars['approvedPage'] = $notify_url ;
			$this->vars = $vars;
			$this->payment_params->url='https://www.beanstream.com/scripts/payment/payment.asp';

			return $this->showPage('end');
		}
	}


	function getPaymentDefaultValues(&$element) //To set the back end default values
	{
		$element->payment_name='Beanstream';
		$element->payment_description='You can pay by credit card using this payment method';
		$element->payment_images='MasterCard,VISA,Credit_card,American_Express,Diners,Discover,JCB,Sears'; //Merchant accounts for each card type
		$element->payment_params->address_type="billing";
		$element->payment_params->invalid_status='cancelled';
		$element->payment_params->verified_status='confirmed';
	}


	function onPaymentNotification(&$statuses)
	{
		$vars = array();
		$filter = JFilterInput::getInstance();
		$userSide=false;
		$validOrder=true;
		$app = JFactory::getApplication();

		foreach($_REQUEST as $key => $value)
		{
			$key = $filter->clean($key);
			$value = JRequest::getString($key);
			$vars[$key]=$value;
			if ($key=='userside')
				$userSide=true;
		}

		$order_id = (int)@$vars['trnOrderNumber'];

		$dbOrder = $this->getOrder($order_id);
		if(empty($dbOrder))
			return false;
		$this->loadPaymentParams($dbOrder);
		if(empty($this->payment_params))
			return false;
		if(strcmp($this->name,$dbOrder->order_payment_method))
			return false;
		$this->loadOrderData($dbOrder);
		$return_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=checkout&task=after_end&order_id='.$order_id.$this->url_itemid;
		$cancel_url = HIKASHOP_LIVE.'index.php?option=com_hikashop&ctrl=order&task=cancel_order&order_id='.$order_id.$this->url_itemid;

		if($this->payment_params->debug)
		{
			echo print_r($vars,true)."\n\n\n";
			echo print_r($dbOrder,true)."\n\n\n";
		}

		if (!$userSide)
		{
			if ($this->payment_params->hash_method != 'NONE' && !empty($this->payment_params->hash))
			{
				$validOrder=false;
				$hash = $this->beanstream_signature($this->payment_params->hash,$vars,false,true);
				if (strcasecmp($hash,$vars['hashValue'])!=0)
				{
					if($this->payment_params->debug)
					{
						$url = $this->beanstream_signature($this->payment_params->hash,$vars,true,true);
						var_dump($url);
						echo 'Hash error '.$vars['hashValue'].' - '.$hash."\n\n\n";
					}
					return false;
				}
				else
				{
					$validOrder=true;
				}
			}
			if ( $vars['trnApproved']!=1 || $vars['messageId']!=1 || $vars['messageText']!='Approved' )
			{
				if($this->payment_params->debug)
					echo 'payment '.$vars['messageText']."\n\n\n";
				$this->modifyOrder($order_id, $this->payment_params->invalid_status, true, true);
				return false;
			}
			elseif($validOrder)
			{
				$this->modifyOrder($order_id, $this->payment_params->verified_status, true, true);
				return true;
			}
		}

		else
		{
			if ($this->payment_params->hash_method != 'NONE' && !empty($this->payment_params->hash))
			{
				$validOrder=false;
				$hash = $this->beanstream_signature($this->payment_params->hash,$vars,false,true);
				if (strcasecmp($hash,$vars['hashValue'])!=0)
					return false;
				else
					$validOrder=true;
			}
			if ( $vars['trnApproved']!=1 || $vars['messageId']!=1 || $vars['messageText']!='Approved' )
			{
				$this->app->redirect($cancel_url);
				return false;
			}
			elseif($validOrder)
			{
				$this->app->redirect($return_url);
				return true;
			}
		}

		return true;
	}



	function onPaymentConfigurationSave(&$element)
	{
		parent::onPaymentConfigurationSave($element);


		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		jimport('joomla.filesystem.path');
		$lang = JFactory::getLanguage();
		$locale = strtolower(substr($lang->get('tag'),0,2));
		$writen=true;

		$content = '<?php
			$_GET[\'option\']=\'com_hikashop\';
			$_GET[\'tmpl\']=\'component\';
			$_GET[\'ctrl\']=\'checkout\';
			$_GET[\'task\']=\'notify\';
			$_GET[\'notif_payment\']=\'beanstream\';
			$_GET[\'format\']=\'html\';
			$_GET[\'lang\']=\''.$locale.'\';
			$_REQUEST[\'option\']=\'com_hikashop\';
			$_REQUEST[\'tmpl\']=\'component\';
			$_REQUEST[\'ctrl\']=\'checkout\';
			$_REQUEST[\'task\']=\'notify\';
			$_REQUEST[\'notif_payment\']=\'beanstream\';
			$_REQUEST[\'format\']=\'html\';
			$_REQUEST[\'lang\']=\''.$locale.'\';
			include(\'index.php\');
		';
		ob_start();
		$writen = JFile::write(JPATH_ROOT.DS.'beanstream_params.php', $content);
		ob_get_clean();
		if (!$writen)
			$app->enqueueMessage('The plugin failed writing the file beanstream_params.php in the root directory : '.JPATH_ROOT.DS. '. Please check if the writing permission has been given to the owner.','error');

		$content = '<?php
			$_GET[\'option\']=\'com_hikashop\';
			$_GET[\'tmpl\']=\'component\';
			$_GET[\'ctrl\']=\'checkout\';
			$_GET[\'task\']=\'notify\';
			$_GET[\'notif_payment\']=\'beanstream\';
			$_GET[\'format\']=\'html\';
			$_GET[\'lang\']=\''.$locale.'\';
			$_GET[\'userside\']=\'1\';
			$_REQUEST[\'option\']=\'com_hikashop\';
			$_REQUEST[\'tmpl\']=\'component\';
			$_REQUEST[\'ctrl\']=\'checkout\';
			$_REQUEST[\'task\']=\'notify\';
			$_REQUEST[\'notif_payment\']=\'beanstream\';
			$_REQUEST[\'format\']=\'html\';
			$_REQUEST[\'lang\']=\''.$locale.'\';
			$_REQUEST[\'userside\']=\'1\';
			include(\'index.php\');
		';
		ob_start();
		$writen = JFile::write(JPATH_ROOT.DS.'beanstream_params_u.php', $content);
		ob_get_clean();
		if (!$writen)
			$app->enqueueMessage('The plugin failed writing the file beanstream_params_u.php in the root directory : '.JPATH_ROOT.DS. '. Please check if the writing permission has been given to the owner.','error');

		return true;
	}


	function beanstream_signature($hash, $parameters,$debug=false, $decode=false)
	{
		$clear_string = '';
		$first=true;
		$expectedKey = array (
			'trnApproved',
			'trnId',
			'messageId',
			'messageText',
			'authCode',
			'responseType',
			'trnAmount',
			'trnDate',
			'trnOrderNumber',
			'trnLanguage',
			'trnCustomerName',
			'trnEmailAddress',
			'trnPhoneNumber',
			'avsProcessed',
			'avsId',
			'avsResult',
			'avsAddrMatch',
			'avsPostalMatch',
			'avsMessage',
			'cvdId',
			'cardType',
			'trnType',
			'paymentMethod',
			'ref1',
			'ref2',
			'ref3',
			'ref4',
			'ref5'
		);

		foreach ($parameters as $key => $value)
		{
			if ($decode)
			{
				if (in_array($key,$expectedKey))
				{
					if (!$first)
						$clear_string .= '&';
					$first=false;
					if (strcasecmp($key, 'trnAmount')!=0)
						$clear_string .= $key . '=' . str_replace(".","%2E",urlencode($value)) ;
					else
						$clear_string .= $key . '=' . $value ;
				}
			}
			else
			{
				if (!$first)
					$clear_string .= '&';
				$first=false;
				$clear_string .= $key . '=' . $value ;
			}
		}
		$clear_string .= $hash;

		if ($debug)
			return $clear_string;
		else
		{
			if ($this->payment_params->hash_method == 'MD5')
				return md5($clear_string);
			elseif ($this->payment_params->hash_method == 'SHA1')
				return sha1($clear_string);
		}

	}

}
