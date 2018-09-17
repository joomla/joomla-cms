<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Field;

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;

/**
 * Modules Position field.
 *
 * @since  3.4.2
 */
class ModulesPositionField extends \Joomla\CMS\Form\Field\TextField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.4.2
	 */
	protected $type = 'ModulesPosition';

	/**
	 * Cached options
	 *
	 * @var    array
	 * @since  __DEPLOY_VERSION__
	 */
	protected $options = null;

	/**
	 * Name of the layout being used to render the field
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $layout = 'joomla.form.field.modulesposition';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getInput()
	{
		$data = $this->getLayoutData();

		$clientId  = Factory::getApplication()->input->get('client_id', 0, 'int');
		$positions = HTMLHelper::_('modules.positions', $clientId, 1, $this->value);

		$data['client']    = $clientId;
		$data['positions'] = $positions;

		return $this->getRenderer($this->layout)->render($data);
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   3.4.2
	 * /
	public function getOptions()
	{
		if ($this->options === null)
		{
			$clientId = Factory::getApplication()->input->get('client_id', 0, 'int');
			$options  = ModulesHelper::getPositions($clientId);

			$positions = HTMLHelper::_('modules.positions', $clientId, 1, $this->value);


			$this->options = $positions; //array_merge(parent::getOptions(), $options);
		}

		return $this->options;
	}//*/
}
