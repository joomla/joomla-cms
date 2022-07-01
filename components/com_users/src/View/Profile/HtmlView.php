<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Site\View\Profile;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\Mfa;
use Joomla\Database\DatabaseDriver;

/**
 * Profile view class for Users.
 *
 * @since  1.6
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Profile form data for the user
     *
     * @var  User
     */
    protected $data;

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
     * @var  CMSObject
     */
    protected $state;

    /**
     * An instance of DatabaseDriver.
     *
     * @var    DatabaseDriver
     * @since  3.6.3
     *
     * @deprecated  5.0 Will be removed without replacement
     */
    protected $db;

    /**
     * The page class suffix
     *
     * @var    string
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * The Multi-factor Authentication configuration interface for the user.
     *
     * @var   string|null
     * @since 4.2.0
     */
    protected $mfaConfigurationUI;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void|boolean
     *
     * @since   1.6
     * @throws  \Exception
     */
    public function display($tpl = null)
    {
        $user = $this->getCurrentUser();

        // Get the view data.
        $this->data               = $this->get('Data');
        $this->form               = $this->getModel()->getForm(new CMSObject(['id' => $user->id]));
        $this->state              = $this->get('State');
        $this->params             = $this->state->get('params');
        $this->mfaConfigurationUI = Mfa::getConfigurationInterface($user);
        $this->db                 = Factory::getDbo();

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // View also takes responsibility for checking if the user logged in with remember me.
        $cookieLogin = $user->get('cookieLogin');

        if (!empty($cookieLogin)) {
            // If so, the user must login to edit the password and other data.
            // What should happen here? Should we force a logout which destroys the cookies?
            $app = Factory::getApplication();
            $app->enqueueMessage(Text::_('JGLOBAL_REMEMBER_MUST_LOGIN'), 'message');
            $app->redirect(Route::_('index.php?option=com_users&view=login', false));

            return false;
        }

        // Check if a user was found.
        if (!$this->data->id) {
            throw new \Exception(Text::_('JERROR_USERS_PROFILE_NOT_FOUND'), 404);
        }

        PluginHelper::importPlugin('content');
        $this->data->text = '';
        Factory::getApplication()->triggerEvent('onContentPrepare', array ('com_users.user', &$this->data, &$this->data->params, 0));
        unset($this->data->text);

        // Check for layout from menu item.
        $query = Factory::getApplication()->getMenu()->getActive()->query;

        if (
            isset($query['layout']) && isset($query['option']) && $query['option'] === 'com_users'
            && isset($query['view']) && $query['view'] === 'profile'
        ) {
            $this->setLayout($query['layout']);
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx', ''));

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
        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = Factory::getApplication()->getMenu()->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $this->getCurrentUser()->name));
        } else {
            $this->params->def('page_heading', Text::_('COM_USERS_PROFILE'));
        }

        $this->setDocumentTitle($this->params->get('page_title', ''));

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
