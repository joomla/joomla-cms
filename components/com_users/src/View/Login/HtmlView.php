<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Login;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\AuthenticationHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\User\User;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Login view class for Users.
 *
 * @since  1.5
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
     * The page parameters
     *
     * @var  \Joomla\Registry\Registry|null
     */
    protected $params;

    /**
     * The model state
     *
     * @var  \Joomla\Registry\Registry
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
     * No longer used
     *
     * @var    boolean
     * @since  4.0.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Will be removed without replacement
     */
    protected $tfa = false;

    /**
     * Additional buttons to show on the login page
     *
     * @var    array
     * @since  4.0.0
     */
    protected $extraButtons = [];

    /**
     * Method to display the view.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   1.5
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        // Get the view data.
        $this->user   = $this->getCurrentUser();
        $this->form   = $this->get('Form');
        $this->state  = $this->get('State');
        $this->params = $this->state->get('params');

        // Check for errors.
        if (\count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // Check for layout override
        $active = Factory::getApplication()->getMenu()->getActive();

        if (isset($active->query['layout'])) {
            $this->setLayout($active->query['layout']);
        }

        $this->extraButtons = AuthenticationHelper::getLoginButtons('com-users-login__form');

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''), ENT_COMPAT, 'UTF-8');

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
        $login = (bool) $this->getCurrentUser()->guest;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', $login ? Text::_('JLOGIN') : Text::_('JLOGOUT'));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
