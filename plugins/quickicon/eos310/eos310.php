<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Quickicon.eos310
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Joomla! end of support notification plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgQuickiconEos310 extends JPlugin
{
	/**
	 * The EOS date for 3.10
	 * @todo The date is just for testing and is to be updated on release
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const EOS_DATE = '2024-07-06';

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __DEPLOY_VERSION__
	 */
	protected $autoloadLanguage = true;

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array|void  A list of icon definition associative arrays, consisting of the
	 *						keys link, image, text and access, or void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetIcons($context)
	{
		if (!Factory::getApplication()->isClient('administrator') || version_compare(JVERSION, '4.0', '>='))
		{
			return;
		}

		$diff = Factory::getDate()->diff(Factory::getDate(static::EOS_DATE));
		$monthsUntilEOS = floor($diff->days / 30.417);

		$messageInfo   = $this->getMessageInfo($monthsUntilEOS, $diff->invert);
		$lastSnoozedId = $this->params->get('last_snoozed_id', 0);

		// No messages yet or the message is snoozed
		if (!$messageInfo || $lastSnoozedId >= $messageInfo['id'])
		{
			return;
		}

		// Show this only above the cpanel
		if ($this->app->input->get('option') == 'com_cpanel')
		{
			// Build the  message to be displayed in the cpanel
			$messageText = Text::sprintf(
				$messageInfo['messageText'],
				JHtml::_('date', static::EOS_DATE, Text::_('DATE_FORMAT_LC3')),
				$messageInfo['messageLink'],
			) . "<p><button class='btn btn-warning eosnotify-snooze-btn' type='button'>" . Text::_('PLG_QUICKICON_EOS310_SNOOZE_BUTTON') . "</button></p>";

			$this->app->enqueueMessage(
				$messageText,
				$messageInfo['messageType'],
			);
		}

		// Make sure it opens in a new window with the rel tags attached.
		$messageTextQuickIcon = "<a href='" . $messageInfo['messageLink'] . "' target='_blank' rel='noopener noreferrer'>" . $messageInfo['quickiconText'] . "</a> <span class='icon-new-tab'></span>";

		// We allways show the message as quickicon
		return array(array(
			'link'  => $messageInfo['messageLink'],
			'image' => $messageInfo['image'],
			'text'  => $messageTextQuickIcon,
			'id'	=> 'plg_quickicon_eos310',
			'group' => $messageInfo['groupText'],
		));
	}

	/**
	 * User hit the snooze button
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws  JAccessExceptionNotallowed  If user is not allowed.
	 */
	public function onAjaxSnoozeEOS()
	{
		$diff = Factory::getDate()->diff(Factory::getDate(static::EOS_DATE));

		if (!$this->isAllowedUser() || !$this->isAjaxRequest() || !$diff->invert)
		{
			throw new JAccessExceptionNotallowed(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		$monthsUntilEOS = floor($diff->days / 30.417);
		$messageInfo = $this->getMessageInfo($monthsUntilEOS, $diff->invert);

		$this->params->set('last_snoozed_id', $messageInfo['id']);

		$this->saveParams();
	}

	/**
	 * Return the texts to be displayed based on the time until we reach EOS
	 *
	 * @param   integer  $monthsUntilEOS  The months until we reach EOS
	 * @param   integer  $inverted		Have we surpassed the EOS date
	 *
	 * @return  array|bool  An array with the message to be displayed or false
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getMessageInfo($monthsUntilEOS, $inverted)
	{
		// The EOS date has passed - Support has ended
		if ($inverted === 1)
		{
			return array(
				'id'            => 5,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_ERROR_SUPPORT_ENDED',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_ERROR_SUPPORT_ENDED_SHORT',
				'messageType'   => 'error',
				'image'         => 'minus-circle',
				'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'     => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_EOS'),
			);
		}

		// The security support is ending in 6 months
		if ($monthsUntilEOS <= 6)
		{
			return array(
				'id'            => 4,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SUPPORT_ENDING',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SUPPORT_ENDING_SHORT',
				'messageType'   => 'warning',
				'image'         => 'warning-circle',
				'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'     => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_WARNING'),
			);
		}

		// We are in security only mode now, 12 month to go from now on
		if ($monthsUntilEOS <= 12)
		{
			return array(
				'id'            => 3,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SECURITY_ONLY',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SECURITY_ONLY_SHORT',
				'messageType'   => 'warning',
				'image'         => 'warning-circle',
				'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'     => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_WARNING'),
			);
		}

		// We still have 16 month to go, lets remind them about the pre upgrade checker
		if ($monthsUntilEOS <= 16)
		{
			return array(
				'id'            => 2,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_02',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_02_SHORT',
				'messageType  ' => 'info',
				'image'         => 'info-circle',
				'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Pre-Update_Check',
				'groupText'     => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_INFO'),
			);
		}

		// Lets start our messages 2 month after the initial release, still 22 month to go
		if ($monthsUntilEOS <= 22)
		{
			return array(
				'id'            => 1,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_01',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_01_SHORT',
				'messageType'   => 'info',
				'image'         => 'info-circle',
				'messageLink'   => 'https://joomla.org/4',
				'groupText'     => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_INFO'),
			);
		}

		return false;
	}

	/**
	 * Check valid AJAX request
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isAjaxRequest()
	{
		return strtolower($this->app->input->server->get('HTTP_X_REQUESTED_WITH', '')) === 'xmlhttprequest';
	}

	/**
	 * Check if current user is allowed to send the data
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function isAllowedUser()
	{
		return JFactory::getUser()->authorise('core.login.admin');
	}

	/**
	 * Save the plugin parameters
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function saveParams()
	{
		$query = $this->db->getQuery(true)
			->update($this->db->quoteName('#__extensions'))
			->set($this->db->quoteName('params') . ' = ' . $this->db->quote($this->params->toString('JSON')))
			->where($this->db->quoteName('type') . ' = ' . $this->db->quote('plugin'))
			->where($this->db->quoteName('folder') . ' = ' . $this->db->quote('quickicon'))
			->where($this->db->quoteName('element') . ' = ' . $this->db->quote('eos310'));

		try
		{
			// Lock the tables to prevent multiple plugin executions causing a race condition
			$this->db->lockTable('#__extensions');
		}
		catch (Exception $e)
		{
			// If we can't lock the tables it's too risky to continue execution
			return false;
		}

		try
		{
			// Update the plugin parameters
			$result = $this->db->setQuery($query)->execute();

			$this->clearCacheGroups(array('com_plugins'), array(0, 1));
		}
		catch (Exception $exc)
		{
			// If we failed to execute
			$this->db->unlockTables();

			$result = false;
		}

		try
		{
			// Unlock the tables after writing
			$this->db->unlockTables();
		}
		catch (Exception $e)
		{
			// If we can't lock the tables assume we have somehow failed
			$result = false;
		}

		return $result;
	}

	/**
	 * Clears cache groups. We use it to clear the plugins cache after we update the last run timestamp.
	 *
	 * @param   array  $clearGroups   The cache groups to clean
	 * @param   array  $cacheClients  The cache clients (site, admin) to clean
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function clearCacheGroups(array $clearGroups, array $cacheClients = array(0, 1))
	{
		foreach ($clearGroups as $group)
		{
			foreach ($cacheClients as $client_id)
			{
				try
				{
					$options = array(
						'defaultgroup' => $group,
						'cachebase'	=> $client_id ? JPATH_ADMINISTRATOR . '/cache' : $this->app->get('cache_path', JPATH_SITE . '/cache')
					);

					$cache = JCache::getInstance('callback', $options);
					$cache->clean();
				}
				catch (Exception $e)
				{
					// Ignore it
				}
			}
		}
	}
}
