<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

JFormHelper::loadFieldClass('list');

require_once __DIR__ . '/../../helpers/modules.php';

/**
 * ModulesPosition Field class for the Joomla Framework.
 *
 * @since  3.4.2
 */
class JFormFieldModulesPosition extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since   1.6
	 */
	protected $type = 'ModulesPosition';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   1.6
	 */
	public function getOptions()
	{
		$client_id = $this->form->getValue('client_id', 'filter');
		$options = ModulesHelper::getPositions($client_id);
		return array_merge(parent::getOptions(), $options);
	}
}
