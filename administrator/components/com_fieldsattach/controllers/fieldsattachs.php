<?php
/**
 * @version		$Id: fieldattachs.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

/**
 * fieldsattach Controller
 */
class fieldsattachControllerfieldsattachs extends JControllerForm
{

    /**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function getModel($name = 'fieldattachunidad', $prefix = 'fieldsattachModel')
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}

        /*public function save( )
	{
            echo "save";
            $this->model = $this->getModel();
            $this->store();
        }*/
}
