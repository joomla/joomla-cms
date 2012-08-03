<?php
/**
 * Element: JSSection
 * Displays a multiselectbox of available JoomSuite Resources categories
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
 * JSSection Element
 */
class nnFieldJSSection
{
	var $_version = '12.6.4';

	function getInput($name, $id, $value, $params, $children)
	{
		$this->params = $params;

		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_resource/models/record.php')) {
			return 'JoomSuite Resources files not found...';
		}

		$db = JFactory::getDBO();
		$tables = $db->getTableList();
		if (!in_array($db->getPrefix().'js_res_category', $tables)) {
			return 'JoomSuite Resources category table not found in database...';
		}

		$size = (int) $this->def('size');
		$multiple = $this->def('multiple');
		$get_categories = $this->def('getcategories', 1);

		if (!is_array($value)) {
			$value = explode(',', $value);
		}

		$query = $db->getQuery(true);
		$query->select('c.id, c.parent, c.name');
		$query->from('#__js_res_category AS c');
		$query->where('c.published = 1');
		if (!$get_categories) {
			$query->where('c.parent = 0');
		}
		$db->setQuery($query);
		$menuItems = $db->loadObjectList();

		// establish the hierarchy of the menu
		// TODO: use node model
		$children = array();

		if ($menuItems) {
			// first pass - collect children
			foreach ($menuItems as $v) {
				$pt = $v->parent;
				$list = @$children[$pt] ? $children[$pt] : array();
				array_push($list, $v);
				$children[$pt] = $list;
			}
		}

		// second pass - get an indent list of the items
		require_once JPATH_LIBRARIES.'/joomla/html/html/menu.php';
		$list = JHTMLMenu::treerecurse(0, '', array(), $children, 9999, 0, 0);

		// assemble items to the array
		$options = array();
		foreach ($list as $item) {
			$options[] = JHtml::_('select.option', $item->id, $item->treename, 'value', 'text', 0);
		}

		require_once JPATH_PLUGINS.'/system/nnframework/helpers/html.php';
		return nnHTML::selectlist($options, $name, $value, $id, $size, $multiple, '');
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}

class JFormFieldNN_JSSection extends JFormField
{
	/**
	 * The form field type
	 *
	 * @var		string
	 */
	public $type = 'JSSection';

	protected function getInput()
	{
		$this->_nnfield = new nnFieldJSSection();
		return $this->_nnfield->getInput($this->name, $this->id, $this->value, $this->element->attributes(), $this->element->children());
	}
}