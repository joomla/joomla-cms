<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.formfield');
\JLoader::import('filesystem.dropbox.vendor.autoload', JPATH_PLUGINS);

/**
 * Class JFormFieldAuthButton
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldAuthButton extends JFormField
{
	/**
	 * Name of the field
	 *
	 * @var string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = "AuthButton";

	/**
	 * Get label of the button
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getLabel()
	{
		return '';
	}

	/**
	 * Get input for button
	 *
	 * @return string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getInput()
	{
		$plugin        = \Joomla\CMS\Plugin\PluginHelper::getPlugin('filesystem', 'dropbox');
		$params        = new \Joomla\Registry\Registry($plugin->params);
		$client_id     = $params->get('client_id', '');
		$client_secret = $params->get('client_secret', '');

		$app     = new \Kunnu\Dropbox\DropboxApp($client_id, $client_secret);
		$dropbox = new \Kunnu\Dropbox\Dropbox($app);

		$helper      = $dropbox->getAuthHelper();
		$redirectUri = $helper->getAuthUrl(
			Joomla\CMS\Uri\Uri::root() . 'administrator/index.php' . '?option=com_media&task=plugin.oauthcallback&plugin=dropbox'
		);

		$html  = '<a class="btn btn-info" href="' . $redirectUri . '">Get Access Token</a>';
		$html .= '<br><p><b>*Use this button after saving client credentials</b></p>';

		return $html;
	}
}
