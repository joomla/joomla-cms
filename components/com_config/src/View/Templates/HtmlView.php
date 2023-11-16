<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Templates;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Config\Administrator\Controller\RequestController;
use Joomla\Component\Templates\Administrator\View\Style\JsonView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View to edit a template style.
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The data to be displayed in the form
     *
     * @var   array
     *
     * @since 3.2
     */
    public $item;

    /**
     * The form object
     *
     * @var   Form
     *
     * @since 3.2
     */
    public $form;

    /**
     * Is the current user a super administrator?
     *
     * @var   boolean
     *
     * @since 3.2
     */
    protected $userIsSuperAdmin;

    /**
     * The page class suffix
     *
     * @var    string
     *
     * @since  4.0.0
     */
    protected $pageclass_sfx = '';

    /**
     * The page parameters
     *
     * @var    \Joomla\Registry\Registry|null
     *
     * @since  4.0.0
     */
    protected $params = null;

    /**
     * Method to render the view.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function display($tpl = null)
    {
        $user                   = $this->getCurrentUser();
        $this->userIsSuperAdmin = $user->authorise('core.admin');

        $app   = Factory::getApplication();

        $app->getInput()->set('id', $app->getTemplate(true)->id);

        /** @var MVCFactory $factory */
        $factory = $app->bootComponent('com_templates')->getMVCFactory();

        /** @var JsonView $view */
        $view = $factory->createView('Style', 'Administrator', 'Json');
        $view->setModel($factory->createModel('Style', 'Administrator'), true);
        $view->setLanguage($app->getLanguage());

        $view->document = $this->getDocument();

        $json = $view->display();

        // Execute backend controller
        $serviceData = json_decode($json, true);

        // Access backend com_config
        $requestController = new RequestController();

        // Execute backend controller
        $configData = json_decode($requestController->getJson(), true);

        $data = array_merge($configData, $serviceData);

        /** @var Form $form */
        $form = $this->getForm();

        if ($form) {
            $form->bind($data);
        }

        $this->form = $form;

        $this->data = $serviceData;

        $this->_prepareDocument();

        parent::display($tpl);
    }

    /**
     * Prepares the document.
     *
     * @return  void
     *
     * @since   4.0.0
     */
    protected function _prepareDocument()
    {
        $params = Factory::getApplication()->getParams();

        // Because the application sets a default page title, we need to get it
        // right from the menu item itself
        $this->setDocumentTitle($params->get('page_title', ''));

        if ($params->get('menu-meta_description')) {
            $this->getDocument()->setDescription($params->get('menu-meta_description'));
        }

        if ($params->get('robots')) {
            $this->getDocument()->setMetaData('robots', $params->get('robots'));
        }

        // Escape strings for HTML output
        $this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx', ''));
        $this->params        = &$params;
    }
}
