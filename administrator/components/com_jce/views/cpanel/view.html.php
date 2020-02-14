<?php

// Check to ensure this file is included in Joomla!
defined('JPATH_PLATFORM') or die;

jimport('joomla.application.component.view');

class JceViewCpanel extends JViewLegacy
{
    protected $icons;
    protected $state;

    /**
     * Display the view.
     */
    public function display($tpl = null)
    {
        $user = JFactory::getUser();
        
        $this->state    = $this->get('State');
        $this->icons    = $this->get('Icons');
        $this->params   = JComponentHelper::getParams('com_jce');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        JHtml::_('jquery.framework');

        $document = JFactory::getDocument();
        $document->addScript('components/com_jce/media/js/cpanel.min.js');
        $document->addStyleSheet('components/com_jce/media/css/cpanel.min.css');

        $this->addToolbar();
        $this->sidebar = JHtmlSidebar::render();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        $state = $this->get('State');
        $user = JFactory::getUser();

        JToolbarHelper::title('JCE - ' . JText::_('WF_CPANEL'), 'home');

        $bar = JToolBar::getInstance('toolbar');

        JHtmlSidebar::setAction('index.php?option=com_jce&view=cpanel');

        if ($user->authorise('core.admin', 'com_jce')) {
            JToolbarHelper::preferences('com_jce');
        }
    }
}
