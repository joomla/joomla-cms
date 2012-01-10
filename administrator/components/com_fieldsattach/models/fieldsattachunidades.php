<?php
/**
 * @version		$Id: fieldattachunidades.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * fieldsattachs Model
 */
class fieldsattachModelfieldsattachunidades extends JModelList
{
    /**
	 * Constructor.
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
            parent::__construct();
        }
        /**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @return	void
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
                // Initialise variables.
		$app = JFactory::getApplication('administrator');
                
                $groupId = $this->getUserStateFromRequest($this->context.'.filter.group', 'filter_group_id', null, 'int');
               // $language = $this->getUserStateFromRequest($this->context.'.filter.language', 'filter_language', null, 'int');
		$language = JRequest::getVar("filter_language");
                $this->setState('filter.group_id', $groupId);
                $this->setState('filter.language', $language);

                // List state information.
		parent::populateState('a.title', 'asc');
        }
	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return	string	An SQL query
	 */
	protected function getListQuery() 
	{
		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
                

		// Select some fields
		$query->select('*');

		// From the hello table
		$query->from('#__fieldsattach');
                //WHERE FILTER
                //echo "STATE FILTER:".$this->state->get('filter.group_id');
                //$filter_group_id=  JRequest::getVar( 'filter_group_id' , -1);
                $groupId = $this->getState('filter.group_id');
                $language = $this->getState('filter.language');
                
                if($groupId>0){$query->where(' groupid='.$groupId);}
                if(!empty($language) AND ($language != "*")){$query->where(' ( language="'.$language.'" OR language="*")' ); }
                $query->order(' ordering'); 
                

		return $query;
	}
 
}
