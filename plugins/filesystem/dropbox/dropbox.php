<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  FileSystem.Dropbox
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;

\JLoader::import('filesystem.dropbox.vendor.autoload', JPATH_PLUGINS);

/**
 * FileSystem Dropbox plugin.
 * The plugin used to manipulate dropbox filesystem in Media Manager
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgFileSystemDropbox extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns a dropbox media adapter to the caller which can be used to manipulate files
	 *
	 * @return   \Joomla\Plugin\Filesystem\Dropbox\Adapter\JoomlaDropboxAdapter[]
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function onFileSystemGetAdapters()
	{
		$accessToken = $this->params->get('access_token');
		$accountName = $this->params->get('account_name');

		$dropbox = new \Joomla\Plugin\Filesystem\Dropbox\Adapter\JoomlaDropboxAdapter($accessToken);
		$dropbox->setAccountName($accountName);

		return [$dropbox];
	}

	/**
	 * Handle OAuthCallback request
	 *
	 * @param   \Joomla\Component\Media\Administrator\Event\OAuthCallbackEvent  $event  The event object
	 *
	 * @return  void
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public function onFileSystemOAuthCallback(\Joomla\Component\Media\Administrator\Event\OAuthCallbackEvent $event)
	{
		// Set default result to be returned
		$result = [
			"action" => "control-panel"
		];

		// Get the input
		$data = $event->getInput();
		$code = $data->get('code', null);

		// If code is set proceed
		if ($code != null)
		{
			$clientID     = $this->params->get('client_id', null);
			$clientSecret = $this->params->get('client_secret', null);
			$app          = new \Kunnu\Dropbox\DropboxApp($clientID, $clientSecret);
			$dropbox      = new \Kunnu\Dropbox\Dropbox($app);
			$helper       = $dropbox->getAuthHelper();
			$redirectURI  = Joomla\CMS\Uri\Uri::root() . 'administrator/' .
							'index.php?option=com_media&task=plugin.oauthcallback&plugin=dropbox';
			$tokenData    = $helper->getAccessToken($code, null, $redirectURI);
			$accessToken  = $tokenData->getToken();

			$dropbox->setAccessToken($accessToken);
			$accountName  = $dropbox->getCurrentAccount()->getDisplayName();

			// Save Access token and Account name
			$this->params->set('access_token', $accessToken);
			$this->params->set('account_name', $accountName);

			$db = \Joomla\CMS\Factory::getDbo();
			$query = $db->getQuery(true)
						->update($db->qn('#__extensions'))
						->set($db->qn('params') . '=' . $db->q($this->params->toString()))
						->where($db->qn('element') . '=' . $db->q('dropbox'))
						->where($db->qn('type') . '=' . $db->q('plugin'));
			$db->setQuery($query);
			$db->execute();

			// Set result
			$result = [
				"action"  => "media-manager",
				"message" => "Account for " . $accountName . " successfully set"
			];
		}

		// Pass back the result to event
		$event->setArgument('result', $result);
	}
}
