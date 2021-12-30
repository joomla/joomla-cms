<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\View\Link;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a redirect link.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The active item
	 *
	 * @var  object
	 */
	protected $item;

	/**
	 * The Form object
	 *
	 * @var  \Joomla\CMS\Form\Form
	 */
	protected $form;

	/**
	 * The model state
	 *
	 * @var   \Joomla\CMS\Object\CMSObject
	 */
	protected $state;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  False if unsuccessful, otherwise void.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		$canDo = ContentHelper::getActions('com_redirect');

		ToolbarHelper::title($isNew ? Text::_('COM_REDIRECT_MANAGER_LINK_NEW') : Text::_('COM_REDIRECT_MANAGER_LINK_EDIT'), 'map-signs redirect');

		$toolbarButtons = [];

		// If not checked out, can save the item.
		if ($canDo->get('core.edit'))
		{
			ToolbarHelper::apply('link.apply');
			$toolbarButtons[] = ['save', 'link.save'];
		}

		/**
		 * This component does not support Save as Copy due to uniqueness checks.
		 * While it can be done, it causes too much confusion if the user does
		 * not change the Old URL.
		 */
		if ($canDo->get('core.edit') && $canDo->get('core.create'))
		{
			$toolbarButtons[] = ['save2new', 'link.save2new'];
		}

		ToolbarHelper::saveGroup(
			$toolbarButtons,
			'btn-success'
		);

		if (empty($this->item->id))
		{
			ToolbarHelper::cancel('link.cancel');
		}
		else
		{
			ToolbarHelper::cancel('link.cancel', 'JTOOLBAR_CLOSE');
		}

		ToolbarHelper::help('Redirects:_New_or_Edit');
	}
}
