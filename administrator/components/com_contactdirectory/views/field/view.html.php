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

		$lists['ordering'] = JHtml::_('list.specificordering',  $field, $field->id, $query );

		// build the html select list for published
		$lists['published'] = JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $field->published );

		// build the html select list for access
		$lists['access'] = JHtml::_('list.accesslevel', $field);

		// build the html select list for type
		$types = array(
    		//'checkbox' => 'Check Box (Single)',
    		//'multicheckbox' => 'Check Box (Muliple)',
    		//'date' => 'Date',
    		//'select' => 'Drop Down (Single Select)',
    		//'multiselect' => 'Drop Down (Multi-Select)',
    		'text' => JText::_('TEXT_FIELD'),
    		'textarea' => JText::_('TEXT_AREA'),
    		'editor' => JText::_('EDITOR_TEXT_AREA'),
    		//'number' => 'Number Text',
    		'email' => JText::_('EMAIL_ADDRESS'),
    		'url' => JText::_('URL'),
    		//'radio' => 'Radio Button',
    		'image', JText::_('IMAGE'),
        );
		$lists['type'] = JHtml::_(
            'select.genericlist',
            $types,
            'type',
            array(
                'list.attr' => 'class="inputbox"',
                'list.select' => $field->type,
                'option.key' => null
            )
        );

		// build the html select list for position
		$positions = array(
            'title' => JText::_('TITLE'),
    		'top' => JText::_('TOP'),
    		'left' => JText::_('LEFT'),
    		'main' => JText::_('MAIN'),
    		'right' => JText::_('RIGHT'),
    		'bottom' => JText::_('BOTTOM'),
        );

		$lists['pos'] = JHtml::_(
            'select.genericlist', 
            $positions, 
            'pos',
            array(
                'list.attr' => 'class="inputbox"',
                'list.select' => $field->pos,
                'option.key' => null
            )
        );

		//clean field data
		JFilterOutput::objectHTMLSafe( $field, ENT_QUOTES, 'description' );

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'field.xml';
		$params = new JParameter( $field->params, $file );

		$this->assignRef('lists', $lists);
		$this->assignRef('field', $field);
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
