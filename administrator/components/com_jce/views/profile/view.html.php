<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class JceViewProfile extends JViewLegacy
{
    protected $state;
    protected $item;
    public $form;

    /**
     * Display the view.
     */
    public function display($tpl = null)
    {
        $this->state = $this->get('State');
        $this->item = $this->get('Item');
        $this->form = $this->get('Form');

        $this->plugins = $this->get('Plugins');
        $this->rows = $this->get('Rows');
        $this->available = $this->get('AvailableButtons');
        $this->additional = $this->get('AdditionalPlugins');

        // load language files
        $language = JFactory::getLanguage();
        $language->load('com_jce', JPATH_SITE);
        $language->load('com_jce_pro', JPATH_SITE);

        // set JLayoutHelper base path
        JLayoutHelper::$defaultBasePath = JPATH_COMPONENT_ADMINISTRATOR;

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHtml::_('behavior.modal', 'a.modal_users');
        JHtml::_('jquery.ui', array('core', 'sortable'));

        $this->addToolbar();
        //$this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);

        $document = JFactory::getDocument();
        $document->addStyleSheet('components/com_jce/media/css/profile.min.css?' . WF_VERSION);

        $document->addScript('components/com_jce/media/js/core.min.js?' . WF_VERSION);
        $document->addScript('components/com_jce/media/js/profile.min.js?' . WF_VERSION);

        // default theme
        $document->addStyleSheet(JURI::root(true) . '/components/com_jce/editor/tiny_mce/themes/advanced/skins/default/ui.admin.css?' . WF_VERSION);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   2.7
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user       = JFactory::getUser();
        $canEdit    = $user->authorise('core.create', 'com_jce');

        JToolbarHelper::title(JText::_('WF_PROFILES_EDIT'), 'user');

        // For new records, check the create permission.
        if ($canEdit) {
            JToolbarHelper::apply('profile.apply');
            JToolbarHelper::save('profile.save');
            JToolbarHelper::save2new('profile.save2new');
        }

        if (empty($this->item->id)) {
            JToolbarHelper::cancel('profile.cancel');
        } else {
            JToolbarHelper::cancel('profile.cancel', 'JTOOLBAR_CLOSE');
        }

        JToolbarHelper::divider();
        JToolbarHelper::help('WF_PROFILES_EDIT');
    }
}
