<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mails
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Mails\Administrator\View\Template;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a mail template.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The Form object
	 *
	 * @var  \Joomla\CMS\Form\Form
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var  CMSObject
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var  object
	 */
	protected $state;

	/**
	 * The template data
	 *
	 * @var  array
	 */
	protected $templateData;

	/**
	 * Master data for the mail template
	 *
	 * @var  CMSObject
	 */
	protected $master;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function display($tpl = null)
	{
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->master = $this->get('Master');
		$this->form = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		list($component, $template_id) = explode('.', $this->item->template_id, 2);
		$fields = array('subject', 'body', 'htmlbody');
		$this->templateData = array();
		$language = Factory::getLanguage();
		$language->load($component, JPATH_SITE, $this->item->language, true);
		$language->load($component, JPATH_ADMINISTRATOR, $this->item->language, true);

		foreach ($fields as $field)
		{
			$this->templateData[$field] = (object) ['master' => $this->master->$field, 'translated' => Text::_($this->master->$field)];

			if (is_null($this->item->$field)
				|| $this->item->$field == ''
				|| $this->item->$field == $this->master->$field)
			{
				$this->item->$field = $this->master->$field;
				$this->form->setFieldAttribute($field, 'disabled', 'true');
				$this->form->setValue($field, null, $this->master->$field);
			}
			else
			{
				$this->form->setValue($field . '_switcher', null, 1);
			}
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title(
			Text::_('COM_MAILS_PAGE_EDIT_MAIL'),
			'pencil-2 article-add'
		);

		$saveGroup = $toolbar->dropdownButton('save-group');

		$saveGroup->configure(
			function (Toolbar $childBar)
			{
				$childBar->apply('template.apply');
				$childBar->save('template.save');
			}
		);

		$toolbar->cancel('template.cancel', 'JTOOLBAR_CLOSE');

		$toolbar->divider();
		$toolbar->help('JHELP_CONFIG_MAIL_MANAGER_EDIT');
	}
}
