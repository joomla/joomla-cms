<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

JFormHelper::loadFieldClass('list');

class JFormFieldAlphabet extends JFormFieldList {
	
	protected $type = 'Alphabet';

	public function getOptions() {
        $alphabets = array();
        foreach( range('a', 'z') as $elements) {
			$alphabets[] = array("value" => $elements, "text" => $elements);
		}
		$options = array_merge(parent::getOptions(), $alphabets);
        return $options;
	}
}