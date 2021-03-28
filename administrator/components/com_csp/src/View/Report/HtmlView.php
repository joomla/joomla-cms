<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\View\Report;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * Report view class for the Csp package.
 *
 * @since  __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The record item
	 *
	 * @var    \stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    CMSObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * Form object for the item
	 *
	 * @var    Form
	 * @since  __DEPLOY_VERSION__
	 */
	public $form;

	/**
	 * Permissions for com_csp
	 *
	 * @var CMSObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $canDo;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');
		$this->form  = $this->get('Form');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new GenericDataException(implode("\n", $errors), 500);
		}

		$this->addToolbar();

		return parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$user       = Factory::getUser();

		$isNew      = ($this->item->id == 0);
		$checkedOut = !($isNew || $this->item->checked_out == $user->id);

		$this->canDo = ContentHelper::getActions('com_csp');

		ToolbarHelper::title(Text::_('COM_CSP_REPORTS_' . ($isNew ? 'ADD_REPORT' : 'EDIT_REPORT')), 'shield-alt');

		$toolbar = Toolbar::getInstance();

		if (($isNew && $this->canDo->get('core.create')) || (!$isNew && !$checkedOut && $this->canDo->get('core.edit')))
		{
			$toolbar->apply('report.apply');

			$saveGroup = $toolbar->dropdownButton('save-group');

			$canDo = $this->canDo;

			$saveGroup->configure(
				function (Toolbar $childBar) use ($canDo, $isNew)
				{
					$childBar->save('report.save');

					if ($canDo->get('core.create'))
					{
						$childBar->save2new('report.save2new');

						if (!$isNew)
						{
							$childBar->save2copy('report.save2copy');
						}
					}
				}
			);
		}

		$toolbar->cancel('report.cancel', 'JTOOLBAR_CLOSE');

		$toolbar->divider();
		ToolbarHelper::help('JHELP_COMPONENTS_CSP_REPORTS');
	}
}
