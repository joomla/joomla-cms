<?php
// $HeadURL: https://joomgallery.org/svn/joomgallery/JG-2.0/JG/trunk/administrator/components/com_joomgallery/views/images/view.html.php $
// $Id: view.html.php 3386 2011-10-09 16:35:01Z erftralle $
/******************************************************************************\
**   JoomGallery 2                                                            **
**   By: JoomGallery::ProjectTeam                                             **
**   Copyright (C) 2008 - 2011  JoomGallery::ProjectTeam                      **
**   Based on: JoomGallery 1.0.0 by JoomGallery::ProjectTeam                  **
**   Released under GNU GPL Public License                                    **
**   License: http://www.gnu.org/copyleft/gpl.html or have a look             **
**   at administrator/components/com_joomgallery/LICENSE.TXT                  **
\******************************************************************************/

defined('_JEXEC') or die('Direct Access to this location is not allowed.');

jimport('joomla.application.component.view');

/**
 * HTML View class for the images list view
 *
 * @package JoomGallery
 * @since   1.5.5
 */
class LanguagesViewOverrides extends JView
{
  /**
   * HTML view display method
   *
   * @access  public
   * @param   string  $tpl  The name of the template file to parse
   * @return  void
   * @since   1.5.5
   */
  function display($tpl = null)
  {
    jimport('joomla.language.helper');
    JHTML::_('behavior.tooltip');

    $doc  = JFactory::getDocument();
    $doc->addStyleSheet(JURI::root().'media/system/css/overrider.css');
    //JHTML::core();
    $doc->addScript(JURI::root().'media/system/js/overrider.js');

    // Get data from the model
    $state      = $this->get('State');
    $items      = $this->get('Overrides');
    $pagination = $this->get('Pagination');

    $this->assignRef('state',       $state);
    $this->assignRef('items',       $items);
    $this->assignRef('pagination',  $pagination);

    $this->addToolbar();
    parent::display($tpl);
  }

  protected function addToolbar()
  {
    // Get the results for each action
    $canDo = LanguagesHelper::getActions();

    JToolBarHelper::title(JText::_('COM_LANGUAGES_VIEW_OVERRIDES_TITLE'), 'langmanager');

    if($canDo->get('core.create'))
    {
      JToolbarHelper::addNew('override.add');
    }

    if($canDo->get('core.edit') && $this->pagination->total)
    {
      JToolbarHelper::editList('override.edit');
    }

    if($canDo->get('core.delete') && $this->pagination->total)
    {
      JToolbarHelper::deleteList('', 'overrides.delete');
    }

    if($canDo->get('core.admin'))
    {
      JToolBarHelper::preferences('com_languages');
    }
  }
}