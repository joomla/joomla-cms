<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Chrome Styles field.
 *
 * @since  3.0
 */
class ChromestyleField extends GroupedlistField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.0
     */
    public $type = 'ChromeStyle';

    /**
     * The client ID.
     *
     * @var    integer
     * @since  3.2
     */
    protected $clientId;

    /**
     * Method to get certain otherwise inaccessible properties from the form field object.
     *
     * @param   string  $name  The property name for which to get the value.
     *
     * @return  mixed  The property value or null.
     *
     * @since   3.2
     */
    public function __get($name)
    {
        if ($name === 'clientId') {
            return $this->clientId;
        }

        return parent::__get($name);
    }

    /**
     * Method to set certain otherwise inaccessible properties of the form field object.
     *
     * @param   string  $name   The property name for which to get the value.
     * @param   mixed   $value  The value of the property.
     *
     * @return  void
     *
     * @since   3.2
     */
    public function __set($name, $value)
    {
        switch ($name) {
            case 'clientId':
                $this->clientId = (int) $value;
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
        $result = parent::setup($element, $value, $group);

        if ($result === true) {
            // Get the client id.
            $clientId = $this->element['client_id'];

            if (!isset($clientId)) {
                $clientName = $this->element['client'];

                if (isset($clientName)) {
                    $client   = ApplicationHelper::getClientInfo($clientName, true);
                    $clientId = $client->id;
                }
            }

            if (!isset($clientId) && $this->form instanceof Form) {
                $clientId = $this->form->getValue('client_id');
            }

            $this->clientId = (int) $clientId;
        }

        return $result;
    }


    /**
     * Method to get the list of template chrome style options
     * grouped by template.
     *
     * @return  array[]  The field option objects as a nested array in groups.
     *
     * @since   3.0
     */
    protected function getGroups()
    {
        $groups = [];

        // Add Module Style Field
        $tmp            = '---' . Text::_('JLIB_FORM_VALUE_FROM_TEMPLATE') . '---';
        $groups[$tmp][] = HTMLHelper::_('select.option', '0', Text::_('JLIB_FORM_VALUE_INHERITED'));

        $templateStyles = $this->getTemplateModuleStyles();

        // Create one new option object for each available style, grouped by templates
        foreach ($templateStyles as $template => $styles) {
            $template          = ucfirst($template);
            $groups[$template] = [];

            foreach ($styles as $style) {
                $tmp                 = HTMLHelper::_('select.option', $template . '-' . $style, $style);
                $groups[$template][] = $tmp;
            }
        }

        return $groups;
    }

    /**
     * Method to get the templates module styles.
     *
     * @return  string[]  The array of styles, grouped by templates.
     *
     * @since   3.0
     */
    protected function getTemplateModuleStyles()
    {
        $moduleStyles = [];

        // Global Layouts
        $layouts = Folder::files(JPATH_SITE . '/layouts/chromes', '.*\.php');

        foreach ($layouts as &$layout) {
            $layout = basename($layout, '.php');
        }

        $moduleStyles['system'] = $layouts;

        $templates = $this->getTemplates();
        $path      = JPATH_ADMINISTRATOR;

        if ($this->clientId === 0) {
            $path = JPATH_SITE;
        }

        foreach ($templates as $template) {
            $chromeLayoutPath = $path . '/templates/' . $template->element . '/html/layouts/chromes';

            if (!is_dir(Path::clean($chromeLayoutPath))) {
                continue;
            }

            $layouts = Folder::files($chromeLayoutPath, '.*\.php');

            if ($layouts) {
                foreach ($layouts as &$layout) {
                    $layout = basename($layout, '.php');
                }

                $moduleStyles[$template->element] = $layouts;
            }
        }

        return $moduleStyles;
    }

    /**
     * Return a list of templates
     *
     * @return  object[]  List of templates
     *
     * @since   3.2.1
     */
    protected function getTemplates()
    {
        $db = $this->getDatabase();

        // Get the database object and a new query object.
        $query = $db->createQuery();

        // Build the query.
        $query->select(
            [
                $db->quoteName('element'),
                $db->quoteName('name'),
            ]
        )
            ->from($db->quoteName('#__extensions'))
            ->where(
                [
                    $db->quoteName('client_id') . ' = :clientId',
                    $db->quoteName('type') . ' = ' . $db->quote('template'),
                    $db->quoteName('enabled') . ' = 1',
                ]
            )
            ->bind(':clientId', $this->clientId, ParameterType::INTEGER);

        // Set the query and load the templates.
        $db->setQuery($query);

        return $db->loadObjectList('element');
    }
}
