<?php
/**
 * @copyright	Copyright (C) 2021 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Joomla! update notification plugin
 *
 * @since  __DEPLOY_VERSION__
 */
class plgQuickiconEos310 extends JPlugin
{
	/**
	 * Indicates sending statistics is always allowed.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	const EOS_DATE = '28.05.2021';

	/**
	 * Indicates sending statistics is always allowed.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	const SNOOZE_INFO = 1;

	/**
	 * Indicates sending statistics is only allowed one time.
	 *
	 * @var    integer
	 * @since  __DEPLOY_VERSION__
	 */
	const SNOOZE_WARNING = 2;

	/**
	 * Application object
	 *
	 * @var    JApplicationCms
	 * @since  __DEPLOY_VERSION__
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    JDatabaseDriver
	 * @since  __DEPLOY_VERSION__
	 */
	protected $db;

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
	 * @param  $context  The calling context
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since        __DEPLOY_VERSION__
	 */
	public function onGetIcons($context)
	{
		$diff = Factory::getDate()->diff(Factory::getDate(static::EOS_DATE));
		$weeksUntilEOS = floor($diff->days / 7);

		$messageInfo = $this->getMessageInfo($diff->m, $diff->invert);

		$messageText = Text::sprintf(
			$messageInfo['messageText'],
			static::EOS_DATE,
			$messageInfo['messageLink'],
		);

		if ($messageInfo['showMainMessage'] && $this->app->input->get('option') == 'com_cpanel')
		{
			$this->app->enqueueMessage(
				$messageText,
				$messageInfo['messageType'],
			);
		}

		if ($messageInfo['showQuickIconMessage'])
		{
			return array(array(
				'link'  => $messageInfo['messageLink'],
				'image' => 'info-circle',
				'text'  => $messageText,
				'id'    => 'plg_quickicon_eos310',
				'group' => Text::_('PLG_QUICKICON_EOS310_GROUP')
			));
		}
	}

	/**
	 * Return the texts to be displayed based on the time until we reach EOS
	 *
	 * @param  $monthsUntilEOS  The months until we reach EOS
	 * @param  $inverted        Have we surpassed the EOS date
	 *
	 * @return  array  An array with the message do be displayed
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getMessageInfo($monthsUntilEOS, $inverted)
	{
		// The EOS date has passed - Support has ended
		if ($inverted === 1)
		{
			return array(
				'messageText' => 'PLG_QUICKICON_EOS310_MESSAGE_ERROR_SUPPORT_ENDED',
				'messageType' => 'danger',
				'messageLink' => 'https://docs.joomla.org/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'showMainMessage' => true,
				'showQuickIconMessage' => true,

			);
		}

		// Lets start our messages 2 month after the initial release, still 22 month to go
		if ($monthsUntilEOS <= 22)
		{
			return array(
				'messageText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_01',
				'messageType' => 'info',
				'messageLink' => 'https://joomla.org/4',
				'showMainMessage' => false,
				'showQuickIconMessage' => true,

			);
		}

		// We still have 16 month to go, lets remind them about the pre upgrade checker
		if ($monthsUntilEOS <= 16)
		{
			return array(
				'messageText' => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_02',
				'messageType' => 'info',
				'messageLink' => 'https://docs.joomla.org/Pre-Update_Check',
				'showMainMessage' => false,
				'showQuickIconMessage' => true,
			);
		}

		// We are in security only mode now, 12 month to go from now on
		if ($monthsUntilEOS <= 12)
		{
			return array(
				'messageText' => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SECURITY_ONLY',
				'messageType' => 'warning',
				'messageLink' => 'https://docs.joomla.org/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'showMainMessage' => true,
				'showQuickIconMessage' => true,
			);
		}

		// The security support is ending in 6 months
		if ($monthsUntilEOS <= 6)
		{
			return array(
				'messageText' => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SUPPORT_ENDING',
				'messageType' => 'warning',
				'messageLink' => 'https://docs.joomla.org/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'showMainMessage' => true,
				'showQuickIconMessage' => true,
			);
		}

		return array(
			'showMainMessage' => false,
			'showQuickIconMessage' => false,
		);
	}
}
