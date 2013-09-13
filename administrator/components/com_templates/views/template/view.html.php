<?php
/**
* @package     Joomla.Administrator
* @subpackage  com_templates
*
* @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
* @license     GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;

/**
* View to edit a template style.
*
* @package     Joomla.Administrator
* @subpackage  com_templates
* @since       1.6
*/
class TemplatesViewTemplate extends JViewLegacy
{
    /**
     * For loading extension state
     */
    protected $state;

    /**
     * For loading template details
     */
    protected $template;

    /**
     * For loading the source form
     */
    protected $form;

    /**
     * For loading source file contents
     */
    protected $source;

    /**
     * Extension id
     */
    protected $id;

    /**
     * Encrypted file path
     */
    protected $file;

    /**
     * List of available overrides
     */
    protected $overridesList;

    /**
     * Name of the present file
     */
    protected $fileName;

    /**
     * Type of the file - image, source, font
     */
    protected $type;

    /**
     * For loading image information
     */
    protected $image;

    /**
     * Template id for showing preview button
     */
    protected $preview;

    /**
     * For loading font information
     */
    protected $font;

    /**
     * For checking if the template is hathor
     */
    protected $hathor;

    /**
     * A nested array containing lst of files and folders
     */
    protected $files;

    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        $app            = JFactory::getApplication();
        $this->file     = $app->input->get('file');
        $this->fileName = base64_decode($this->file);
        $explodeArray   = explode('.',$this->fileName);
        $ext            = end($explodeArray);
        $this->files	= $this->get('Files');
        $this->state	= $this->get('State');
        $this->template	= $this->get('Template');
        $this->preview	= $this->get('Preview');
        $this->hathor	= $this->get('Hathor');

        if(in_array($ext, array('css','js','php','xml','ini','less')))
        {
            $this->form		= $this->get('Form');
            $this->source	= $this->get('Source');
            $this->type 	= 'file';
        }
        elseif(in_array($ext, array('jpg','jpeg','png','gif')))
        {
            $this->image    = $this->get('Image');
            $this->type 	= 'image';
        }
        elseif(in_array($ext, array('woff','otf','ttf')))
        {
            $this->font     = $this->get('Font');
            $this->type 	= 'font';
        }
        $this->overridesList	= $this->get('OverridesList');
        $this->id       = $this->state->get('extension.id');



        // Check for errors.
        if (count($errors = $this->get('Errors')))
        {
            $app->enqueueMessage(implode("\n", $errors));
            return false;
        }

        $this->addToolbar();
        parent::display($tpl);
    }

    /**
     * Add the page title and toolbar.
     *
     * @since   1.6
     */
    protected function addToolbar()
    {
        JFactory::getApplication()->input->set('hidemainmenu', true);
        $canDo = TemplatesHelper::getActions();

        if($canDo->get('core.edit') && $canDo->get('core.create') && $canDo->get('core.admin'))
        {
            $showButton = true;
        }
        else
        {
            $showButton = false;
        }
        // Get the toolbar object instance
        $bar = JToolBar::getInstance('toolbar');
        $explodeArray = explode('.',$this->fileName);
        $ext = end($explodeArray);

        JToolbarHelper::title(JText::_('COM_TEMPLATES_MANAGER_VIEW_TEMPLATE'), 'thememanager');

        //Add a Apply and save button
        if($this->type == 'file')
        {
            if ($showButton)
            {
                JToolbarHelper::apply('template.apply');
                JToolbarHelper::save('template.save');
            }
        }

        //Add a Crop and Resize button
        elseif($this->type == 'image')
        {

            if ($showButton)
            {
                JToolbarHelper::custom('template.cropImage', 'move', 'move', 'COM_TEMPLATES_BUTTON_CROP', false, false);

                JToolbarHelper::modal('resizeModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RESIZE');
            }
        }

        // Add a copy button
        if ($this->hathor->home == 0)
        {
            if ($showButton)
            {
                JToolbarHelper::modal('collapseModal', 'icon-copy', 'JLIB_HTML_BATCH_COPY');
            }
        }

        //Add a Template preview button
        if ($this->preview->client_id == 0)
        {
            $bar->appendButton('Link', 'picture', 'COM_TEMPLATES_BUTTON_PREVIEW', JUri::root() . 'index.php?tp=1&templateStyle=' . $this->preview->id);
        }

        //Add Manage folders button
        if ($showButton)
        {
            JToolbarHelper::modal('folderModal', 'icon-folder icon white', 'COM_TEMPLATES_BUTTON_FOLDERS');
        }

        // Add a new file button
        if ($showButton)
        {
            JToolbarHelper::modal('fileModal', 'icon-file', 'COM_TEMPLATES_BUTTON_FILE');
        }

        //Add a Rename file Button
        if ($this->hathor->home == 0)
        {
            if ($showButton)
            {
                JToolbarHelper::modal('renameModal', 'icon-refresh', 'COM_TEMPLATES_BUTTON_RENAME');
            }
        }

        //Add a Delete file Button
        if ($showButton)
        {
            JToolbarHelper::modal('deleteModal', 'icon-remove', 'COM_TEMPLATES_BUTTON_DELETE');
        }

        //Add a Compile Button
        if ($showButton)
        {
            if($ext == 'less')
            {
                JToolbarHelper::custom('template.less', 'play', 'play', 'COM_TEMPLATES_BUTTON_LESS', false, false);
            }
        }

        JToolbarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');

        JToolbarHelper::divider();
        JToolbarHelper::help('JHELP_EXTENSIONS_TEMPLATE_MANAGER_TEMPLATES_EDIT');
    }

    /**
     * Method for creating the collapsible tree.
     *
     * uses recursion
     *
     * @since   3.2
     */
    protected function directoryTree($array)
    {
        $temp           = $this->files;
        $this->files    = $array;
        $txt            = $this->loadTemplate('tree');
        $this->files    = $temp;
        return $txt;
    }

    /**
     * Method for listing the folder tree in modals.
     *
     * uses recursion
     *
     * @since   3.2
     */
    protected function folderTree($array)
    {
        $temp           = $this->files;
        $this->files    = $array;
        $txt            = $this->loadTemplate('folders');
        $this->files    = $temp;
        return $txt;
    }

}
