<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * The AJAX Controller
 *
 * The plugin event triggered is onAjaxFoo, where 'foo' is
 * the value of the 'name' variable passed via the URL
 * Example: index.php?option=com_ajax&[plugin|module]=foo&format=[raw|json|xml|image]
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @since   3.2
 */
class AjaxControllerAjax extends JControllerBase
{

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 */
	public function execute()
	{
		// Get the document format.
		$viewFormat = JFactory::getDocument()->getType();
		// Get the action
		$action     = $this->input->get('action');

	    $viewClass  = 'AjaxViewAjax' . ucfirst($viewFormat);
	    $modelClass = 'AjaxModel' . ucfirst($action);

	    if(!class_exists($viewClass) || !class_exists($modelClass))
	    {
	    	return false;
	    }

		// Get the View
	    $view = new $viewClass(new $modelClass);

	    // Render the view.
	    echo $view->render();

	    return true;
	}
}
