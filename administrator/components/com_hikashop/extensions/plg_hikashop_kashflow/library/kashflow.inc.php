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
class Kashflow
{
	private $client   = null;
	private $username = "";
	private $password = "";

	public function __construct($username,$password)
	{
		$this->client   = new SoapClient("https://securedwebapp.com/api/service.asmx?WSDL");
		$this->username = $username;
		$this->password = $password;
	}

	private static function handleResponse($response)
	{
		if("NO" == $response->Status)
			throw(new Exception($response->StatusDetail));
		return $response;
	}

	public function makeRequest($fn,$extra = null)
	{
		$parameters = array();
		$parameters['UserName'] = $this->username;
		$parameters['Password'] = $this->password;
		if(null != $extra)
			$parameters = array_merge($parameters,$extra);
		return self::handleResponse($this->client->$fn($parameters));
	}
}

?>
