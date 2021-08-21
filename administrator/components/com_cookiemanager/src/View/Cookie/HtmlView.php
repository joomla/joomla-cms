<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cookiemanager
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cookiemanager\Administrator\View\Cookie;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;

/**
 * View to edit a cookie.
 *
 * @since   __DEPLOY_VERSION__
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var    \JForm
	 * @since  __DEPLOY_VERSION__
	 */
	protected $form;

	/**
	 * The active item
	 *
	 * @var    object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $item;

	/**
	 * The model state
	 *
	 * @var    object
	 * @since  __DEPLOY_VERSION__
	 */
	protected $state;

	/**
	 * The actions the user is authorised to perform
	 *
	 * @var    \JObject
	 * @since  __DEPLOY_VERSION__
	 */
	protected $canDo;

	/**
	 * Display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  void
	 *
	 * @throws \Exception
	 * @since   __DEPLOY_VERSION__
	 */
	public function display($tpl = null)
	{
		$this->form  = $this->get('Form');
		$this->item  = $this->get('Item');
		$this->state = $this->get('State');

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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function addToolbar()
	{
		Factory::getApplication()->input->set('hidemainmenu', true);

		$canDo = ContentHelper::getActions('com_cookiemanager');
		$isNew      = ($this->item->id == 0);

		// Get the toolbar object instance
		$toolbar = Toolbar::getInstance();

		ToolbarHelper::title($isNew ? Text::_('COM_COOKIEMANAGER_COOKIE_NEW') : Text::_('COM_COOKIEMANAGER_COOKIE_EDIT'), 'cookie-bite');

		if ($canDo->get('core.create'))
		{
			$toolbar->apply('cookie.apply');
			$toolbar->save('cookie.save');
		}

		$toolbar->cancel('cookie.cancel');

		ToolbarHelper::help('JHELP_COMPONENTS_COOKIEMANAGER_COOKIES_EDIT');
	}
}
