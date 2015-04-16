<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Templates.isis
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Custom field for compiling LESS to css
 *
 * @since  3.4.
 */
class JFormFieldCompile extends JFormField
{
	protected function getInput()
	{
		$compile = 0;
		$compile = JFactory::getApplication()->input->get('compileless');
		$pageurl = str_replace('&amp;compileless=1', '', JURI::getInstance());

		if ($compile) {

			$less = new JLess;
			$less->setPreserveComments(false);
			$less->setFormatter(new JLessFormatterJoomla);

			$uncompressed = array(
				JPATH_ADMINISTRATOR . '/templates/isis/less/template.less' => JPATH_ADMINISTRATOR . '/templates/isis/css/template.css',
				JPATH_ADMINISTRATOR . '/templates/isis/less/template-rtl.less' => JPATH_ADMINISTRATOR . '/templates/isis/css/template-rtl.css'
			);

			foreach ($uncompressed as $source => $output)
			{
				try
				{
					$less->compileFile($source, $output);
				}
				catch (Exception $e)
				{
					echo $e->getMessage();
				}
			}
		}
		return '<button onclick="window.location.href=\''.$pageurl.'\'+\'&amp;compileless=1\'" class="btn btn-danger" type="button">' . JText::_('TPL_ISIS_COMPILE_LABEL') . '</button>';
	}

}
