<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_pemasoft_finance
 *
 * @copyright   2020 PeMaSoft - Peter Mayer, All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Base controller class for com_pemasoft_finance.
 *
 * @since  1.5
 */
class PemasoftFinanceController extends JControllerLegacy
{
	/**
	 * Method to display a view.
	 *
	 * @param   boolean  $cachable   If true, the view output will be cached
	 * @param   array    $urlparams  An array of safe URL parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return  JController  This object to support chaining.
	 *
	 * @since   1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Get the document object.
		$document = JFactory::getDocument();

        // $id = $this->input->getInt('id');

		// Set the default view name and format from the Request.
        $vName = $this->input->get('view', 'stocks');
        $this->input->set('view', $vName);

		// Check for errors.
		// if (count($errors = $this->get('Errors')))
		// {
		// 	JLog::add(implode('<br />', $errors), JLog::WARNING, 'whi - Error #0001');

		// 	return false;
		// }

        parent::display($cachable, $urlparams);

        return $this;       

	}
}
