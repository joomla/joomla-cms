<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.log
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Joomla! System Logging Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  System.log
 * @since       1.5
 */
class PlgSystemLog extends JPlugin
{
	public function onUserLoginFailure($response)
	{
		$errorlog = array();

		switch($response['status'])
		{
			case JAuthentication::STATUS_SUCCESS:
				$errorlog['status']  = $response['type'] . " CANCELED: ";
				$errorlog['comment'] = $response['error_message'];
				break;

			case JAuthentication::STATUS_FAILURE:
				$errorlog['status']  = $response['type'] . " FAILURE: ";
				$errorlog['comment'] = $response['error_message'];
				if ($this->params->get('log_username', 0) && $this->params->get('log_sourceip',0))
				{
					$alternativeIPData = $this->retrieveAlternativeIPAddress();
					if (is_array($alternativeIPData) && (!JDEBUG || !JFactory::getApplication()->getCfg('debug_lang'))) {
						$errorlog['comment'] .= ' (username="' . $response['username'] . 
									'",REMOTE_ADDR=' . $_SERVER["REMOTE_ADDR"] .
									' ' . $alternativeIPData[0] . '=' . $alternativeIPData[1] . ')';
					} elseif (is_array($alternativeIPData) && (JDEBUG || JFactory::getApplication()->getCfg('debug_lang'))) {
						$errorlog['comment'] .= ' (username="' . $response['username'] . '",DEBUG=';
						foreach ( $alternativeIPData as $source) {
							$errorlog['comment'] .= $source . '//';
						}
						$errorlog['comment'] .= ')';
					} else {
						$errorlog['comment'] .= ' (username="' . $response['username'] . '",srcip=' . $_SERVER["REMOTE_ADDR"] . ')';
					}
				}
				elseif ($this->params->get('log_username', 0) && $this->params->get('log_sourceip',1))
				{
					$errorlog['comment'] .= ' (username="' . $response['username'] . '")';
				}
				break;

			default:
				$errorlog['status']  = $response['type'] . " UNKNOWN ERROR: ";
				$errorlog['comment'] = $response['error_message'];
				break;
		}
		JLog::addLogger(array(), JLog::INFO);
		JLog::add($errorlog['comment'], JLog::INFO, $errorlog['status']);
	}
	
	private function retrieveAlternativeIPAddress()
	{
		$result = false;
		$ip_sources = array('HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_FORWARDED','HTTP_FORWARDED_FOR','HTTP_X_CLUSTER_CLIENT_IP');
		if (JDEBUG || JFactory::getApplication()->getCfg('debug_lang'))
		{	$result=array();
			foreach ( $ip_sources as $source) {
				$result=array_push($_SERVER[$source]);
			}
		}
		else 
		{
			foreach ( $ip_sources as $source) {
				if (array_key_exists($source,$_SERVER) && isset($_SERVER[$source])
							       && !empty($_SERVER[$source])
							       && filter_var($_SERVER[$source],
							       FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
				$result=array($source,trim($_SERVER[$source]));
				break;
				}
			}
		}
		return $result;
	}
}
