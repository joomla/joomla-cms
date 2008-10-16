<?php
// ensure a valid entry point
defined( '_JEXEC' ) or die( 'Restricted access' );

// import the JModel class
jimport('joomla.application.component.controller');

/**
 * Contact Directory Field Controller
 *
 */
class ContactdirectoryControllerField extends JController
{
	/**
	 * Display the list of fields
	 */
	function display()
	{
		JRequest::setVar('view', 'fields');
		parent::display();
	}

	function add()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view'  , 'field');
		JRequest::setVar( 'edit', false );

		// Checkout the field
		$model = $this->getModel('field');
		$model->checkout();

		parent::display();
	}

	function edit()
	{
		JRequest::setVar( 'hidemainmenu', 1 );
		JRequest::setVar( 'view'  , 'field');
		JRequest::setVar( 'edit', true );

		// Checkout the field
		$model = $this->getModel('field');
		$model->checkout();

		parent::display();
	}

	function apply()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model =& $this->getModel('field');

		if ($id = $model->store($post)) {
			$msg = JText::_( 'FIELD_SAVED' );
		} else {
			$msg = JText::_( 'ERROR_SAVING_FIELD' );
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_contactdirectory&controller=field&task=edit&cid[]='. $id;
		$this->setRedirect($link, $msg);
	}

	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$post	= JRequest::get('post');
		$cid	= JRequest::getVar( 'cid', array(0), 'post', 'array' );
		$post['id'] = (int) $cid[0];

		$model = $this->getModel('field');

		if ($model->store($post)) {
			$msg = JText::_( 'FIELD_SAVED' );
		} else {
			$msg = $model->getError();
		}

		// Check the table in so it can be edited.... we are done with it anyway
		$model->checkin();
		$link = 'index.php?option=com_contactdirectory&controller=field';
		$this->setRedirect($link, $msg);
	}

	function remove()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'ERROR_SAVING_FIELD' ) );
		}

		$model = $this->getModel('field');
		if($model->delete($cid)) {
			$msg = JText::_( 'FIELD_DELETED' );
		} else {
			$msg = JText::_( 'ERROR_DELETING_FIELD' );
		}

		$link = 'index.php?option=com_contactdirectory&controller=field';
		$this->setRedirect($link, $msg);
	}


	function publish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'SELECT_ITEM_PUBLISH' ) );
		}

		$model = $this->getModel('field');
		if(!$model->publish($cid, 1)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}

	function unpublish()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid = JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		if (count( $cid ) < 1) {
			JError::raiseError(500, JText::_( 'SELECT_ITEM_UNPUBLISH' ) );
		}

		$model = $this->getModel('field');
		if(!$model->publish($cid, 0)) {
			echo "<script> alert('".$model->getError(true)."'); window.history.go(-1); </script>\n";
		}

		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}

	function cancel()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Checkin the field
		$model = $this->getModel('field');
		$model->checkin();

		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}


	function orderup()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model = $this->getModel('field');
		$model->move(-1);

		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field');
	}

	function orderdown()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$model = $this->getModel('field');
		$model->move(1);

		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field');
	}

	function saveorder()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		$cid 	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		$order 	= JRequest::getVar( 'order', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);
		JArrayHelper::toInteger($order);

		$model = $this->getModel('field');
		$model->saveorder($cid, $order);

		$msg = 'New ordering saved';
		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field', $msg );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accesspublic()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model = $this->getModel( 'field' );
		if ($model->setAccess($cid, 0)) {
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessregistered()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model = $this->getModel( 'field' );
		if ($model->setAccess($cid, 1)) {
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}

	/**
	* Save the item(s) to the menu selected
	*/
	function accessspecial()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get some variables from the request
		$cid	= JRequest::getVar( 'cid', array(), 'post', 'array' );
		JArrayHelper::toInteger($cid);

		$model = $this->getModel( 'field' );
		if ($model->setAccess($cid, 2)) {
		} else {
			$msg = $model->getError();
		}
		$this->setRedirect( 'index.php?option=com_contactdirectory&controller=field' );
	}
}

?>