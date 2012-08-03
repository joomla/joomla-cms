<?php
/**
 * Element: Radio Images
 * Displays a list of radio items and the images you can chose from
 *
 * @package			NoNumber Framework
 * @version			12.6.4
 *
 * @author			Peter van Westen <peter@nonumber.nl>
 * @link			http://www.nonumber.nl
 * @copyright		Copyright © 2012 NoNumber All Rights Reserved
 * @license			http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Radio Images Element
 */
class nnFieldRadioImages
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		jimport('joomla.filesystem.folder');
		jimport('joomla.filesystem.file');

		// path to images directory
		$path = JPATH_ROOT.'/'.$this->def('directory');
		$filter = $this->def('filter');
		$exclude = $this->def('exclude');
		$stripExt = $this->def('stripext');
		$files = JFolder::files($path, $filter);
		$rowcount = $this->def('rowcount');

		$options = array();

		if (!$this->def('hide_none')) {
			$options[] = JHtml::_('select.option', '-1', JText::_('Do not use').'<br />');
		}

		if (!$this->def('hide_default')) {
			$options[] = JHtml::_('select.option', '', JText::_('Use default').'<br />');
		}

		if (is_array($files)) {
			$count = 0;
			foreach ($files as $file) {
				if ($exclude) {
					if (preg_match(chr(1).$exclude.chr(1), $file)) {
						continue;
					}
				}
				$count++;
				if ($stripExt) {
					$file = JFile::stripExt($file);
				}
				$image = '<img src="../'.$this->def('directory').'/'.$file.'" style="padding-right: 10px;" title="'.$file.'" alt="'.$file.'" />';
				if ($rowcount && $count >= $rowcount) {
					$image .= '<br />';
					$count = 0;
				}
				$options[] = JHtml::_('select.option', $file, $image);
			}
		}

		$list = JHtml::_('select.radiolist', $options, ''.$name.'', '', 'value', 'text', $value, $id);

		$list = '<div style="float:left;">'.str_replace('<input type="radio"', '</div><div style="float:left;margin:2px 0;"><input type="radio" style="float:left;"', $list).'</div>';
		$list = str_replace(array('<label', '</label>'), array('<span style="float: left;"', '</span>'), $list);
		$list = preg_replace('#</span>(\s*)</div>#', '</span></div>\1', $list);
		$list = str_replace('<br /></span></div>', '<br /></span></div><div style="clear:both;"></div>', $list);

		$list = '<div style="clear:both;"></div>'.$list;

		return $list;
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_RadioImages extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'RadioImages';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldRadioImages();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}