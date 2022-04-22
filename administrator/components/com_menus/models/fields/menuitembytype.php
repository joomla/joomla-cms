<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('groupedlist');

// Import the com_menus helper.
JLoader::register('MenusHelper', JPATH_ADMINISTRATOR . '/components/com_menus/helpers/menus.php');

/**
 * Supports an HTML grouped select list of menu item grouped by menu
 *
 * @since  3.8.0
 */
class JFormFieldMenuitemByType extends JFormFieldGroupedList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	public $type = 'MenuItemByType';

	/**
	 * The menu type.
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	protected $menuType;

	/**
	 * The client id.
	 *
	 * @var    string
	 * @since  3.8.0
	 */
	protected $clientId;

	/**
	 * The language.
	 *
	 * @var    array
	 * @since  3.8.0
	 */
	protected $language;

	/**
	 * The published status.
	 *
	 * @var    array
	 * @since  3.8.0
	 */
	protected $published;

	/**
	 * The disabled status.
	 *
	 * @var    array
	 * @since  3.8.0
	 */
	protected $disable;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to get the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.8.0
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'menuType':
			case 'clientId':
			case 'language':
			case 'published':
			case 'disable':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to set the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.8.0
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'menuType':
				$this->menuType = (string) $value;
				break;

			case 'clientId':
				$this->clientId = (int) $value;
				break;

			case 'language':
			case 'published':
			case 'disable':
				$value = (string) $value;
				$this->$name = $value ? explode(',', $value) : array();
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.8.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$result = parent::setup($element, $value, $group);

		if ($result == true)
		{
			$menuType = (string) $this->element['menu_type'];

			if (!$menuType)
			{
				$app = JFactory::getApplication('administrator');
				$currentMenuType = $app->getUserState('com_menus.items.menutype', '');
				$menuType        = $app->input->getString('menutype', $currentMenuType);
			}

			$this->menuType  = $menuType;
			$this->clientId  = (int) $this->element['client_id'];
			$this->published = $this->element['published'] ? explode(',', (string) $this->element['published']) : array();
			$this->disable   = $this->element['disable'] ? explode(',', (string) $this->element['disable']) : array();
			$this->language  = $this->element['language'] ? explode(',', (string) $this->element['language']) : array();
		}

		return $result;
	}

	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   3.8.0
	 */
	protected function getGroups()
	{
		$groups = array();

		$menuType = $this->menuType;

		// Get the menu items.
		$items = MenusHelper::getMenuLinks($menuType, 0, 0, $this->published, $this->language, $this->clientId);

		// Build group for a specific menu type.
		if ($menuType)
		{
			// If the menutype is empty, group the items by menutype.
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__menu_types'))
				->where($db->quoteName('menutype') . ' = ' . $db->quote($menuType));
			$db->setQuery($query);

			try
			{
				$menuTitle = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$menuTitle = $menuType;
			}

			// Initialize the group.
			$groups[$menuTitle] = array();

			// Build the options array.
			foreach ($items as $key => $link)
			{
				// Unset if item is menu_item_root
				if ($link->text === 'Menu_Item_Root')
				{
					unset($items[$key]);
					continue;
				}

				$levelPrefix = str_repeat('- ', max(0, $link->level - 1));

				// Displays language code if not set to All
				if ($link->language !== '*')
				{
					$lang = ' (' . $link->language . ')';
				}
				else
				{
					$lang = '';
				}

				$groups[$menuTitle][] = JHtml::_('select.option',
					$link->value, $levelPrefix . $link->text . $lang,
					'value',
					'text',
					in_array($link->type, $this->disable)
				);
			}
		}
		// Build groups for all menu types.
		else
		{
			// Build the groups arrays.
			foreach ($items as $menu)
			{
				// Initialize the group.
				$groups[$menu->title] = array();

				// Build the options array.
				foreach ($menu->links as $link)
				{
					$levelPrefix = str_repeat('- ', max(0, $link->level - 1));

					// Displays language code if not set to All
					if ($link->language !== '*')
					{
						$lang = ' (' . $link->language . ')';
					}
					else
					{
						$lang = '';
					}

					$groups[$menu->title][] = JHtml::_('select.option',
						$link->value,
						$levelPrefix . $link->text . $lang,
						'value',
						'text',
						in_array($link->type, $this->disable)
					);
				}
			}
		}

		// Merge any additional groups in the XML definition.
		$groups = array_merge(parent::getGroups(), $groups);

		return $groups;
	}
}
