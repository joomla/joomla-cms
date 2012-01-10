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

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * fieldattachs Controller
 */
class FieldsattachControllerFieldsattachimages extends JControllerAdmin
{
       /**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	function __construct()
	{  
		parent::__construct();

	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'fieldsattachimages', $prefix = 'fieldsattachModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
        public function delete()
	{ 

                $model = $this->getModel( "fieldsattachimage" );
                $model->delete();
                $link= 'index.php?option=com_fieldsattach&view=fieldsattachimages&tmpl=component' ;
                $this->setRedirect($link, $msg);
	}


         


        
}
