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
class plgQuickiconEos310 extends JPlugin
{
	/**
	 * The EOS date for 3.10 to be updated at the stable release
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	const EOS_DATE = '07.06.2024';

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
	 * @param   $context  The calling context
	 *
	 * @return  array|void  A list of icon definition associative arrays, consisting of the
	 *                     keys link, image, text and access, or void.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onGetIcons($context)
	{
		$diff = Factory::getDate()->diff(Factory::getDate(static::EOS_DATE));
		$monthsUntilEOS = floor($diff->days / 30.417);

		$messageInfo = $this->getMessageInfo($monthsUntilEOS, $diff->invert);

		// No messages yet.
		if (!$messageInfo)
		{
			return;
		}

		// Build the message to be displayed
		$messageText = Text::sprintf(
			$messageInfo['messageText'],
			static::EOS_DATE,
			$messageInfo['messageLink'],
		);

		// Check whether we show this message above the cpanel
		if ($messageInfo['showMainMessage'] && $this->app->input->get('option') == 'com_cpanel')
		{
			$this->app->enqueueMessage(
				$messageText,
				$messageInfo['messageType'],
			);
		}

		// Check whether we show this message on the quick icon side.
		if ($messageInfo['showQuickIconMessage'])
		{
			return array(array(
				'link'  => $messageInfo['messageLink'],
				'image' => $messageInfo['image'],
				'text'  => $messageText,
				'id'    => 'plg_quickicon_eos310',
				'group' => $messageInfo['groupText'],
			));
		}
	}

	/**
	 * Return the texts to be displayed based on the time until we reach EOS
	 *
	 * @param  $monthsUntilEOS  The months until we reach EOS
	 * @param  $inverted        Have we surpassed the EOS date
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
				'messageText'          => 'PLG_QUICKICON_EOS310_MESSAGE_ERROR_SUPPORT_ENDED',
				'messageType'          => 'error',
				'image'                => 'minus-circle',
				'messageLink'          => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'            => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_EOS'),
				'showMainMessage'      => true,
				'showQuickIconMessage' => true,
			);
		}

		// The security support is ending in 6 months
		if ($monthsUntilEOS <= 6)
		{
			return array(
				'messageText'          => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SUPPORT_ENDING',
				'messageType'          => 'warning',
				'image'                => 'warning-circle',
				'messageLink'          => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'            => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_WARNING'),
				'showMainMessage'      => true,
				'showQuickIconMessage' => true,
			);
		}

		// We are in security only mode now, 12 month to go from now on
		if ($monthsUntilEOS <= 12)
		{
			return array(
				'messageText'          => 'PLG_QUICKICON_EOS310_MESSAGE_WARNING_SECURITY_ONLY',
				'messageType'          => 'warning',
				'image'                => 'warning-circle',
				'messageLink'          => 'https://docs.joomla.org/Special:MyLanguage/Planning_for_Mini-Migration_-_Joomla_3.10.x_to_4.x',
				'groupText'            => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_WARNING'),
				'showMainMessage'      => false,
				'showQuickIconMessage' => true,
			);
		}

		// We still have 16 month to go, lets remind them about the pre upgrade checker
		if ($monthsUntilEOS <= 16)
		{
			return array(
				'messageText'          => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_02',
				'messageType'          => 'info',
				'image'                => 'info-circle',
				'messageLink'          => 'https://docs.joomla.org/Special:MyLanguage/Pre-Update_Check',
				'groupText'            => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_INFO'),
				'showMainMessage'      => false,
				'showQuickIconMessage' => true,
			);
		}

		// Lets start our messages 2 month after the initial release, still 22 month to go
		if ($monthsUntilEOS <= 22)
		{
			return array(
				'messageText'          => 'PLG_QUICKICON_EOS310_MESSAGE_INFO_MESSAGE_01',
				'messageType'          => 'info',
				'image'                => 'info-circle',
				'messageLink'          => 'https://joomla.org/4',
				'groupText'            => Text::_('PLG_QUICKICON_EOS310_GROUPNAME_INFO'),
				'showMainMessage'      => false,
				'showQuickIconMessage' => true,
			);
		}

		return false;
	}
}
