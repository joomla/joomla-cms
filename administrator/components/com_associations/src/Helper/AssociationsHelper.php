<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Associations\Administrator\Helper;

use Joomla\CMS\Association\AssociationExtensionInterface;
use Joomla\CMS\Association\AssociationServiceInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Associations component helper.
 *
 * @since  3.7.0
 */
class AssociationsHelper extends ContentHelper
{
    /**
     * Array of Registry objects of extensions
     *
     * @var    array
     * @since  3.7.0
     */
    public static $extensionsSupport = null;

    /**
     * List of extensions name with support
     *
     * @var    array
     * @since  3.7.0
     */
    public static $supportedExtensionsList = [];

    /**
     * Get the associated items for an item
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     * @param   int     $itemId         The id of item for which we need the associated items
     *
     * @return  array
     *
     * @since  3.7.0
     */
    public static function getAssociationList($extensionName, $typeName, $itemId)
    {
        if (!self::hasSupport($extensionName)) {
            return [];
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        return $helper->getAssociationList($typeName, $itemId);
    }

    /**
     * Get the instance of the extension helper class
     *
     * @param   string  $extensionName  The extension name with com_
     *
     * @return  \Joomla\CMS\Association\AssociationExtensionHelper|null
     *
     * @since  3.7.0
     */
    public static function getExtensionHelper($extensionName)
    {
        if (!self::hasSupport($extensionName)) {
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
     * @return  \Joomla\CMS\Table\Table|null
     *
     * @since  3.7.0
     */
    public static function getItem($extensionName, $typeName, $itemId)
    {
        if (!self::hasSupport($extensionName)) {
            return null;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        return $helper->getItem($typeName, $itemId);
    }

    /**
     * Check if extension supports associations
     *
     * @param   string  $extensionName  The extension name with com_
     *
     * @return  boolean
     *
     * @since  3.7.0
     */
    public static function hasSupport($extensionName)
    {
        if (\is_null(self::$extensionsSupport)) {
            self::getSupportedExtensions();
        }

        return \in_array($extensionName, self::$supportedExtensionsList);
    }

    /**
     * Loads the helper for the given class.
     *
     * @param   string  $extensionName  The extension name with com_
     *
     * @return  AssociationExtensionInterface|null
     *
     * @since  4.0.0
     */
    private static function loadHelper($extensionName)
    {
        $component = Factory::getApplication()->bootComponent($extensionName);

        if ($component instanceof AssociationServiceInterface) {
            return $component->getAssociationsExtension();
        }

        // Check if associations helper exists
        if (!file_exists(JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php')) {
            return null;
        }

        require_once JPATH_ADMINISTRATOR . '/components/' . $extensionName . '/helpers/associations.php';

        $componentAssociationsHelperClassName = self::getExtensionHelperClassName($extensionName);

        if (!class_exists($componentAssociationsHelperClassName, false)) {
            return null;
        }

        // Create an instance of the helper class
        return new $componentAssociationsHelperClassName();
    }

    /**
     * Get the extension specific helper class name
     *
     * @param   string  $extensionName  The extension name with com_
     *
     * @return  string
     *
     * @since  3.7.0
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
     * @return  string
     *
     * @since  3.7.0
     */
    private static function getExtensionRealName($extensionName)
    {
        return strpos($extensionName, 'com_') === false ? $extensionName : substr($extensionName, 4);
    }

    /**
     * Get the associated language edit links Html.
     *
     * @param   string   $extensionName   Extension Name
     * @param   string   $typeName        ItemType
     * @param   integer  $itemId          Item id.
     * @param   string   $itemLanguage    Item language code.
     * @param   boolean  $addLink         True for adding edit links. False for just text.
     * @param   boolean  $assocLanguages  True for showing non associated content languages. False only languages with associations.
     *
     * @return  string   The language HTML
     *
     * @since  3.7.0
     */
    public static function getAssociationHtmlList($extensionName, $typeName, $itemId, $itemLanguage, $addLink = true, $assocLanguages = true)
    {
        // Get the associations list for this item.
        $items = self::getAssociationList($extensionName, $typeName, $itemId);

        $titleFieldName = self::getTypeFieldName($extensionName, $typeName, 'title');

        // Get all content languages.
        $languages         = LanguageHelper::getContentLanguages([0, 1], false);
        $content_languages = array_column($languages, 'lang_code');

        // Display warning if Content Language is trashed or deleted
        foreach ($items as $item) {
            if (!\in_array($item['language'], $content_languages)) {
                Factory::getApplication()->enqueueMessage(Text::sprintf('JGLOBAL_ASSOCIATIONS_CONTENTLANGUAGE_WARNING', $item['language']), 'warning');
            }
        }

        $canEditReference = self::allowEdit($extensionName, $typeName, $itemId);
        $canCreate        = self::allowAdd($extensionName, $typeName);

        // Create associated items list.
        foreach ($languages as $langCode => $language) {
            // Don't do for the reference language.
            if ($langCode == $itemLanguage) {
                continue;
            }

            // Don't show languages with associations, if we don't want to show them.
            if ($assocLanguages && isset($items[$langCode])) {
                unset($items[$langCode]);
                continue;
            }

            // Don't show languages without associations, if we don't want to show them.
            if (!$assocLanguages && !isset($items[$langCode])) {
                continue;
            }

            // Get html parameters.
            if (isset($items[$langCode])) {
                $title       = $items[$langCode][$titleFieldName];
                $additional  = '';

                if (isset($items[$langCode]['catid'])) {
                    $db = Factory::getContainer()->get(DatabaseInterface::class);

                    // Get the category name
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('title'))
                        ->from($db->quoteName('#__categories'))
                        ->where($db->quoteName('id') . ' = :id')
                        ->bind(':id', $items[$langCode]['catid'], ParameterType::INTEGER);

                    $db->setQuery($query);
                    $categoryTitle = $db->loadResult();

                    $additional = '<strong>' . Text::sprintf('JCATEGORY_SPRINTF', $categoryTitle) . '</strong> <br>';
                } elseif (isset($items[$langCode]['menutype'])) {
                    $db = Factory::getContainer()->get(DatabaseInterface::class);

                    // Get the menutype name
                    $query = $db->getQuery(true)
                        ->select($db->quoteName('title'))
                        ->from($db->quoteName('#__menu_types'))
                        ->where($db->quoteName('menutype') . ' = :menutype')
                        ->bind(':menutype', $items[$langCode]['menutype']);

                    $db->setQuery($query);
                    $menutypeTitle = $db->loadResult();

                    $additional = '<strong>' . Text::sprintf('COM_MENUS_MENU_SPRINTF', $menutypeTitle) . '</strong><br>';
                }

                $labelClass  = 'bg-secondary';
                $target      = $langCode . ':' . $items[$langCode]['id'] . ':edit';
                $allow       = $canEditReference
                                && self::allowEdit($extensionName, $typeName, $items[$langCode]['id'])
                                && self::canCheckinItem($extensionName, $typeName, $items[$langCode]['id']);

                $additional .= $addLink && $allow ? Text::_('COM_ASSOCIATIONS_EDIT_ASSOCIATION') : '';
            } else {
                $items[$langCode] = [];

                $title      = Text::_('COM_ASSOCIATIONS_NO_ASSOCIATION');
                $additional = $addLink ? Text::_('COM_ASSOCIATIONS_ADD_NEW_ASSOCIATION') : '';
                $labelClass = 'bg-warning';
                $target     = $langCode . ':0:add';
                $allow      = $canCreate;
            }

            // Generate item Html.
            $options   = [
                'option'   => 'com_associations',
                'view'     => 'association',
                'layout'   => 'edit',
                'itemtype' => $extensionName . '.' . $typeName,
                'task'     => 'association.edit',
                'id'       => $itemId,
                'target'   => $target,
            ];

            $url     = Route::_('index.php?' . http_build_query($options));
            $url     = $allow && $addLink ? $url : '';
            $text    = $language->lang_code;

            $tooltip = '<strong>' . htmlspecialchars($language->title, ENT_QUOTES, 'UTF-8') . '</strong><br>'
                . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '<br><br>' . $additional;
            $classes = 'badge ' . $labelClass;

            $items[$langCode]['link'] = '<a href="' . $url . '" class="' . $classes . '">' . $text . '</a>'
                . '<div role="tooltip">' . $tooltip . '</div>';
        }

        return LayoutHelper::render('joomla.content.associations', $items);
    }

    /**
     * Get all extensions with associations support.
     *
     * @return  array  The extensions.
     *
     * @since  3.7.0
     */
    public static function getSupportedExtensions()
    {
        if (!\is_null(self::$extensionsSupport)) {
            return self::$extensionsSupport;
        }

        self::$extensionsSupport = [];

        $extensions = self::getEnabledExtensions();

        foreach ($extensions as $extension) {
            $support = self::getSupportedExtension($extension->element);

            if ($support->get('associationssupport') === true) {
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
     * @return  \Joomla\Registry\Registry  The item properties.
     *
     * @since  3.7.0
     */
    public static function getSupportedExtension($extensionName)
    {
        $result = new Registry();

        $result->def('component', $extensionName);
        $result->def('associationssupport', false);
        $result->def('helper', null);

        $helper = self::loadHelper($extensionName);

        if (!$helper) {
            return $result;
        }

        $result->set('helper', $helper);

        if ($helper->hasAssociationsSupport() === false) {
            return $result;
        }

        $result->set('associationssupport', true);

        // Get the translated titles.
        $languagePath = JPATH_ADMINISTRATOR . '/components/' . $extensionName;
        $lang         = Factory::getApplication()->getLanguage();

        $lang->load($extensionName . '.sys', JPATH_ADMINISTRATOR);
        $lang->load($extensionName . '.sys', $languagePath);
        $lang->load($extensionName, JPATH_ADMINISTRATOR);
        $lang->load($extensionName, $languagePath);

        $result->def('title', Text::_(strtoupper($extensionName)));

        // Get the supported types
        $types  = $helper->getItemTypes();
        $rTypes = [];

        foreach ($types as $typeName) {
            $details     = $helper->getType($typeName);
            $context     = 'component';
            $title       = $helper->getTypeTitle($typeName);
            $languageKey = $typeName;

            $typeNameExploded = explode('.', $typeName);

            if (array_pop($typeNameExploded) === 'category') {
                $languageKey = strtoupper($extensionName) . '_CATEGORIES';
                $context     = 'category';
            }

            if ($lang->hasKey(strtoupper($extensionName . '_' . $title . 'S'))) {
                $languageKey = strtoupper($extensionName . '_' . $title . 'S');
            }

            $title = $lang->hasKey($languageKey) ? Text::_($languageKey) : Text::_('COM_ASSOCIATIONS_ITEMS');

            $rType = new Registry();

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
     * @since  3.7.0
     */
    private static function getEnabledExtensions()
    {
        $db = Factory::getContainer()->get(DatabaseInterface::class);

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
     * @since  3.7.0
     */
    public static function getContentLanguages()
    {
        return LanguageHelper::getContentLanguages([0, 1]);
    }

    /**
     * Get the associated items for an item
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     * @param   int     $itemId         The id of item for which we need the associated items
     *
     * @return  boolean
     *
     * @since  3.7.0
     */
    public static function allowEdit($extensionName, $typeName, $itemId)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        if (method_exists($helper, 'allowEdit')) {
            return $helper->allowEdit($typeName, $itemId);
        }

        return Factory::getApplication()->getIdentity()->authorise('core.edit', $extensionName);
    }

    /**
     * Check if user is allowed to create items.
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     *
     * @return  boolean  True on allowed.
     *
     * @since  3.7.0
     */
    public static function allowAdd($extensionName, $typeName)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        if (method_exists($helper, 'allowAdd')) {
            return $helper->allowAdd($typeName);
        }

        return Factory::getApplication()->getIdentity()->authorise('core.create', $extensionName);
    }

    /**
     * Check if an item is checked out
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     * @param   int     $itemId         The id of item for which we need the associated items
     *
     * @return  boolean  True if item is checked out.
     *
     * @since   3.7.0
     */
    public static function isCheckoutItem($extensionName, $typeName, $itemId)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        if (!self::typeSupportsCheckout($extensionName, $typeName)) {
            return false;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        if (method_exists($helper, 'isCheckoutItem')) {
            return $helper->isCheckoutItem($typeName, $itemId);
        }

        $item = self::getItem($extensionName, $typeName, $itemId);

        $checkedOutFieldName = $helper->getTypeFieldName($typeName, 'checked_out');

        return $item->{$checkedOutFieldName} != 0;
    }

    /**
     * Check if user can checkin an item.
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     * @param   int     $itemId         The id of item for which we need the associated items
     *
     * @return  boolean  True on allowed.
     *
     * @since   3.7.0
     */
    public static function canCheckinItem($extensionName, $typeName, $itemId)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        if (!self::typeSupportsCheckout($extensionName, $typeName)) {
            return true;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        if (method_exists($helper, 'canCheckinItem')) {
            return $helper->canCheckinItem($typeName, $itemId);
        }

        $item = self::getItem($extensionName, $typeName, $itemId);

        $checkedOutFieldName = $helper->getTypeFieldName($typeName, 'checked_out');

        $userId = Factory::getApplication()->getIdentity()->id;

        return ($item->{$checkedOutFieldName} == $userId || $item->{$checkedOutFieldName} == 0);
    }

    /**
     * Check if the type supports checkout
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     *
     * @return  boolean  True on allowed.
     *
     * @since  3.7.0
     */
    public static function typeSupportsCheckout($extensionName, $typeName)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        $support = $helper->getTypeSupport($typeName);

        return !empty($support['checkout']);
    }

    /**
     * Get a table field name for a type
     *
     * @param   string  $extensionName  The extension name with com_
     * @param   string  $typeName       The item type
     * @param   string  $fieldName      The item type
     *
     * @return  boolean  True on allowed.
     *
     * @since  3.7.0
     */
    public static function getTypeFieldName($extensionName, $typeName, $fieldName)
    {
        if (!self::hasSupport($extensionName)) {
            return false;
        }

        // Get the extension specific helper method
        $helper = self::getExtensionHelper($extensionName);

        return $helper->getTypeFieldName($typeName, $fieldName);
    }

    /**
     * Gets the language filter system plugin extension id.
     *
     * @return  integer  The language filter system plugin extension id.
     *
     * @since   3.7.2
     */
    public static function getLanguagefilterPluginId()
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true)
            ->select($db->quoteName('extension_id'))
            ->from($db->quoteName('#__extensions'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('languagefilter'));
        $db->setQuery($query);

        try {
            $result = (int) $db->loadResult();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        return $result;
    }
}
