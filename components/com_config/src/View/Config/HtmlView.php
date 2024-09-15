<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Site\View\Config;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\Component\Config\Administrator\Controller\RequestController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View for the global configuration
 *
 * @since  3.2
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The form object
     *
     * @var   \Joomla\CMS\Form\Form
     *
     * @since 3.2
     */
    public $form;

    /**
     * The data to be displayed in the form
     *
     * @var   array
     *
     * @since 3.2
     */
    public $data;

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
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function display($tpl = null)
    {
        $user                   = $this->getCurrentUser();
        $this->userIsSuperAdmin = $user->authorise('core.admin');

        // Access backend com_config
        $requestController = new RequestController();

        // Execute backend controller
        $serviceData = json_decode($requestController->getJson(), true);

        $form = $this->getForm();

        if ($form) {
            $form->bind($serviceData);
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
