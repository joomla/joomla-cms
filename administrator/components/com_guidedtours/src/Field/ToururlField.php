<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_guidedtours
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Guidedtours\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper as Html;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Tour URLs Field class.
 * Display a list of enabled URLs for the administration components from the extension table.
 *
 * @since   __DEPLOY_VERSION__
 */
class ToururlField extends ListField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since   __DEPLOY_VERSION__
     */
    protected $type = 'Toururl';

    /**
     * Method to attach a Form object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string             $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     TourUrlField::setup()
     * @since   __DEPLOY_VERSION__
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        return parent::setup($element, $value, $group);
    }

    protected function getOptions()
    {
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);
        $query->select($db->quoteName(['a.extension_id','a.name','a.element']));
        $query->from($db->quoteName('#__extensions', 'a'));
        $query->where($db->quoteName('a.type') . ' = "component"');
        $query->where($db->quoteName('a.enabled') . ' = 1');
        //$query->where($db->quoteName('a.access') . ' = 1');
        $query->order('a.name ASC');
        $db->setQuery((string)$query);
        $items = $db->loadObjectList();

        $options = [];
        if ($items) {
            $options[] = Html::_('select.option', 'custom', Text::_('COM_GUIDEDTOURS_FIELD_URL_TYPE_CUSTOM_URL'));
            foreach ($items as $item) {
                $options[] = Html::_('select.option', 'administrator/index.php?option=' . $item->element, Text::_($item->name) . ' (' . $item->element . ')');
            }
        }

        return $options;
    }
}
