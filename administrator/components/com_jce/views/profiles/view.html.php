<?php

// Check to ensure this file is included in Joomla!
defined('JPATH_PLATFORM') or die;

class JceViewProfiles extends JViewLegacy
{
    protected $items;
    protected $pagination;
    protected $state;

    /**
     * Display the view.
     */
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        $this->pagination = $this->get('Pagination');
        $this->state = $this->get('State');
        $this->filterForm = $this->get('FilterForm');

        $this->params = JComponentHelper::getParams('com_jce');

        // Check for errors.
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));

            return false;
        }

        if (empty($this->items)) {
            $link = JHTML::link('index.php?option=com_jce&task=profiles.repair&' . JSession::getFormToken() . '=1', JText::_('WF_DB_CREATE_RESTORE'), array('class' => 'wf-profiles-repair'));
            JFactory::getApplication()->enqueueMessage(JText::_('WF_DB_PROFILES_ERROR') . ' - ' . $link, 'error');
        }

        JHtml::_('jquery.framework');

        $document = JFactory::getDocument();
        $document->addScript('components/com_jce/media/js/profiles.min.js');
        $document->addStyleSheet('components/com_jce/media/css/profiles.min.css');

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

        JToolbarHelper::title('JCE - ' . JText::_('WF_PROFILES'), 'users');

        $bar = JToolBar::getInstance('toolbar');

        if ($user->authorise('jce.profiles', 'com_jce')) {
            JToolbarHelper::addNew('profile.add');
            JToolbarHelper::custom('profiles.copy', 'copy', 'copy', 'WF_PROFILES_COPY', true);
            
            // Instantiate a new JLayoutFile instance and render the layout
            $layout = new JLayoutFile('toolbar.uploadprofile');
            $bar->appendButton('Custom', $layout->render(array()), 'upload');

            JToolbarHelper::custom('profiles.export', 'download', 'download', 'WF_PROFILES_EXPORT', true);

            JToolbarHelper::publish('profiles.publish', 'JTOOLBAR_PUBLISH', true);
            JToolbarHelper::unpublish('profiles.unpublish', 'JTOOLBAR_UNPUBLISH', true);

            JToolbarHelper::deleteList('', 'profiles.delete', 'JTOOLBAR_DELETE');
        }

        JHtmlSidebar::setAction('index.php?option=com_jce&view=profiles');

        if ($user->authorise('core.admin', 'com_jce')) {
            JToolbarHelper::preferences('com_jce');
        }
    }

    /**
     * Returns an array of fields the table can be sorted by.
     *
     * @return array Array containing the field name to sort by as the key and display text as value
     *
     * @since   3.0
     */
    protected function getSortFields()
    {
        return array(
            'ordering' => JText::_('JGRID_HEADING_ORDERING'),
            'name' => JText::_('JGLOBAL_TITLE'),
            'published' => JText::_('JSTATUS'),
            'id' => JText::_('JGRID_HEADING_ID'),
        );
    }
}
