<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class JceViewMediabox extends JViewLegacy
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

        $this->name = JText::_('WF_MEDIABOX');
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
        JToolbarHelper::title(JText::_('WF_MEDIABOX'), 'pictures');

        // If not checked out, can save the item.
        if ($user->authorise('jce.config', 'com_jce')) {
            JToolbarHelper::apply('mediabox.apply');
            JToolbarHelper::save('mediabox.save');
        }

        JToolbarHelper::cancel('mediabox.cancel', 'JTOOLBAR_CLOSE');

        JToolbarHelper::divider();
        JToolbarHelper::help('WF_MEDIABOX_EDIT');
    }
}
