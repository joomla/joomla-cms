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
class ContactdirectoryViewContact extends JView
{
	function display($tpl = null)
	{
		global $mainframe;

		$db =& JFactory::getDBO();
		$uri =& JFactory::getURI();
		$user =& JFactory::getUser();
		$model	=& $this->getModel();

		if (!$user->authorize( 'com_contactdirectory', 'manage contacts' )) {
			$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
		}

		$lists = array();

		//get the contact
		$contact	=& $this->get('data');
		$isNew	= ($contact->id < 1);

		//get the fields
		$fields =& $this->get('fields');
		if($fields == null){
			$query = "SELECT title, type, params, alias FROM #__contactdirectory_fields WHERE published = 1 ORDER BY pos, ordering";
			$db->setQuery( $query );
			$fields = $db->loadObjectList();
			foreach($fields as $field){
				$field->show_contact = 1;
				$field->show_directory = 1;
				$field->data = null;
			}
		}

		//get the categories
		$categories =& $this->get('categories');

		// fail if checked out not by 'me'
		if ($model->isCheckedOut( $user->get('id') )) {
			$msg = JText::sprintf( 'DESCBEINGEDITTED', JText::_( 'THE_CONTACT' ), $contact->name );
			$mainframe->redirect( 'index.php?option=com_contactdirectory&controller=contact', $msg );
		}

		// Edit or Create?
		if (!$isNew)
		{
			$model->checkout( $user->get('id') );
		}
		else
		{
			// initialise new record
			$contact->published = 1;
			$contact->approved 	= 1;
			$contact->order 	= 0;
		}

		// build the html list for categories
		$query = "SELECT id AS value, title AS text"
				. " FROM #__categories"
				. " WHERE section = 'com_contactdirectory'"
				. " AND published = 1"
				. " ORDER BY ordering";
		$db->setQuery( $query );
		$cat = $db->loadObjectList();

		$select = array();
		foreach ($categories as $category){
			$select[] = $category->id;
		}

		$lists['category'] = JHtml::_('select.genericlist', $cat, 'categories[]', 'multiple="multiple" class="inputbox" size="'. count($cat).'"', 'value', 'text', $select );

		$i = 0;
		foreach ($categories as $category) {
			$query = "SELECT c.name AS text, map.ordering AS value "
				."FROM jos_contactdirectory_contacts c "
				."LEFT JOIN jos_contactdirectory_con_cat_map map ON map.contact_id = c.id "
				."WHERE c.published = 1 AND map.category_id = '$category->id' ORDER BY ordering";

			$order = JHtml::_('list.genericordering', $query );
			$lists['ordering'.$i] = JHtml::_('select.genericlist', $order, 'ordering[]', 'class="inputbox" size="1"', 'value', 'text', intval( $category->ordering ) );
			$i++;
		}

		//$lists['ordering'] = JHtml::_('list.specificordering',  $contact, $contact->id, $query );

		// build the html select list for published
		$lists['published'] = JHtml::_('select.booleanlist',  'published', 'class="inputbox"', $contact->published );

		// build the html select list for access
		$lists['access'] = JHtml::_('list.accesslevel', $contact);


		// build the html for the booleanlist Show / Hide Field
		$i = 0;
		foreach ($fields as $field){
			$field->params = new JParameter($field->params);

			$lists['showContact'.$i] = JHtml::_('select.booleanlist', 'showContactPage['.$field->alias.']', 'class="inputbox"', $field->show_contact, 'show', 'hide' );
			$lists['showDirectory'.$i] = JHtml::_('select.booleanlist', 'showContactLists['.$field->alias.']', 'class="inputbox"', $field->show_directory, 'show', 'hide' );
			$i++;
		}

		// build list of users
		$lists['user_id'] = JHtml::_('list.users',  'user_id', $contact->user_id, 1, null, 'name', 0 );

		//clean contact data
		JFilterOutput::objectHTMLSafe( $contact, ENT_QUOTES, 'description' );

		$file 	= JPATH_COMPONENT.DS.'models'.DS.'contact.xml';
		$params = new JParameter( $contact->params, $file );

		$this->assignRef('lists',		$lists);
		$this->assignRef('contact',		$contact);
		$this->assignRef('fields',		$fields);
		$this->assignRef('categories',	$categories);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}
}
