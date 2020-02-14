<?php
/**
 * @package Helix Ultimate Framework
 * @author JoomShaper https://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2018 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
*/

defined ('_JEXEC') or die ();

jimport('joomla.form.formfield');
jimport('joomla.html.html.select');

require_once dirname(__DIR__) . '/core/lib/fa.php';

class JFormFieldHelixicon extends JFormField {

	protected $type = 'Helixicon';

	public function getInput()
	{

		$icons = $fa_list;

		$arr = array();
		$arr[] = JHtml::_('select.option', '', '' );

		foreach ($icons as $value)
		{
			$arr[] = JHtml::_('select.option', $value, str_replace('fa-', '', $value) );
		}

		return JHtml::_('select.genericlist', $arr, $this->name, null, 'value', 'text', $this->value);

	}
}
