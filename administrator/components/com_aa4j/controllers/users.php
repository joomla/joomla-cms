<?php
/**
 * @version		$Id: users.php 22355 2011-11-07 05:11:58Z github_bot $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Users list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @since		1.6
 */
class Aa4jControllerUsers extends JControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_USERS_USERS';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
	//	$this->loadLanguage(JPATH_ADMINISTRATOR.'/components/'.'com_users');
		parent::__construct($config);

		$this->registerTask('block',		'changeBlock');
		$this->registerTask('unblock',		'changeBlock');
			$this->registerTask('detect',		'detect');
						$this->registerTask('delete',		'detete');
	}
	/**
	 * Proxy for getModel.
	 *
	 * @since	1.6
	 */
	public function getModel($name = 'User', $prefix = 'Aa4jModel', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, $config);
	}
	/**
	 * Method to delete rows.
	 *
	 * @param	array	$pks	An array of item ids.
	 *
	 * @return	boolean	Returns true on success, false on failure.
	 * @since	1.6
	 */
	public function delete()
	{
			$app = JFactory::getApplication();
		require_once(dirname(dirname(dirname(__FILE__))).DS.'com_users'.DS.'models'.DS.'user.php');
		// Initialise variables.
		$user	= JFactory::getUser();
		$model= new UsersModelUser();
		$table	= $model->getTable();
		$pks	= JRequest::getVar('cid', array(), '', 'array');
		$pks	= (array) $pks;

        // Check if I am a Super Admin
		$iAmSuperAdmin	= $user->authorise('core.admin');

		// Trigger the onUserBeforeSave event.
		JPluginHelper::importPlugin('user');
		$dispatcher = JDispatcher::getInstance();
    var_dump($user->id);
    var_dump($pks);
		if (in_array($user->id, $pks)) {
				//	Jexit('qui'); 
			$app->enqueueMessage(JText::_('COM_USERS_USERS_ERROR_CANNOT_DELETE_SELF'),'ERROR');
			$app->Redirect('index.php?option=com_aa4j&view=users');

		}   
		
		// Iterate the items to delete each one.
		foreach ($pks as $i => $pk)
		{
			if ($table->load($pk)) {
				// Access checks.
				$allow = $user->authorise('core.delete', 'com_users');
				// Don't allow non-super-admin to delete a super admin
				$allow = (!$iAmSuperAdmin && JAccess::check($pk, 'core.admin')) ? false : $allow;

				if ($allow) {
					// Get users data for the users to delete.
					$user_to_delete = JFactory::getUser($pk);

					// Fire the onUserBeforeDelete event.
					$dispatcher->trigger('onUserBeforeDelete', array($table->getProperties()));

					if (!$table->delete($pk)) {
						$this->setError($table->getError());
						return false;
					} else {
						// Trigger the onUserAfterDelete event.
						$dispatcher->trigger('onUserAfterDelete', array($user_to_delete->getProperties(), true, $this->getError()));
					}
				}
				else {
					// Prune items that you can't change.
					unset($pks[$i]);
					JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
				}
			}
			else {
				$this->setError($table->getError());
				return false;
			}
		}

		$this->setRedirect('index.php?option=com_aa4j&view=users');
		return;
	}
	/**
	 * Method to remove a record.
	 *
	 * @since	1.6
	 */
	public function changeBlock()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
// Get the model.
//var_dump(dirname(dirname(dirname(__FILE__))).DS.'com_users'.DS.'models'.DS.'user.php');
	require_once(dirname(dirname(dirname(__FILE__))).DS.'com_users'.DS.'models'.DS.'user.php');
		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');
		$values	= array('block' => 1, 'unblock' => 0);
		$task	= $this->getTask();
		$value	= JArrayHelper::getValue($values, $task, 0, 'int');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));
		} else {
			$model= new UsersModelUser();

			// Change the state of the records.
			if (!$model->block($ids, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1){
					$this->setMessage(JText::plural('COM_USERS_N_USERS_BLOCKED', count($ids)));
				} elseif ($value == 0){
					$this->setMessage(JText::plural('COM_USERS_N_USERS_UNBLOCKED', count($ids)));
				}
			}
		}

		$this->setRedirect('index.php?option=com_aa4j&view=users');
	}
public function detect() {
	$app = JFactory::getApplication();
	$db = JFactory::getDbo();
	 	$cids	= JRequest::getVar('cid', array(), '', 'array');
	 	
	 if (JPluginHelper::isEnabled('alikonweb', 'alikonweb.detector')){					
			   JPluginHelper::importPlugin( 'alikonweb' );
			   $dispatcher2 =& JDispatcher::getInstance();
          foreach($cids as $cid) {
            $db->setQuery( 
	           'select * FROM #__userextras e , #__users u WHERE e.id='.(int) $cid
	          .' and u.id=e.id'
	                	 );
	                	 
	           	$user = $db->loadObject();
	     	       if (!$db->query()) {
		                   JError::raiseError(392, $db->getErrorMsg() );
		                    return false;    
               }			              
	    	 //jexit(var_dump($user));
		        	 $info_detector = $dispatcher2->trigger( 'onDetect',array(0,$user->ip,$user->email,$user->username,' ',' ') );
		     	    	if( $info_detector[0]['score'] >=4){		     	 				     	 		
                  $db->setQuery( 
	                   'UPDATE #__users SET block =   1 WHERE id='.(int) $cid
	                	 );
	                $db->query();
		
                 if ($db->getErrorNum()) {
			              JError::raiseNotice(500, $db->getErrorMsg());
			           return false;           
	           		 }  	 
		     	 	 }     	
		     	 
           $app->enqueueMessage(JText::sprintf('COM_AA4J_DETECT_RESULT',$info_detector[0]['text'], $user->id,$user->username,$user->ip));
         }     
    } else {
    	 $app->enqueueMessage(JText::_('COM_AA4J_DETECT_UNLOADED'));
    }	
    $this->setRedirect('index.php?option=com_aa4j&view=users');
}
	/**
	 * Method to remove a record.
	 *
	 * @since	1.6
	 */
	public function activate()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$ids	= JRequest::getVar('cid', array(), '', 'array');

		if (empty($ids)) {
			JError::raiseWarning(500, JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Change the state of the records.
			if (!$model->activate($ids)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				$this->setMessage(JText::plural('COM_USERS_N_USERS_ACTIVATED', count($ids)));
			}
		}

		$this->setRedirect('index.php?option=com_aa4j&view=users');
	}

	/**
	 * Method to run batch opterations.
	 *
	 * @return	void
	 * @since	1.6
	 */
	function batch()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Initialise variables.
		$app	= JFactory::getApplication();
		$model	= $this->getModel('User');
		$vars	= JRequest::getVar('batch', array(), 'post', 'array');
		$cid	= JRequest::getVar('cid', array(), 'post', 'array');

		// Sanitize user ids.
		$cid = array_unique($cid);
		JArrayHelper::toInteger($cid);

		// Remove any values of zero.
		if (array_search(0, $cid, true)) {
			unset($cid[array_search(0, $cid, true)]);
		}

		// Attempt to run the batch operation.
		if (!$model->batch($vars, $cid)) {
			// Batch operation failed, go back to the users list and display a notice.
			$message = JText::sprintf('COM_USERS_USER_BATCH_FAILED', $model->getError());
			$this->setRedirect('index.php?option=com_users&view=users', $message, 'error');
			return false;
		}

		$message = JText::_('COM_USERS_USER_BATCH_SUCCESS');
		$this->setRedirect('index.php?option=com_users&view=users', $message);
		return true;
	}
}
