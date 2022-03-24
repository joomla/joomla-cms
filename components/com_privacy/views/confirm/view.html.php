<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Request confirmation view class
 *
 * @since  3.9.0
 */
class PrivacyViewConfirm extends JViewLegacy
{
	/**
	 * The form object
	 *
	 * @var    JForm
	 * @since  3.9.0
	 */
	protected $form;

	/**
	 * The CSS class suffix to append to the view container
	 *
	 * @var    string
	 * @since  3.9.0
	 */
	protected $pageclass_sfx;

	/**
	 * The view parameters
	 *
	 * @var    Registry
	 * @since  3.9.0
	 */
	protected $params;

	/**
	 * The state information
	 *
	 * @var    JObject
	 * @since  3.9.0
	 */
	protected $state;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @see     JViewLegacy::loadTemplate()
	 * @since   3.9.0
	 * @throws  Exception
	 */
	public function display($tpl = null)
	{
		// Initialise variables.
		$this->form   = $this->get('Form');
		$this->state  = $this->get('State');
		$this->params = $this->state->params;

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new Exception(implode("\n", $errors), 500);
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''), ENT_COMPAT, 'UTF-8');

		$this->prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
		$title = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_PRIVACY_VIEW_CONFIRM_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
	}
}
