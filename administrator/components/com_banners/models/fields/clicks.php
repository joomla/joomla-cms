<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Clicks Field class for the Joomla Framework.
 *
 * @since  1.6
 */
class JFormFieldClicks extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'Clicks';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 *
	 * @since   1.6
	 */
	protected function getInput()
	{
		// This will need to change with the new renderer???
		$layout = new JLayoutFile('components.com_banners.fields.click');

		return $layout->render(
			array(
				'id' => $this->id,
				'name' => $this->name,
				'value' => $this->value
			)
		);
	}
}
