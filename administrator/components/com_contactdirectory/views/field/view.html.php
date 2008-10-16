<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the ContactDirectory component
 *
 * @static
 * @package		Joomla
 * @subpackage	ContactDirectory
 * @since 1.0
 */
class ContactdirectoryViewField extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $option;

		$db		=& JFactory::getDBO();
		$uri 	=& JFactory::getURI();
		$user 	=& JFactory::getUser();
		$model	=& $this->getModel();

		if (!$user->authorize( 'com_contactdirectory', 'manage fields' )) {
			$mainframe->redirect('index.php?option=com_contactdirectory&controller=contact', JText::_('ALERTNOTAUTH'));
		}

		$lists = array();

		//get the field
		$field	=& $this->get('data');
		$isNew	= ($field->id < 1);

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'THE_FIELD' ), $field->title );
			$mainframe->redirect( 'index.php?option=com_contactdirectory&controller=field', $msg );
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			$field->published = 1;
			$field->approved 	= 1;
			$field->order 	= 0;
		}

		// build the html select list for ordering
		$query = "SELECT ordering AS value, title AS text"
			. " FROM #__contactdirectory_fields"
			. " WHERE pos = '$field->pos'"
			. " ORDER BY ordering";

		$lists['ordering'] = JHTML::_('list.specificordering',  $field, $field->id, $query );

		// build the html select list for published
		$lists['published'] = JHTML::_('select.booleanlist',  'published', 'class="inputbox"', $field->published );

		// build the html select list for access
		$lists['access'] = JHTML::_('list.accesslevel', $field);

		// build the html select list for type
		$types = array();
		//$types[] = JHTML::_('select.option', 'checkbox', 'Check Box (Single)' );
		//$types[] = JHTML::_('select.option', 'multicheckbox', 'Check Box (Muliple)' );
		//$types[] = JHTML::_('select.option', 'date', 'Date' );
		//$types[] = JHTML::_('select.option', 'select', 'Drop Down (Single Select)' );
		//$types[] = JHTML::_('select.option', 'multiselect', 'Drop Down (Multi-Select)' );
		$types[] = JHTML::_('select.option', 'text', JText::_('TEXT_FIELD'));
		$types[] = JHTML::_('select.option', 'textarea', JText::_('TEXT_AREA'));
		$types[] = JHTML::_('select.option', 'editor', JText::_('EDITOR_TEXT_AREA'));
		//$types[] = JHTML::_('select.option', 'number', 'Number Text' );
		$types[] = JHTML::_('select.option', 'email', JText::_('EMAIL_ADDRESS'));
		$types[] = JHTML::_('select.option', 'url', JText::_('URL'));
		//$types[] = JHTML::_('select.option', 'radio', 'Radio Button' );
		$types[] = JHTML::_('select.option', 'image', JText::_('IMAGE'));

		$lists['type'] = JHTML::_('select.genericlist', $types, 'type', 'class="inputbox"', 'value', 'text', $field->type );

		// build the html select list for position
		$positions = array();
		$positions[] = JHTML::_('select.option', 'title', JText::_('TITLE'));
		$positions[] = JHTML::_('select.option', 'top', JText::_('TOP'));
		$positions[] = JHTML::_('select.option', 'left', JText::_('LEFT'));
		$positions[] = JHTML::_('select.option', 'main', JText::_('MAIN'));
		$positions[] = JHTML::_('select.option', 'right', JText::_('RIGHT'));
		$positions[] = JHTML::_('select.option', 'bottom', JText::_('BOTTOM'));

		$lists['pos'] = JHTML::_('select.genericlist', $positions, 'pos', 'class="inputbox"', 'value', 'text', $field->pos );

		//clean field data
		JFilterOutput::objectHTMLSafe( $field, ENT_QUOTES, 'description' );

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'field.xml';
		$params = new JParameter( $field->params, $file );

		$this->assignRef('lists',		$lists);
		$this->assignRef('field',		$field);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
