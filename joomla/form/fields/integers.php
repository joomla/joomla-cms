<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once dirname(__FILE__).DS.'list.php';

/**
 * Form Field class for the Joomla Framework.
 *
 * @package		Joomla.Framework
 * @subpackage	Form
 * @since		1.6
 */
class JFormFieldIntegers extends JFormFieldList
{
	/**
	 * The field type.
	 *
	 * @var		string
	 */
	public $type = 'Integers';

	/**
	 * Method to get a list of options for a list input.
	 *
	 * @return	array		An array of JHtml options.
	 */
	protected function _getOptions()
	{
		$first		= (int)$this->_element->attributes('first');
		$last		= (int)$this->_element->attributes('last');
		$step		= (int)max(1, $this->_element->attributes('step'));
		$options	= array();

		for ($i = $first; $i <= $last; $i += $step) {
			$options[] = JHtml::_('select.option', $i);
		}

		$options	= array_merge(
						parent::_getOptions(),
						$options
					);

		return $options;
	}
}