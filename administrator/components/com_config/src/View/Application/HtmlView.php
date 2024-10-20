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
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;
use Joomla\Component\Config\Administrator\Model\ApplicationModel;

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
     * @var   \Joomla\Registry\Registry
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
     * Title of the fieldset
     *
     * @var    string
     */
    public $name;

    /**
     * Name of the fields to display
     *
     * @var    string
     */
    public $fieldsname;

    /**
     * CSS class of the form
     *
     * @var    string
     */
    public $formclass;

    /**
     * Description of the fieldset
     *
     * @var    string
     */
    public $description;

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
            /** @var ApplicationModel $model */
            $model = $this->getModel();

            // Load Form and Data
            $this->form = $model->getForm();
            $this->data = $model->getData();
            $this->user = $this->getCurrentUser();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        // Bind data
        if ($this->form && $this->data) {
            $this->form->bind($this->data);
        }

        // Get the params for com_users.
        $this->usersParams = ComponentHelper::getParams('com_users');

        // Get the params for com_media.
        $this->mediaParams = ComponentHelper::getParams('com_media');

        $this->components  = ConfigHelper::getComponentsWithConfig();
        ConfigHelper::loadLanguageForComponents($this->components);

        $this->userIsSuperAdmin = $this->user->authorise('core.admin');

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
        $toolbar    = $this->getDocument()->getToolbar();

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
