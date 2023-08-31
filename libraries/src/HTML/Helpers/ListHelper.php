<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseQuery;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for creating different select lists
 *
 * @since  1.5
 */
abstract class ListHelper
{
    /**
     * Build the select list to choose an image
     *
     * @param   string  $name        The name of the field
     * @param   string  $active      The selected item
     * @param   string  $javascript  Alternative javascript
     * @param   string  $directory   Directory the images are stored in
     * @param   string  $extensions  Allowed extensions
     *
     * @return  array  Image names
     *
     * @since   1.5
     */
    public static function images($name, $active = null, $javascript = null, $directory = null, $extensions = 'bmp|gif|jpg|png')
    {
        if (!$directory) {
            $directory = '/images/';
        }

        if (!$javascript) {
            $javascript = "onchange=\"if (document.forms.adminForm." . $name
                . ".options[selectedIndex].value!='') {document.imagelib.src='..$directory' + document.forms.adminForm." . $name
                . ".options[selectedIndex].value} else {document.imagelib.src='media/system/images/blank.png'}\"";
        }

        $imageFiles = new \DirectoryIterator(JPATH_SITE . '/' . $directory);
        $images     = [HTMLHelper::_('select.option', '', Text::_('JOPTION_SELECT_IMAGE'))];

        foreach ($imageFiles as $file) {
            $fileName = $file->getFilename();

            if (!$file->isFile()) {
                continue;
            }

            if (preg_match('#(' . $extensions . ')$#', $fileName)) {
                $images[] = HTMLHelper::_('select.option', $fileName);
            }
        }

        $images = HTMLHelper::_(
            'select.genericlist',
            $images,
            $name,
            [
                'list.attr'   => 'size="1" ' . $javascript,
                'list.select' => $active,
            ]
        );

        return $images;
    }

    /**
     * Returns an array of options
     *
     * @param   DatabaseQuery|string   $query  SQL with 'ordering' AS value and 'name field' AS text
     * @param   integer                $chop   The length of the truncated headline
     *
     * @return  array  An array of objects formatted for JHtml list processing
     *
     * @since   1.5
     */
    public static function genericordering($query, $chop = 30)
    {
        $db      = Factory::getDbo();
        $options = [];
        $db->setQuery($query);

        $items = $db->loadObjectList();

        if (empty($items)) {
            $options[] = HTMLHelper::_('select.option', 1, Text::_('JLIB_FORM_FIELD_PARAM_INTEGER_FIRST_LABEL'));

            return $options;
        }

        $options[] = HTMLHelper::_('select.option', 0, ' - ' . Text::_('JLIB_FORM_FIELD_PARAM_INTEGER_FIRST_LABEL') . ' - ');

        for ($i = 0, $n = count($items); $i < $n; $i++) {
            $items[$i]->text = Text::_($items[$i]->text);

            if (StringHelper::strlen($items[$i]->text) > $chop) {
                $text = StringHelper::substr($items[$i]->text, 0, $chop) . '...';
            } else {
                $text = $items[$i]->text;
            }

            $options[] = HTMLHelper::_('select.option', $items[$i]->value, $text);
        }

        $options[] = HTMLHelper::_('select.option', $items[$i - 1]->value + 1, ' - ' . Text::_('JLIB_FORM_FIELD_PARAM_INTEGER_LAST_LABEL') . ' - ');

        return $options;
    }

    /**
     * Build the select list for Ordering derived from a query
     *
     * @param   integer  $name      The scalar value
     * @param   string   $query     The query
     * @param   string   $attribs   HTML tag attributes
     * @param   string   $selected  The selected item
     * @param   integer  $neworder  1 if new and first, -1 if new and last, 0  or null if existing item
     * @param   string   $id        ID attribute for the resulting <select> element
     *
     * @return  string   HTML markup for the select list
     *
     * @since   1.6
     */
    public static function ordering($name, $query, $attribs = null, $selected = null, $neworder = null, ?string $id = null)
    {
        if (empty($attribs)) {
            $attribs = 'size="1"';
        }

        if (empty($neworder)) {
            $orders = HTMLHelper::_('list.genericordering', $query);
            $html   = HTMLHelper::_(
                'select.genericlist',
                $orders,
                $name,
                ['list.attr' => $attribs, 'list.select' => (int) $selected, 'id' => $id ?? false]
            );
        } else {
            if ($neworder > 0) {
                $text = Text::_('JGLOBAL_NEWITEMSLAST_DESC');
            } elseif ($neworder <= 0) {
                $text = Text::_('JGLOBAL_NEWITEMSFIRST_DESC');
            }

            $html = '<input type="hidden" name="' . $name . '" value="' . (int) $selected . '"><span class="readonly">' . $text . '</span>';
        }

        return $html;
    }

    /**
     * Select list of active users
     *
     * @param   string   $name        The name of the field
     * @param   string   $active      The active user
     * @param   integer  $nouser      If set include an option to select no user
     * @param   string   $javascript  Custom javascript
     * @param   string   $order       Specify a field to order by
     *
     * @return  string   The HTML for a list of users list of users
     *
     * @since   1.5
     */
    public static function users($name, $active, $nouser = 0, $javascript = null, $order = 'name')
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('u.id', 'value'),
                    $db->quoteName('u.name', 'text'),
                ]
            )
            ->from($db->quoteName('#__users', 'u'))
            ->join('LEFT', $db->quoteName('#__user_usergroup_map', 'm'), $db->quoteName('m.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('u.block') . ' = 0')
            ->order($order)
            ->group($db->quoteName('u.id'));
        $db->setQuery($query);

        if ($nouser) {
            $users[] = HTMLHelper::_('select.option', '0', Text::_('JOPTION_NO_USER'));
            $users   = array_merge($users, $db->loadObjectList());
        } else {
            $users = $db->loadObjectList();
        }

        $users = HTMLHelper::_(
            'select.genericlist',
            $users,
            $name,
            [
                'list.attr'   => 'size="1" ' . $javascript,
                'list.select' => $active,
            ]
        );

        return $users;
    }

    /**
     * Select list of positions - generally used for location of images
     *
     * @param   string   $name        Name of the field
     * @param   string   $active      The active value
     * @param   string   $javascript  Alternative javascript
     * @param   boolean  $none        Null if not assigned
     * @param   boolean  $center      Null if not assigned
     * @param   boolean  $left        Null if not assigned
     * @param   boolean  $right       Null if not assigned
     * @param   boolean  $id          Null if not assigned
     *
     * @return  array  The positions
     *
     * @since   1.5
     */
    public static function positions(
        $name,
        $active = null,
        $javascript = null,
        $none = true,
        $center = true,
        $left = true,
        $right = true,
        $id = false
    ) {
        $pos = [];

        if ($none) {
            $pos[''] = Text::_('JNONE');
        }

        if ($center) {
            $pos['center'] = Text::_('JGLOBAL_CENTER');
        }

        if ($left) {
            $pos['left'] = Text::_('JGLOBAL_LEFT');
        }

        if ($right) {
            $pos['right'] = Text::_('JGLOBAL_RIGHT');
        }

        $positions = HTMLHelper::_(
            'select.genericlist',
            $pos,
            $name,
            [
                'id'          => $id,
                'list.attr'   => 'size="1"' . $javascript,
                'list.select' => $active,
                'option.key'  => null,
            ]
        );

        return $positions;
    }
}
