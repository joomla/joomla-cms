<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class JceViewConfig extends JViewLegacy
{
    public $form;
    public $data;

    public function display($tpl = null)
    {
        $document = JFactory::getDocument();

        $form = $this->get('Form');
        $data = $this->get('Data');

        // Bind the form to the data.
        if ($form && $data) {
            $form->bind($data);
        }

        $this->form = $form;
        $this->data = $data;

        $this->name = JText :: _('WF_CONFIG');
        $this->fieldsname = "";

        $this->addToolbar();
        //$this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   3.0
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);

        $user = JFactory::getUser();
        JToolbarHelper::title('JCE - ' . JText::_('WF_CONFIGURATION'), 'equalizer');

        // If not checked out, can save the item.
        if ($user->authorise('jce.config', 'com_jce')) {
            JToolbarHelper::apply('config.apply');
            JToolbarHelper::save('config.save');
        }

        JToolbarHelper::cancel('config.cancel', 'JTOOLBAR_CLOSE');

        JToolbarHelper::divider();
        JToolbarHelper::help('WF_CONFIG_EDIT');
    }
}
