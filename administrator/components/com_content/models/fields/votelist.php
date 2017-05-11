<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Votelist Field class.
 *
 * @since  __DEPLOY_VERSION__
 */
class JFormFieldVotelist extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'Votelist';

	/**
	 * Method to get the field options.
	 *
	 * @return array The field option objects.
	 *
	 * @throws \Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function getOptions()
	{
		// Requires vote plugin enabled
		return JPluginHelper::isEnabled('content', 'vote') ? parent::getOptions() : array();
	}
}
