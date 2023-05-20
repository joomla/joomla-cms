<?php

/**
 * @package       JED
 *
 * @subpackage    TICKETS
 *
 * @copyright     (C) 2022 Open Source Matters, Inc.  <https://www.joomla.org>
 * @license       GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Jed\Component\Jed\Site\View\Ticketmessageform;

// No direct access
// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use Exception;
use Jed\Component\Jed\Site\Helper\JedHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

/**
 * View class for a list of Jed.
 *
 * @since  4.0.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * The model state
     *
     * @var    CMSObject
     * @since  4.0.0
     */
    protected CMSObject $state;
    /**
     * The item object
     *
     * @var    object
     * @since  4.0.0
     */
    protected mixed $item;
    /**
     * The Form object
     *
     * @var    Form
     *
     * @since  4.0.0
     */
    protected mixed $form;
    /**
     * The components parameters
     *
     * @var  object
     *
     * @since 4.0.0
     */
    protected mixed $params;
    /**
     * Does user have permission to save form
     *
     * @var    bool
     * @since  4.0.0
     */
    protected $canSave;

    /**
     * Prepares the document
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    protected function prepareDocument()
    {
        $app   = Factory::getApplication();
        $menus = $app->getMenu();
        $title = null;

        // Because the application sets a default page title,
        // we need to get it from the menu item itself
        $menu = $menus->getActive();

        if ($menu) {
            $this->params->def('page_heading', $this->params->get('page_title', $menu->title));
        } else {
            $this->params->def('page_heading', Text::_('COM_JED_DEFAULT_PAGE_TITLE'));
        }

        $title = $this->params->get('page_title', '');

        if (empty($title)) {
            $title = $app->get('sitename');
        } elseif ($app->get('sitename_pagetitles', 0) == 1) {
            $title = Text::sprintf('JPAGETITLE', $app->get('sitename'), $title);
        } elseif ($app->get('sitename_pagetitles', 0) == 2) {
            $title = Text::sprintf('JPAGETITLE', $title, $app->get('sitename'));
        }

        $this->document->setTitle($title);

        if ($this->params->get('menu-meta_description')) {
            $this->document->setDescription($this->params->get('menu-meta_description'));
        }

        if ($this->params->get('menu-meta_keywords')) {
            $this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
        }

        if ($this->params->get('robots')) {
            $this->document->setMetadata('robots', $this->params->get('robots'));
        }
    }

    /**
     * Display the view
     *
     * @param   string  $tpl  Template name
     *
     * @return void
     *
     * @since 4.0.0
     *
     * @throws Exception
     */
    public function display($tpl = null)
    {
        $app  = Factory::getApplication();
        $user = JedHelper::getUser();

        $this->state   = $this->get('State');
        $this->item    = $this->get('Item');
        $this->params  = $app->getParams('com_jed');
        $this->canSave = $this->get('CanSave');
        $this->form    = $this->get('Form');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            throw new Exception(implode("\n", $errors));
        }


        $this->prepareDocument();

        parent::display($tpl);
    }
}
