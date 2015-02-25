<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Class JControllerAdministrate
 * This class is reserved for administrative controls.
 * This protected all administrative tasks and prevents unauthorised access.
 */
abstract class JControllerAdministrate extends JControllerCms
{
	public function __construct(JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		$option = $input->get('option');

		if (!JFactory::getUser()->authorise('core.manage', $option))
		{
			throw new ErrorException(JText::_('JERROR_ALERTNOAUTHOR'), 404);
		}

		parent::__construct($input, $app, $config);
	}

	/**
	 * Method to get the id(s) from the input and insure they are integers
	 *
	 * @return array
	 */
	protected function getIds()
	{
		$input = $this->input;
		$cid   = $this->cleanCid($input->post->get('cid', array($input->getInt('id', 0)), 'array'));

		return $cid;
	}

	/**
	 * Method to cast all values in a cid array to integer values
	 *
	 * @param array $cid
	 *
	 * @return array of clean integer ids
	 */
	protected function cleanCid($cid)
	{
		$cleanCid = array();
		foreach ((array) $cid AS $pk)
		{
			$cleanCid[] = (int) $pk;
		}

		return $cleanCid;
	}
}