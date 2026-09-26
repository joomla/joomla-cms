<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Field\Modal;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ModalSelectField;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports a modal menu item picker.
 *
 * @since  3.7.0
 */
class MenuField extends ModalSelectField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   3.7.0
     */
    protected $type = 'Modal_Menu';

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.7.0
     */
    public function __get($name)
    {
        switch ($name) {
            case 'allowSelect':
            case 'allowClear':
            case 'allowNew':
            case 'allowEdit':
            case 'allowPropagate':
                // @TODO: The override only for backward compatibility. Remove in Joomla 6.
                $map = [
                    'allowSelect'    => 'select',
                    'allowClear'     => 'clear',
                    'allowNew'       => 'new',
                    'allowEdit'      => 'edit',
                    'allowPropagate' => 'propagate',
                ];
                $newName = $map[$name];

                @trigger_error(
                    \sprintf(
                        'MenuField::__get property "%s" is deprecated, and will not work in Joomla 6. Use "%s" property instead.',
                        $name,
                        $newName
                    ),
                    E_USER_DEPRECATED
                );

                return parent::__get($newName);
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
     * @since   3.7.0
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'allowSelect':
            case 'allowClear':
            case 'allowNew':
            case 'allowEdit':
            case 'allowPropagate':
                // @TODO: The override only for backward compatibility. Remove in Joomla 6.
                $map = [
                    'allowSelect'    => 'select',
                    'allowClear'     => 'clear',
                    'allowNew'       => 'new',
                    'allowEdit'      => 'edit',
                    'allowPropagate' => 'propagate',
                ];
                $newName = $map[$name];

                @trigger_error(
                    \sprintf(
                        'MenuField::__set property "%s" is deprecated, and will not work in Joomla 6. Use "%s" property instead.',
                        $name,
                        $newName
                    ),
                    E_USER_DEPRECATED
                );

                $value = (string) $value;
                $value = !($value === 'false' || $value === 'off' || $value === '0');

                parent::__set($newName, $value);
                break;

            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                        For example if the field has name="foo" and the group value is set to "bar" then the
     *                                      full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.7.0
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        // Check if the value consist with id:alias, extract the id only
        if ($value && str_contains($value, ':')) {
            [$id]  = explode(':', $value, 2);
            $value = (int) $id;
        }

        $return = parent::setup($element, $value, $group);

        if (!$return) {
            return $return;
        }

        $app = Factory::getApplication();

        $app->getLanguage()->load('com_menus', JPATH_ADMINISTRATOR);

        $languages = LanguageHelper::getContentLanguages([0, 1], false);
        $language  = (string) $this->element['language'];
        $clientId  = (int) $this->element['clientid'];

        // Prepare enabled actions
        $this->canDo['propagate']  = ((string) $this->element['propagate'] === 'true') && \count($languages) > 2;

        // Creating/editing menu items is not supported in frontend.
        if (!$app->isClient('administrator')) {
            $this->canDo['new']  = false;
            $this->canDo['edit'] = false;
        }

        // Prepare Urls
        $linkItems = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkItems->setQuery([
            'option'                => 'com_menus',
            'view'                  => 'items',
            'layout'                => 'modal',
            'tmpl'                  => 'component',
            'client_id'             => $clientId,
            Session::getFormToken() => 1,
        ]);
        $linkItem = clone $linkItems;
        $linkItem->setVar('view', 'item');
        $linkCheckin = (new Uri())->setPath(Uri::base(true) . '/index.php');
        $linkCheckin->setQuery([
            'option'                => 'com_menus',
            'task'                  => 'items.checkin',
            'format'                => 'json',
            Session::getFormToken() => 1,
        ]);

        if ($language) {
            $linkItems->setVar('forcedLanguage', $language);
            $linkItem->setVar('forcedLanguage', $language);

            $modalTitle = Text::_('COM_MENUS_SELECT_A_MENUITEM') . ' &#8212; ' . $this->getTitle();

            $this->dataAttributes['data-language'] = $language;
        } else {
            $modalTitle = Text::_('COM_MENUS_SELECT_A_MENUITEM');
        }

        $urlSelect = $linkItems;
        $urlEdit   = clone $linkItem;
        $urlEdit->setVar('task', 'item.edit');
        $urlNew    = clone $linkItem;
        $urlNew->setVar('task', 'item.add');

        $this->urls['select']  = (string) $urlSelect;
        $this->urls['new']     = (string) $urlNew;
        $this->urls['edit']    = (string) $urlEdit;
        $this->urls['checkin'] = (string) $linkCheckin;

        // Prepare titles
        $this->modalTitles['select']  = $modalTitle;
        $this->modalTitles['new']     = Text::_('COM_MENUS_NEW_MENUITEM');
        $this->modalTitles['edit']    = Text::_('COM_MENUS_EDIT_MENUITEM');

        $this->hint = $this->hint ?: Text::_('COM_MENUS_SELECT_A_MENUITEM');

        return $return;
    }

    /**
     * Method to retrieve the title of selected item.
     *
     * @return string
     *
     * @since   5.0.0
     */
    protected function getValueTitle()
    {
        $value = (int) $this->value ?: '';
        $title = '';

        if ($value) {
            try {
                $db    = $this->getDatabase();
                $query = $db->getQuery(true)
                    ->select($db->quoteName('title'))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('id') . ' = :id')
                    ->bind(':id', $value, ParameterType::INTEGER);
                $db->setQuery($query);

                $title = $db->loadResult();
            } catch (\Throwable $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
            }
        }

        return $title ?: $value;
    }

    /**
     * Method to get the data to be passed to the layout for rendering.
     *
     * @return  array
     *
     * @since 5.0.0
     */
    protected function getLayoutData()
    {
        $data             = parent::getLayoutData();
        $data['language'] = (string) $this->element['language'];

        return $data;
    }

    /**
     * Get the renderer
     *
     * @param   string  $layoutId  Id to load
     *
     * @return  FileLayout
     *
     * @since   5.0.0
     */
    protected function getRenderer($layoutId = 'default')
    {
        $layout = parent::getRenderer($layoutId);
        $layout->setComponent('com_menus');
        $layout->setClient(1);

        return $layout;
    }
}
