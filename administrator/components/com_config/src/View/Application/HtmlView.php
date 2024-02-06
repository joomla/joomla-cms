<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\View\Application;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;

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
     * The model state
     *
     * @var    \Joomla\CMS\Object\CMSObject
     * @since  3.2
     */
    public $state;

    /**
     * The form object
     *
     * @var    \Joomla\CMS\Form\Form
     * @since  3.2
     */
    public $form;

    /**
     * The data to be displayed in the form
     *
     * @var   array
     * @since 3.2
     */
    public $data;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     \JViewLegacy::loadTemplate()
     * @since   3.0
     */
    public function display($tpl = null)
    {
        try {
            // Load Form and Data
            $form = $this->get('form');
            $data = $this->get('data');
            $user = $this->getCurrentUser();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        // Bind data
        if ($form && $data) {
            $form->bind($data);
        }

        // Get the params for com_users.
        $usersParams = ComponentHelper::getParams('com_users');

        // Get the params for com_media.
        $mediaParams = ComponentHelper::getParams('com_media');

        $this->form        = &$form;
        $this->data        = &$data;
        $this->usersParams = &$usersParams;
        $this->mediaParams = &$mediaParams;
        $this->components  = ConfigHelper::getComponentsWithConfig();
        ConfigHelper::loadLanguageForComponents($this->components);

        $this->userIsSuperAdmin = $user->authorise('core.admin');

        $this->addToolbar();

        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @return  void
     *
     * @since   3.2
     */
    protected function addToolbar()
    {
        $toolbar    = Toolbar::getInstance();

        ToolbarHelper::title(Text::_('COM_CONFIG_GLOBAL_CONFIGURATION'), 'cog config');
        $toolbar->apply('application.apply');
        $toolbar->divider();
        $toolbar->save('application.save');
        $toolbar->divider();
        $toolbar->cancel('application.cancel');
        $toolbar->divider();
        $toolbar->inlinehelp();
        $toolbar->help('Site_Global_Configuration');
    }
}
