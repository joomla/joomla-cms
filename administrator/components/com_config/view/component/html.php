<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

class ConfigViewComponentHtml extends JViewItem
{
	public function render($tpl = null)
	{
		$model = $this->getModel();
		$this->components = $model->getList();
		// Apparently I need to load the main language file too.
		// Would be nice if configuration language strings were in the .sys.ini
		ConfigHelperConfig::loadLanguageForComponents($this->components, $this->config['component']);

		$user = JFactory::getUser();
		$this->userIsSuperAdmin = $user->authorise('core.admin');
		$this->addToolbar();

		//Last thing I want to do is insure we have a proper formURL
		$this->formUrl .='&component='.$this->config['component'];
		return parent::render($tpl);
	}


	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function addToolbar()
	{
		// No toolbars in the front-end =^(
		if (JFactory::getApplication()->isSite())
		{
			return;
		}

		JToolbarHelper::title(JText::_($this->config['component'] . '_configuration'), 'equalizer config');
		JToolbarHelper::apply('store');
		JToolbarHelper::save('store.cancel');
		JToolbarHelper::divider();
		JToolbarHelper::cancel('cancel');
		JToolbarHelper::divider();
		JToolbarHelper::help('JHELP_COMPONENTS_' . $this->config['component'] . '_OPTIONS');
	}

	/**
	 * This is a special case, because we are not using only using the item params, so overriding here allows us
	 * to skip the user session security check.
	 * @param $model
	 */
	protected function prepareForm($model)
	{
		if(isset($this->item->params))
		{
			$this->form->bind($this->item->params);
		}

		$this->fieldsets = $this->form->getFieldsets();

		if(!$model->allowAction('core.admin') && isset($this->fieldsets['permissions']))
		{
			unset($this->fieldsets['permissions']);
		}
	}

	public function canView()
	{
		$model = $this->getModel();
		if($model->allowAction('core.admin'))
		{
			return true;
		}
		throw new ErrorException(JText::_('JERROR_ALERTNOAUTHOR'), 404);
	}
}
