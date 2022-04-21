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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Joomla! end of support notification plugin
 *
 * @since  3.10.0
 */
class PlgQuickiconEos310 extends CMSPlugin
{
	/**
	 * The EOS date for 3.10
	 *
	 * @var    string
	 * @since  3.10.0
	 */
	const EOS_DATE = '2023-08-17';

	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  3.10.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  3.10.0
	 */
	protected $db;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  3.10.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Holding the current valid message to be shown
	 *
	 * @var    boolean
	 * @since  3.10.0
	 */
	private $currentMessage = false;

	/**
	 * Constructor.
	 *
	 * @param   object  &$subject  The object to observe.
	 * @param   array   $config    An optional associative array of configuration settings.
	 *
	 * @since   3.10.0
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		$diff           = Factory::getDate()->diff(Factory::getDate(static::EOS_DATE));
		$monthsUntilEOS = floor($diff->days / 30.417);

		$this->currentMessage = $this->getMessageInfo($monthsUntilEOS, $diff->invert);
	}

	/**
	 * Check and show the the alert and quickicon message
	 *
	 * @param   string  $context  The calling context
	 *
	 * @return  array|void  A list of icon definition associative arrays, consisting of the
	 *			keys link, image, text and access, or void.
	 *
	 * @since   3.10.0
	 */
	public function onGetIcons($context)
	{
		if (!$this->shouldDisplayMessage())
		{
			return;
		}

		// No messages yet
		if (!$this->currentMessage)
		{
			return;
		}

		// Show this only when not snoozed
		if ($this->params->get('last_snoozed_id', 0) < $this->currentMessage['id'])
		{
			// Load the snooze scripts.
			HTMLHelper::_('jquery.framework');
			HTMLHelper::_('script', 'plg_quickicon_eos310/snooze.js', array('version' => 'auto', 'relative' => true));

			// Build the  message to be displayed in the cpanel
			$messageText = Text::sprintf(
				$this->currentMessage['messageText'],
				HTMLHelper::_('date', static::EOS_DATE, Text::_('DATE_FORMAT_LC3')),
				$this->currentMessage['messageLink']
			);

			if ($this->currentMessage['snoozable'])
			{
				$messageText .=
					'<p><button class="btn btn-warning eosnotify-snooze-btn" type="button">' .
					Text::_('PLG_QUICKICON_EOS310_SNOOZE_BUTTON') .
					'</button></p>';
			}

			$this->app->enqueueMessage(
				$messageText,
				$this->currentMessage['messageType']
			);
		}

		// The message as quickicon
		$messageTextQuickIcon = Text::sprintf(
			$this->currentMessage['quickiconText'],
			HTMLHelper::_(
				'date',
				static::EOS_DATE,
				Text::_('DATE_FORMAT_LC3')
			)
		);

		// The message as quickicon
		return array(array(
			'link'   => $this->currentMessage['messageLink'],
			'target' => '_blank',
			'rel'    => 'noopener noreferrer',
			'image'  => $this->currentMessage['image'],
			'text'   => $messageTextQuickIcon,
			'id'	 => 'plg_quickicon_eos310',
			'group'  => $this->currentMessage['groupText'],
		));
	}

	/**
	 * User hit the snooze button
	 *
	 * @return  void
	 *
	 * @since   3.10.0
	 *
	 * @throws  JAccessExceptionNotallowed  If user is not allowed.
	 */
	public function onAjaxSnoozeEOS()
	{
		// No messages yet so nothing to snooze
		if (!$this->currentMessage)
		{
			return;
		}

		if (!$this->isAllowedUser() || !$this->isAjaxRequest())
		{
			throw new JAccessExceptionNotallowed(Text::_('JGLOBAL_AUTH_ACCESS_DENIED'), 403);
		}

		// Make sure only snoozable messages can be snoozed
		if ($this->currentMessage['snoozable'])
		{
			$this->params->set('last_snoozed_id', $this->currentMessage['id']);

			$this->saveParams();
		}
	}

	/**
	 * Return the texts to be displayed based on the time until we reach EOS
	 *
	 * @param   integer  $monthsUntilEOS  The months until we reach EOS
	 * @param   integer  $inverted        Have we surpassed the EOS date
	 *
	 * @return  array|bool  An array with the message to be displayed or false
	 *
	 * @since   3.10.0
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
				'groupText'     => 'PLG_QUICKICON_EOS310_GROUPNAME_EOS',
				'snoozable'     => false,
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
				'groupText'     => 'PLG_QUICKICON_EOS310_GROUPNAME_WARNING',
				'snoozable'     => true,
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
				'groupText'     => 'PLG_QUICKICON_EOS310_GROUPNAME_WARNING',
				'snoozable'     => true,
			);
		}

		// We still have 16 month to go, lets remind our users about the pre upgrade checker
		if ($monthsUntilEOS <= 16)
		{
			return array(
				'id'            => 2,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_02',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_02_SHORT',
				'messageType'   => 'info',
				'image'         => 'info-circle',
				'messageLink'   => 'https://docs.joomla.org/Special:MyLanguage/Pre-Update_Check',
				'groupText'     => 'PLG_QUICKICON_EOS310_GROUPNAME_INFO',
				'snoozable'     => true,
			);
		}

		// Lets start our messages 2 month after the initial release, still 22 month to go
		if ($monthsUntilEOS <= 22)
		{
			return array(
				'id'            => 1,
				'messageText'   => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_01',
				'quickiconText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_01_SHORT',
				'messageType'   => 'info',
				'image'         => 'info-circle',
				'messageLink'   => 'https://www.joomla.org/4/#features',
				'groupText'     => 'PLG_QUICKICON_EOS310_GROUPNAME_INFO',
				'snoozable'     => true,
			);
		}

		return false;
	}

	/**
	 * Determines if the message and quickicon should be displayed
	 *
	 * @return  boolean
	 *
	 * @since   3.10.0
	 */
	private function shouldDisplayMessage()
	{
		// Only on admin app
		if (!$this->app->isClient('administrator'))
		{
			return false;
		}

		// Only if authenticated
		if (Factory::getUser()->guest)
		{
			return false;
		}

		// Only on HTML documents
		if ($this->app->getDocument()->getType() !== 'html')
		{
			return false;
		}

		// Only on full page requests
		if ($this->app->input->getCmd('tmpl', 'index') === 'component')
		{
			return false;
		}

		// Only to com_cpanel
		if ($this->app->input->get('option') !== 'com_cpanel')
		{
			return false;
		}

		// Don't show anything in 4.0
		if (version_compare(JVERSION, '4.0', '>='))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check valid AJAX request
	 *
	 * @return  boolean
	 *
	 * @since   3.10.0
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
	 * @since   3.10.0
	 */
	private function isAllowedUser()
	{
		return Factory::getUser()->authorise('core.login.admin');
	}

	/**
	 * Save the plugin parameters
	 *
	 * @return  boolean
	 *
	 * @since   3.10.0
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
	 * @since   3.10.0
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
