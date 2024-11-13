<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Config\Administrator\View\Component;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Component\Config\Administrator\Helper\ConfigHelper;
use Joomla\Component\Config\Administrator\Model\ComponentModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * View for the component configuration
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
     * An object with the information for the component
     *
     * @var    \Joomla\CMS\Component\ComponentRecord
     * @since  3.2
     */
    public $component;

    /**
     * List of fieldset objects
     *
     * @var    object[]
     *
     * @since  5.2.0
     */
    public $fieldsets;

    /**
     * Form control
     *
     * @var    string
     *
     * @since  5.2.0
     */
    public $formControl;

    /**
     * Base64 encoded return URL
     *
     * @var    string
     *
     * @since  5.2.0
     */
    public $return;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     *
     * @see     \JViewLegacy::loadTemplate()
     * @since   3.2
     */
    public function display($tpl = null)
    {
        try {
            /** @var ComponentModel $model */
            $model = $this->getModel();

            $this->component = $model->getComponent();

            if (!$this->component->enabled) {
                return;
            }

            $this->form = $model->getForm();
            $user       = $this->getCurrentUser();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        $this->fieldsets   = $this->form ? $this->form->getFieldsets() : null;
        $this->formControl = $this->form ? $this->form->getFormControl() : null;

        // Don't show permissions fieldset if not authorised.
        if (!$user->authorise('core.admin', $this->component->option) && isset($this->fieldsets['permissions'])) {
            unset($this->fieldsets['permissions']);
        }

        $this->components = ConfigHelper::getComponentsWithConfig();

        $this->userIsSuperAdmin = $user->authorise('core.admin');
        $this->currentComponent = Factory::getApplication()->getInput()->get('component');
        $this->return           = Factory::getApplication()->getInput()->get('return', '', 'base64');

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

        ToolbarHelper::title(Text::_($this->component->option . '_configuration'), 'cog config');
        $toolbar->apply('component.apply');
        $toolbar->divider();
        $toolbar->save('component.save');
        $toolbar->divider();
        $toolbar->cancel('component.cancel');
        $toolbar->divider();

        $inlinehelp  = (string) $this->form->getXml()->config->inlinehelp['button'] === 'show';
        $targetClass = (string) $this->form->getXml()->config->inlinehelp['targetclass'] ?: 'hide-aware-inline-help';

        if ($inlinehelp) {
            $toolbar->inlinehelp($targetClass);
        }

        $helpUrl = $this->form->getData()->get('helpURL');
        $helpKey = (string) $this->form->getXml()->config->help['key'];

        // Try with legacy language key
        if (!$helpKey) {
            $language    = Factory::getApplication()->getLanguage();
            $languageKey = 'JHELP_COMPONENTS_' . strtoupper($this->currentComponent) . '_OPTIONS';

            if ($language->hasKey($languageKey)) {
                $helpKey = $languageKey;
            }
        }

        $toolbar->help($helpKey, (bool) $helpUrl, null, $this->currentComponent);
    }
}
