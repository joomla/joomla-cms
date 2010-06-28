<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksControllerWeblink extends JControllerForm
{	
	/**
	 * @since	1.6
	 */
	protected $context = 'com_weblinks.edit.weblink';

	/**
	 * @since	1.6
	 */
	protected $view_item = 'form';

	/**
	 * @since	1.6
	 */
	protected $view_list = 'categories';

	/**
	 * Constructor
	 *
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		parent::__construct($config);

		$this->registerTask('apply',		'save');
		$this->registerTask('save2new',		'save');
		$this->registerTask('save2copy',	'save');
	}

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 * @since	1.5
	 */
	public function &getModel($name = 'form', $prefix = '', $config = array())
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}
	/**
	 * Save the record
	 */
	public function save()
	{
		if( parent::save() === true ) {
			$cid = JRequest::getVar( 'jform' );
			$cid = ( int ) $cid[ 'catid' ];
			$this->setRedirect( JRoute::_( 'index.php?option=com_weblinks&view=category&id='.$cid, false ) );
		}
		$this->setMessage(JText::_('COM_WEBLINK_SUBMIT_SAVE_SUCCESS'));
	}
	/**
	 * Method to cancel an edit
	 *
	 * Checks the item in, sets item ID in the session to null, and then redirects to the list page.
	 *
	 * @access	public
	 * @return	void
	 */
	public function cancel()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('JInvalid_Token'));

		// Initialise variables.
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

		// Redirect to the list screen.
		$this->setRedirect($this->_getReturnPage());
	}
	
	protected function _getReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

		if (!($return = $app->getUserState($context.'.return'))) {
			$return = JRequest::getVar('return', base64_encode(JURI::base()));
		}

		$return = JFilterInput::getInstance()->clean($return, 'base64');
		$return = base64_decode($return);

		if (!JURI::isInternal($return)) {
			$return = JURI::base();
		}

		return $return;
	}

	protected function _setReturnPage()
	{
		$app		= JFactory::getApplication();
		$context	= $this->context.'.';

		$return = JRequest::getVar('return', null, 'default', 'base64');

		$app->setUserState($context.'return', $return);
	}
	
	/**
	 * Go to a weblink
	 *
	 * @return	void
	 * @since	1.6
	 */
	public function go()
	{
		// Get the ID from the request
		$id = JRequest::getInt('id');

		// Get the model, requiring published items
		$modelLink	= $this->getModel('Weblink', '', array('ignore_request' => true));
		$modelLink->setState('filter.published', 1);

		// Get the item
		$link	= $modelLink->getItem($id);

		// Make sure the item was found.
		if (empty($link)) {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'));
		}

		// Check whether item access level allows access.
		$user	= JFactory::getUser();
		$groups	= $user->authorisedLevels();

		if (!in_array($link->access, $groups)) {
			return JError::raiseError(403, JText::_("JERROR_ALERTNOAUTHOR"));
		}

		// Check whether category access level allows access.
		$modelCat = $this->getModel('Category', 'WeblinksModel', array('ignore_request' => true));
		$modelCat->setState('filter.published', 1);

		// Get the category
		$category = $modelCat->getCategory($link->catid);

		// Make sure the category was found.
		if (empty($category)) {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_NOT_FOUND'));
		}

		// Check whether item access level allows access.
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Redirect to the URL
		// TODO: Probably should check for a valid http link
		if ($link->url) {
			$modelLink->hit($id);
			JFactory::getApplication()->redirect($link->url);
		} else {
			return JError::raiseWarning(404, JText::_('COM_WEBLINKS_ERROR_WEBLINK_URL_INVALID'));
		}
	}
}
