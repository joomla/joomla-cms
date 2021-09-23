<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('radio');

/**
 * Voteradio Field class.
 *
 * @since  3.8.0
 */
class JFormFieldVoteradio extends JFormFieldRadio
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.7.1
	 */
	protected $type = 'Voteradio';

	/**
	 * Method to get the field Label.
	 *
	 * @return array The field label objects.
	 *
	 * @throws \Exception
	 *
	 * @since  3.8.2
	 */
	public function getLabel()
	{
		// Requires vote plugin enabled
		return JPluginHelper::isEnabled('content', 'vote') ? parent::getLabel() : null;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return array The field option objects.
	 *
	 * @throws \Exception
	 *
	 * @since  3.7.1
	 */
	public function getOptions()
	{
		// Requires vote plugin enabled
		return JPluginHelper::isEnabled('content', 'vote') ? parent::getOptions() : array();
	}
}
