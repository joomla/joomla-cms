<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Content\Administrator\Field;

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\PluginHelper;

\JFormHelper::loadFieldClass('radio');

/**
 * Voteradio Field class.
 *
 * @since  3.8.0
 */
class VoteradioField extends \JFormFieldRadio
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
	 * @return string The field label
	 *
	 * @throws \Exception
	 *
	 * @since  3.8.2
	 */
	public function getLabel()
	{
		// Requires vote plugin enabled
		return \JPluginHelper::isEnabled('content', 'vote') ? parent::getLabel() : null;
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
		return PluginHelper::isEnabled('content', 'vote') ? parent::getOptions() : array();
	}
}
