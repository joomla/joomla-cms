<?php
/**
 * @version		$Id: fieldattachimages.php 15 2011-09-02 18:37:15Z cristian $
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
class fieldsattachModelfieldsattachimages extends JModelList
{
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



                $session =& JFactory::getSession();

                if(JRequest::getVar("reset")==1){
                    $session->set('articleid',"");
                    $session->set('fieldsattachid',""); 
                }

                $articleid =  $session->get('articleid');
                $fieldsattachid =  $session->get('fieldsattachid');

 

                if(empty($articleid) ){
                    $articleid = JRequest::getVar("articleid");
                    $session->set('articleid',$articleid);

                  
                }
                if(empty($fieldsattachid) ){
                    $fieldsattachid = JRequest::getVar("fieldsattachid");
                    $session->set('fieldsattachid',$fieldsattachid);
                }


                // Select some fields
		$query->select('*');

		// From the hello table
		$query->from('#__fieldsattach_images');
                $query->where("articleid = ".$articleid." AND fieldsattachid=".$fieldsattachid);

                $query->order("ordering");

                //echo "".$query;
		return $query;
	}

        

        
}
