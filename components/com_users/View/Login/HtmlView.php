<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Users\Site\View\Login;

defined('_JEXEC') or die;

use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\User\User;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Login view class for Users.
 *
 * @since  1.5
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * The \JForm object
	 *
	 * @var  \JForm
	 */
	protected $form;

	/**
	 * The page parameters
	 *
	 * @var  \Joomla\Registry\Registry|null
	 */
	protected $params;

	/**
	 * The model state
	 *
	 * @var  CMSObject
	 */
	protected $state;

	/**
	 * The logged in user
	 *
	 * @var  User
	 */
	protected $user;

	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $pageclass_sfx = '';

	/**
	 * Array containing the available two factor authentication methods
	 *
	 * @var    string
	 * @since  4.0.0
	 */
	protected $tfa = '';

	/**
	 * Method to display the view.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.5
	 * @throws  \Exception
	 */
	public function display($tpl = null)
	{
		// Get the view data.
		$this->user   = Factory::getUser();
		$this->form   = $this->get('Form');
		$this->state  = $this->get('State');
		$this->params = $this->state->get('params');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \JViewGenericdataexception(implode("\n", $errors), 500);
		}

		// Check for layout override
		$active = Factory::getApplication()->getMenu()->getActive();

		if (isset($active->query['layout']))
		{
			$this->setLayout($active->query['layout']);
		}

		$tfa = AuthenticationHelper::getTwoFactorMethods();
		$this->tfa = is_array($tfa) && count($tfa) > 1;

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'), ENT_COMPAT, 'UTF-8');

		$this->prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  \Exception
	 */
	protected function prepareDocument()
	{
		$app   = Factory::getApplication();
		$menus = $app->getMenu();
		$user  = Factory::getUser();
		$login = $user->get('guest') ? true : false;
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
			$this->params->def('page_heading', $login ? Text::_('JLOGIN') : Text::_('JLOGOUT'));
		}

		$title = $this->params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetaData('robots', $this->params->get('robots'));
		}
	}
}
