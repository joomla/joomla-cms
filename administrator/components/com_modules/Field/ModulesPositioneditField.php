<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Edit Modules Position field.
 *
 * @since  4.0.0
 */
class ModulesPositioneditField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $type = 'ModulesPositionedit';

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $layout = 'joomla.form.field.modulespositionedit';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   4.0.0
	 */
	protected function getInput()
	{
		$data = $this->getLayoutData();

		$clientId  = Factory::getApplication()->getUserState('com_modules.modules.client_id', 0);
		$positions = HTMLHelper::_('modules.positions', $clientId, 1, $this->value);

		$data['client']    = $clientId;
		$data['positions'] = $positions;

		$renderer = $this->getRenderer($this->layout);
		$renderer->setComponent('com_modules');
		$renderer->setClient(1);

		return $renderer->render($data);
	}
}
