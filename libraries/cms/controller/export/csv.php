<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerExportCSV extends JControllerExportBase
{
	function export($model, $input, $config)
	{
		$fileName = $config['option'].'_'.$config['subject'].'.csv';
		// send the headers
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment;filename='.$fileName);

		// open output for writing
		if ($handle = fopen('php://output','w'))
		{
			// pass the output handle to the model
			$model->exportCsv($handle, $input);
				
			fclose($handle);
		}

		return true;
	}
}