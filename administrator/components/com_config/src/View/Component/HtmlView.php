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
     * An object with the information for the component
     *
     * @var    \Joomla\CMS\Component\ComponentRecord
     * @since  3.2
     */
    public $component;

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
            $component = $this->get('component');

            if (!$component->enabled) {
                return;
            }

            $form = $this->get('form');
            $user = $this->getCurrentUser();
        } catch (\Exception $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return;
        }

        $this->fieldsets   = $form ? $form->getFieldsets() : null;
        $this->formControl = $form ? $form->getFormControl() : null;

        // Don't show permissions fieldset if not authorised.
        if (!$user->authorise('core.admin', $component->option) && isset($this->fieldsets['permissions'])) {
            unset($this->fieldsets['permissions']);
        }

        $this->form = &$form;
        $this->component = &$component;

        $this->components = ConfigHelper::getComponentsWithConfig();

        $this->userIsSuperAdmin = $user->authorise('core.admin');
        $this->currentComponent = Factory::getApplication()->input->get('component');
        $this->return = Factory::getApplication()->input->get('return', '', 'base64');

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
        ToolbarHelper::title(Text::_($this->component->option . '_configuration'), 'cog config');
        ToolbarHelper::apply('component.apply');
        ToolbarHelper::divider();
        ToolbarHelper::save('component.save');
        ToolbarHelper::divider();
        ToolbarHelper::cancel('component.cancel', 'JTOOLBAR_CLOSE');
        ToolbarHelper::divider();

        $inlinehelp  = (string) $this->form->getXml()->config->inlinehelp['button'] == 'show' ?: false;
        $targetClass = (string) $this->form->getXml()->config->inlinehelp['targetclass'] ?: 'hide-aware-inline-help';

        if ($inlinehelp) {
            ToolbarHelper::inlinehelp($targetClass);
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

        ToolbarHelper::help($helpKey, (bool) $helpUrl, null, $this->currentComponent);
    }
}
