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
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\User\User;
use Joomla\Component\Users\Administrator\Helper\Mfa;
use Joomla\Component\Users\Site\Model\ProfileModel;
use Joomla\Database\DatabaseDriver;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

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
     * @var  \Joomla\Registry\Registry
     */
    protected $state;

    /**
     * An instance of DatabaseDriver.
     *
     * @var    DatabaseDriver
     * @since  3.6.3
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Will be removed without replacement use database from the container instead
     *              Example: Factory::getContainer()->get(DatabaseInterface::class);
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

        /** @var ProfileModel $model */
        $model                    = $this->getModel();
        $this->data               = $model->getData();
        $this->form               = $model->getForm();
        $this->state              = $model->getState();
        $this->params             = $this->state->get('params');
        $this->mfaConfigurationUI = Mfa::getConfigurationInterface($user);
        $this->db                 = Factory::getDbo();

        // Check for errors.
        if (\count($errors = $model->getErrors())) {
            throw new GenericDataException(implode("\n", $errors), 500);
        }

        // View also takes responsibility for checking if the user logged in with remember me.
        if (isset($user->cookieLogin) && !empty($user->cookieLogin)) {
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
        Factory::getApplication()->triggerEvent('onContentPrepare', ['com_users.user', &$this->data, &$this->data->params, 0]);
        unset($this->data->text);

        // Check for layout from menu item.
        $active = Factory::getApplication()->getMenu()->getActive();

        if (
            $active && isset($active->query['layout'], $active->query['option'])
            && $active->query['option'] === 'com_users'
            && isset($active->query['view']) && $active->query['view'] === 'profile'
        ) {
            $this->setLayout($active->query['layout']);
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
            $this->getDocument()->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $this->params->get('robots'));
        }
    }
}
