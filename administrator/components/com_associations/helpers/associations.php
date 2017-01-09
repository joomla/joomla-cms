<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Associations component helper.
 *
 * @since  __DEPLOY_VERSION__
 */
class AssociationsHelper extends JHelperContent
{
	/**
	 * var       array   $extensionsSupport  Array of Registry objects of extensions
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static $extensionsSupport = null;

	/**
	 * var       array   $supportedExtensionsList  List of extensions name with support
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static $supportedExtensionsList = array();

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  array
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function getAssociationList($extensionName, $typeName, $itemId)
	{
		if (! self::hasSupport($extensionName))
		{
			return array();
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		$items = $helper->getAssociationList($typeName, $itemId);

		return $items;
	}

	/**
	 * Get the the instance of the extension helper class
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return  HelperClass|null
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function getExtensionHelper($extensionName)
	{
		if (! self::hasSupport($extensionName))
		{
			return null;
		}

		$support = self::$extensionsSupport[$extensionName];

		return $support->get('helper');
	}

	/**
	 * Get item information
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  JTable|null
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function getItem($extensionName, $typeName, $itemId)
	{
		if (! self::hasSupport($extensionName))
		{
			return array();
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		$item = $helper->getItem($typeName, $itemId);

		return $item;
	}

	/**
	 * Check if extension supports associations
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return bool
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function hasSupport($extensionName)
	{
		if (is_null(self::$extensionsSupport))
		{
			self::getSupportedExtensions();
		}

		return in_array($extensionName, self::$supportedExtensionsList);
	}

	/**
	 * Get the extension specific helper class name
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return bool
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	private static function getExtensionHelperClassName($extensionName)
	{
		$realName = self::getExtensionRealName($extensionName);

		return ucfirst($realName) . 'AssociationsHelper';
	}

	/**
	 * Get the real extension name. This means without com_
	 *
	 * @param   string  $extensionName  The extension name with com_
	 *
	 * @return string
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	private static function getExtensionRealName($extensionName)
	{
		return strpos($extensionName, 'com_') === false ? $extensionName : substr($extensionName, 4);
	}

	/**
	 * Get the associated language edit links Html.
	 *
	 * @param   string   $extensionName  Extension Name
	 * @param   string   $typeName       ItemType
	 * @param   integer  $itemId         Item id.
	 * @param   string   $itemLanguage   Item language code.
	 * @param   boolean  $addLink        True for adding edit links. False for just text.
	 * @param   boolean  $allLanguages   True for showing all content languages. False only languages with associations.
	 *
	 * @return string The language HTML
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function getAssociationHtmlList($extensionName, $typeName, $itemId, $itemLanguage, $addLink = true, $allLanguages = true)
	{
		// Get the associations list for this item.
		$items = self::getAssociationList($extensionName, $typeName, $itemId);

		// Get all content languages.
		$languages = self::getContentLanguages();

		$canEditReference = self::allowEdit($extensionName, $typeName, $itemId);
		$canCreate        = self::allowAdd($extensionName, $typeName);

		// Create associated items list.
		foreach ($languages as $langCode => $language)
		{
			// Don't do for the reference language.
			if ($langCode == $itemLanguage)
			{
				continue;
			}

			// Don't show languages without associations, if we don't want to show them.
			if (!$allLanguages && !isset($items[$langCode]))
			{
				continue;
			}

			// Get html parameters.
			if (isset($items[$langCode]))
			{
				$title       = '<br/><br/>' . $items[$langCode]['title'];
				$additional  = '';

				if (isset($items[$langCode]['category_title']))
				{
					$additional = '<br/>' . JText::_('JCATEGORY') . ': ' . $items[$langCode]['category_title'];
				}
				elseif (isset($items[$langCode]['menu_title']))
				{
					$additional = '<br/>' . JText::_('COM_ASSOCIATIONS_HEADING_MENUTYPE') . ': ' . $items[$langCode]['menu_title'];
				}

				$additional .= $addLink ? '<br/><br/>' . JText::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
				$labelClass  = 'label';
				$target      = $langCode . ':' . $items[$langCode]['id'] . ':edit';
				$allow       = $canEditReference && self::allowEdit($extensionName, $typeName, $items[$langCode]['id']);
			}
			else
			{
				$items[$langCode] = array();

				$title      = '<br/><br/>' . JText::_('COM_ASSOCIATIONS_NO_ASSOCIATION');
				$additional = $addLink ? '<br/><br/>' . JText::_('COM_ASSOCIATIONS_ADD_NEW_ASSOCIATION') : '';
				$labelClass = 'label label-warning';
				$target     = $langCode . ':0:add';
				$allow      = $canCreate;
			}

			// Generate item Html.
			$options   = array(
				'option'   => 'com_associations',
				'view'     => 'association',
				'layout'   => 'edit',
				'itemtype' => $extensionName . '.' . $typeName,
				'task'     => 'association.edit',
				'id'       => $itemId,
				'target'   => $target,
			);
			$url       = JRoute::_('index.php?' . http_build_query($options));
			$text      = strtoupper($language->sef);
			$langImage = JHtml::_('image', 'mod_languages/' . $language->image . '.gif', $language->title, array('title' => $language->title), true);
			$tooltip   = implode(' ', array($langImage, $language->title, $title, $additional));

			$items[$langCode]['link'] = JHtml::_('tooltip', $tooltip, null, null, $text, $allow && $addLink ? $url : '', null, 'hasTooltip ' . $labelClass);
		}

		return JLayoutHelper::render('joomla.content.associations', $items);
	}

	/**
	 * Get all extensions with associations support.
	 *
	 * @return  array  The extensions.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getSupportedExtensions()
	{
		if (! is_null(self::$extensionsSupport))
		{
			return self::$extensionsSupport;
		}

		self::$extensionsSupport = array();

		$extensions = self::getEnabledExtensions();

		foreach ($extensions as $extension)
		{
			$support = self::getSupportedExtension($extension->element);

			if ($support->get('associationssupport') === true)
			{
				self::$supportedExtensionsList[] = $extension->element;
			}

			self::$extensionsSupport[$extension->element] = $support;
		}

		return self::$extensionsSupport;
	}

	/**
	 * Get item context based on the item key.
	 *
	 * @param   string  $extensionName  The extension identifier.
	 *
	 * @return  Joomla\Registry\Registry  The item properties.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getSupportedExtension($extensionName)
	{
		$result = new Registry;

		$result->def('component', $extensionName);
		$result->def('associationssupport', false);
		$result->def('helper', null);

		// Check if associations helper exists
		if (! file_exists(JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php'))
		{
			return $result;
		}

		require_once JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php';

		$componentAssociationsHelperClassName = self::getExtensionHelperClassName($extensionName);

		if (! class_exists($componentAssociationsHelperClassName, false))
		{
			return $result;
		}

		// Create an instance of the helper class
		$helper = new $componentAssociationsHelperClassName;
		$result->set('helper', $helper);

		if ($helper->hasAssociationsSupport() === false)
		{
			return $result;
		}

		$result->set('associationssupport', true);

		// Get the translated titles.
		$languagePath = JPATH_ADMINISTRATOR . '/components/' . $extensionName;
		$lang         = JFactory::getLanguage();

		$lang->load($extensionName . '.sys', JPATH_ADMINISTRATOR);
		$lang->load($extensionName . '.sys', $languagePath);
		$lang->load($extensionName, JPATH_ADMINISTRATOR);
		$lang->load($extensionName, $languagePath);

		$result->def('title', JText::_(strtoupper($extensionName)));

		// Get the supported types
		$types = $helper->getItemTypes();

		$rTypes = array();

		foreach ($types as $typeName)
		{
			$details     = $helper->getType($typeName);
			$context     = 'component';
			$title       = $helper->getTypeTitle($typeName);
			$languageKey = $typeName;

			if ($typeName === 'category')
			{
				$languageKey = strtoupper($extensionName) . '_CATEGORIES';
				$context     = 'category';
			}

			if ($lang->hasKey(strtoupper($extensionName . '_' . $title . 'S')))
			{
				$languageKey = strtoupper($extensionName . '_' . $title . 'S');
			}

			$title = $lang->hasKey($languageKey) ? JText::_($languageKey) : JText::_('ITEMS');

			$rType = new Registry;

			$rType->def('name', $typeName);
			$rType->def('details', $details);
			$rType->def('title', $title);
			$rType->def('context', $context);

			$rTypes[$typeName] = $rType;
		}

		$result->def('types', $rTypes);

		return $result;
	}

	/**
	 * Get all installed and enabled extensions
	 *
	 * @return  mixed
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private static function getEnabledExtensions()
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('type') . ' = ' . $db->quote('component'))
			->where($db->quoteName('enabled') . ' = 1');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get all the content languages.
	 *
	 * @return  array  Array of objects all content languages by language code.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function getContentLanguages()
	{
		$db = JFactory::getDbo();

		// Get all content languages.
		$query = $db->getQuery(true)
			->select($db->quoteName(array('sef', 'lang_code', 'image', 'title', 'published')))
			->from($db->quoteName('#__languages'))
			->order($db->quoteName('ordering') . ' ASC');

		$db->setQuery($query);

		return $db->loadObjectList('lang_code');
	}

	/**
	 * Get the associated items for an item
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 * @param   int     $itemId         The id of item for which we need the associated items
	 *
	 * @return  bool
	 *
	 * @since    __DEPLOY_VERSION__
	 */
	public static function allowEdit($extensionName, $typeName, $itemId)
	{
		if (! self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper= self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'allowEdit'))
		{
			return $helper->allowEdit($typeName, $itemId);
		}

		$user = JFactory::getUser();

		return $user->authorise('core.edit', $extensionName);
	}

	/**
	 * Check if user is allowed to create items.
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function allowAdd($extensionName, $typeName)
	{
		if (! self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper= self::getExtensionHelper($extensionName);

		if (method_exists($helper, 'allowAdd'))
		{
			return $helper->allowAdd($typeName);
		}

		$user = JFactory::getUser();

		return $user->authorise('core.create', $extensionName);
	}

	/**
	 * Check if the type supports checkout
	 *
	 * @param   string  $extensionName  The extension name with com_
	 * @param   string  $typeName       The item type
	 *
	 * @return  boolean  True on allowed.
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public static function typeSupportsCheckout($extensionName, $typeName)
	{
		if (! self::hasSupport($extensionName))
		{
			return false;
		}

		// Get the extension specific helper method
		$helper = self::getExtensionHelper($extensionName);

		$support = $helper->getTypeSupport($typeName);

		return ! empty($support['checkout']);
	}
}
