<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Categories\Administrator\Field;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Category Edit field..
 *
 * @since  1.6
 */
class CategoryeditField extends ListField
{
    /**
     * To allow creation of new categories.
     *
     * @var    integer
     * @since  3.6
     */
    protected $allowAdd;

    /**
     * Optional prefix for new categories.
     *
     * @var    string
     * @since  3.9.11
     */
    protected $customPrefix;

    /**
     * A flexible category list that respects access controls
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'CategoryEdit';

    /**
     * Name of the layout being used to render the field
     *
     * @var    string
     * @since  4.0.0
     */
    protected $layout = 'joomla.form.field.categoryedit';

    /**
     * Method to attach a JForm object to the field.
     *
     * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
     * @param   mixed              $value    The form field value to validate.
     * @param   string|null        $group    The field name group control value. This acts as an array container for the field.
     *                                       For example if the field has name="foo" and the group value is set to "bar" then the
     *                                       full field name would end up being "bar[foo]".
     *
     * @return  boolean  True on success.
     *
     * @see     FormField::setup()
     * @since   3.2
     */
    public function setup(\SimpleXMLElement $element, $value, $group = null)
    {
        $return = parent::setup($element, $value, $group);

        if ($return) {
            $this->allowAdd = isset($this->element['allowAdd']) ? (bool) $this->element['allowAdd'] : false;
            $this->customPrefix = (string) $this->element['customPrefix'];
        }

        return $return;
    }

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.6
     */
    public function __get($name)
    {
        switch ($name) {
            case 'allowAdd':
                return (bool) $this->$name;
            case 'customPrefix':
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
     * @since   3.6
     */
    public function __set($name, $value)
    {
        $value = (string) $value;

        switch ($name) {
            case 'allowAdd':
                $value = (string) $value;
                $this->$name = ($value === 'true' || $value === $name || $value === '1');
                break;
            case 'customPrefix':
                $this->$name = (string) $value;
                break;
            default:
                parent::__set($name, $value);
        }
    }

    /**
     * Method to get a list of categories that respects access controls and can be used for
     * either category assignment or parent category assignment in edit screens.
     * Use the parent element to indicate that the field will be used for assigning parent categories.
     *
     * @return  array  The field option objects.
     *
     * @since   1.6
     */
    protected function getOptions()
    {
        $options = [];
        $published = $this->element['published'] ? explode(',', (string) $this->element['published']) : [0, 1];
        $name = (string) $this->element['name'];

        // Let's get the id for the current item, either category or content item.
        $jinput = Factory::getApplication()->input;

        // Load the category options for a given extension.

        // For categories the old category is the category id or 0 for new category.
        if ($this->element['parent'] || $jinput->get('option') == 'com_categories') {
            $oldCat = $jinput->get('id', 0);
            $oldParent = $this->form->getValue($name, 0);
            $extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('extension', 'com_content');
        } else // For items the old category is the category they are in when opened or 0 if new.
        {
            $oldCat = $this->form->getValue($name, 0);
            $extension = $this->element['extension'] ? (string) $this->element['extension'] : (string) $jinput->get('option', 'com_content');
        }

        // Account for case that a submitted form has a multi-value category id field (e.g. a filtering form), just use the first category
        $oldCat = \is_array($oldCat)
            ? (int) reset($oldCat)
            : (int) $oldCat;

        $db   = $this->getDatabase();
        $user = Factory::getUser();

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.title', 'text'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.published'),
                    $db->quoteName('a.lft'),
                    $db->quoteName('a.language'),
                ]
            )
            ->from($db->quoteName('#__categories', 'a'));

        // Filter by the extension type
        if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories') {
            $query->where('(' . $db->quoteName('a.extension') . ' = :extension OR ' . $db->quoteName('a.parent_id') . ' = 0)')
                ->bind(':extension', $extension);
        } else {
            $query->where($db->quoteName('a.extension') . ' = :extension')
                ->bind(':extension', $extension);
        }

        // Filter language
        if (!empty($this->element['language'])) {
            if (strpos($this->element['language'], ',') !== false) {
                $language = explode(',', $this->element['language']);
            } else {
                $language = $this->element['language'];
            }

            $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
        }

        // Filter on the published state
        $state = ArrayHelper::toInteger($published);
        $query->whereIn($db->quoteName('a.published'), $state);

        // Filter categories on User Access Level
        // Filter by access level on categories.
        if (!$user->authorise('core.admin')) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        $query->order($db->quoteName('a.lft') . ' ASC');

        // If parent isn't explicitly stated but we are in com_categories assume we want parents
        if ($oldCat != 0 && ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')) {
            // Prevent parenting to children of this item.
            // To rearrange parents and children move the children up, not the parents down.
            $query->join(
                'LEFT',
                $db->quoteName('#__categories', 'p'),
                $db->quoteName('p.id') . ' = :oldcat'
            )
                ->bind(':oldcat', $oldCat, ParameterType::INTEGER)
                ->where('NOT(' . $db->quoteName('a.lft') . ' >= ' . $db->quoteName('p.lft')
                    . ' AND ' . $db->quoteName('a.rgt') . ' <= ' . $db->quoteName('p.rgt') . ')');
        }

        // Get the options.
        $db->setQuery($query);

        try {
            $options = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');
        }

        // Pad the option text with spaces using depth level as a multiplier.
        for ($i = 0, $n = \count($options); $i < $n; $i++) {
            // Translate ROOT
            if ($this->element['parent'] == true || $jinput->get('option') == 'com_categories') {
                if ($options[$i]->level == 0) {
                    $options[$i]->text = Text::_('JGLOBAL_ROOT_PARENT');
                }
            }

            if ($options[$i]->published == 1) {
                $options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . $options[$i]->text;
            } else {
                $options[$i]->text = str_repeat('- ', !$options[$i]->level ? 0 : $options[$i]->level - 1) . '[' . $options[$i]->text . ']';
            }

            // Displays language code if not set to All
            if ($options[$i]->language !== '*') {
                $options[$i]->text = $options[$i]->text . ' (' . $options[$i]->language . ')';
            }
        }

        // For new items we want a list of categories you are allowed to create in.
        if ($oldCat == 0) {
            foreach ($options as $i => $option) {
                /*
                 * To take save or create in a category you need to have create rights for that category unless the item is already in that category.
                 * Unset the option if the user isn't authorised for it. In this field assets are always categories.
                 */
                if ($option->level != 0 && !$user->authorise('core.create', $extension . '.category.' . $option->value)) {
                    unset($options[$i]);
                }
            }
        } else {
            // If you have an existing category id things are more complex.
            /*
             * If you are only allowed to edit in this category but not edit.state, you should not get any
             * option to change the category parent for a category or the category for a content item,
             * but you should be able to save in that category.
             */
            foreach ($options as $i => $option) {
                $assetKey = $extension . '.category.' . $oldCat;

                if ($option->level != 0 && !isset($oldParent) && $option->value != $oldCat && !$user->authorise('core.edit.state', $assetKey)) {
                    unset($options[$i]);
                    continue;
                }

                if ($option->level != 0 && isset($oldParent) && $option->value != $oldParent && !$user->authorise('core.edit.state', $assetKey)) {
                    unset($options[$i]);
                    continue;
                }

                /*
                 * However, if you can edit.state you can also move this to another category for which you have
                 * create permission and you should also still be able to save in the current category.
                 */
                $assetKey = $extension . '.category.' . $option->value;

                if ($option->level != 0 && !isset($oldParent) && $option->value != $oldCat && !$user->authorise('core.create', $assetKey)) {
                    unset($options[$i]);
                    continue;
                }

                if ($option->level != 0 && isset($oldParent) && $option->value != $oldParent && !$user->authorise('core.create', $assetKey)) {
                    unset($options[$i]);
                }
            }
        }

        if (
            $oldCat != 0 && ($this->element['parent'] == true || $jinput->get('option') == 'com_categories')
            && !isset($options[0])
            && isset($this->element['show_root'])
        ) {
            $rowQuery = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('a.id', 'value'),
                        $db->quoteName('a.title', 'text'),
                        $db->quoteName('a.level'),
                        $db->quoteName('a.parent_id'),
                    ]
                )
                ->from($db->quoteName('#__categories', 'a'))
                ->where($db->quoteName('a.id') . ' = :aid')
                ->bind(':aid', $oldCat, ParameterType::INTEGER);
            $db->setQuery($rowQuery);
            $row = $db->loadObject();

            if ($row->parent_id == '1') {
                $parent = new \stdClass();
                $parent->text = Text::_('JGLOBAL_ROOT_PARENT');
                array_unshift($options, $parent);
            }

            array_unshift($options, HTMLHelper::_('select.option', '0', Text::_('JGLOBAL_ROOT')));
        }

        // Merge any additional options in the XML definition.
        return array_merge(parent::getOptions(), $options);
    }

    /**
     * Method to get the field input markup for a generic list.
     * Use the multiple attribute to enable multiselect.
     *
     * @return  string  The field input markup.
     *
     * @since   3.6
     */
    protected function getInput()
    {
        $data = $this->getLayoutData();

        $data['options']        = $this->getOptions();
        $data['allowCustom']    = $this->allowAdd;
        $data['customPrefix']   = $this->customPrefix;
        $data['refreshPage']    = (bool) $this->element['refresh-enabled'];
        $data['refreshCatId']   = (string) $this->element['refresh-cat-id'];
        $data['refreshSection'] = (string) $this->element['refresh-section'];

        $renderer = $this->getRenderer($this->layout);
        $renderer->setComponent('com_categories');
        $renderer->setClient(1);

        return $renderer->render($data);
    }
}
