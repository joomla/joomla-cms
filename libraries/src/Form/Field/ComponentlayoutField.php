<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Form\Field;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Form Field to display a list of the layouts for a component view from
 * the extension or template overrides.
 *
 * @since  1.6
 */
class ComponentlayoutField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    protected $type = 'ComponentLayout';

    /**
     * Method to get the field input for a component layout field.
     *
     * @return  string   The field input.
     *
     * @since   1.6
     */
    protected function getInput()
    {
        // Get the client id.
        $clientId = $this->element['client_id'];

        if ($clientId === null && $this->form instanceof Form) {
            $clientId = $this->form->getValue('client_id');
        }

        $clientId = (int) $clientId;

        $client = ApplicationHelper::getClientInfo($clientId);

        // Get the extension.
        $extension = (string) $this->element['extension'];

        if (empty($extension) && ($this->form instanceof Form)) {
            $extension = $this->form->getValue('extension');
        }

        $extension = preg_replace('#\W#', '', $extension);

        $template = (string) $this->element['template'];
        $template = preg_replace('#\W#', '', $template);

        $template_style_id = 0;

        if ($this->form instanceof Form) {
            $template_style_id = $this->form->getValue('template_style_id', null, 0);
            $template_style_id = (int) preg_replace('#\W#', '', $template_style_id);
        }

        $view = (string) $this->element['view'];
        $view = preg_replace('#\W#', '', $view);

        // If a template, extension and view are present build the options.
        if ($extension && $view && $client) {
            // Load language file
            $lang = Factory::getLanguage();
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR)
            || $lang->load($extension . '.sys', JPATH_ADMINISTRATOR . '/components/' . $extension);

            // Get the database object and a new query object.
            $db    = $this->getDatabase();
            $query = $db->getQuery(true);

            // Build the query.
            $query->select(
                [
                    $db->quoteName('e.element'),
                    $db->quoteName('e.name'),
                ]
            )
                ->from($db->quoteName('#__extensions', 'e'))
                ->where(
                    [
                        $db->quoteName('e.client_id') . ' = :clientId',
                        $db->quoteName('e.type') . ' = ' . $db->quote('template'),
                        $db->quoteName('e.enabled') . ' = 1',
                    ]
                )
                ->bind(':clientId', $clientId, ParameterType::INTEGER);

            if ($template) {
                $query->where($db->quoteName('e.element') . ' = :template')
                    ->bind(':template', $template);
            }

            if ($template_style_id) {
                $query->join('LEFT', $db->quoteName('#__template_styles', 's'), $db->quoteName('s.template') . ' = ' . $db->quoteName('e.element'))
                    ->where($db->quoteName('s.id') . ' = :style')
                    ->bind(':style', $template_style_id, ParameterType::INTEGER);
            }

            // Set the query and load the templates.
            $db->setQuery($query);
            $templates = $db->loadObjectList('element');

            // Build the search paths for component layouts.
            $component_path = Path::clean($client->path . '/components/' . $extension . '/tmpl/' . $view);

            // Check if the new layouts folder exists, else use the old one
            if (!is_dir($component_path)) {
                $component_path = Path::clean($client->path . '/components/' . $extension . '/views/' . $view . '/tmpl');
            }

            // Prepare array of component layouts
            $component_layouts = [];

            // Prepare the grouped list
            $groups = [];

            // Add a Use Global option if useglobal="true" in XML file
            if ((string) $this->element['useglobal'] === 'true') {
                $groups[Text::_('JOPTION_FROM_STANDARD')]['items'][] = HTMLHelper::_('select.option', '', Text::_('JGLOBAL_USE_GLOBAL'));
            }

            // Add the layout options from the component path.
            if (is_dir($component_path) && ($component_layouts = Folder::files($component_path, '^[^_]*\.xml$', false, true))) {
                // Create the group for the component
                $groups['_']          = [];
                $groups['_']['id']    = $this->id . '__';
                $groups['_']['text']  = Text::sprintf('JOPTION_FROM_COMPONENT');
                $groups['_']['items'] = [];

                foreach ($component_layouts as $i => $file) {
                    // Attempt to load the XML file.
                    if (!$xml = simplexml_load_file($file)) {
                        unset($component_layouts[$i]);

                        continue;
                    }

                    // Get the help data from the XML file if present.
                    if (!$menu = $xml->xpath('layout[1]')) {
                        unset($component_layouts[$i]);

                        continue;
                    }

                    $menu = $menu[0];

                    // Add an option to the component group
                    $value                  = basename($file, '.xml');
                    $component_layouts[$i]  = $value;
                    $text                   = isset($menu['option']) ? Text::_($menu['option']) : (isset($menu['title']) ? Text::_($menu['title']) : $value);
                    $groups['_']['items'][] = HTMLHelper::_('select.option', '_:' . $value, $text);
                }
            }

            // Loop on all templates
            if ($templates) {
                foreach ($templates as $template) {
                    // Load language file
                    $lang->load('tpl_' . $template->element . '.sys', $client->path)
                        || $lang->load('tpl_' . $template->element . '.sys', $client->path . '/templates/' . $template->element);

                    $template_path = Path::clean(
                        $client->path
                        . '/templates/'
                        . $template->element
                        . '/html/'
                        . $extension
                        . '/'
                        . $view
                    );

                    // Add the layout options from the template path.
                    if (is_dir($template_path) && ($files = Folder::files($template_path, '^[^_]*\.php$', false, true))) {
                        foreach ($files as $i => $file) {
                            // Remove layout files that exist in the component folder
                            if (\in_array(basename($file, '.php'), $component_layouts)) {
                                unset($files[$i]);
                            }
                        }

                        if (\count($files)) {
                            // Create the group for the template
                            $groups[$template->name]          = [];
                            $groups[$template->name]['id']    = $this->id . '_' . $template->element;
                            $groups[$template->name]['text']  = Text::sprintf('JOPTION_FROM_TEMPLATE', $template->name);
                            $groups[$template->name]['items'] = [];

                            foreach ($files as $file) {
                                // Add an option to the template group
                                $value = basename($file, '.php');
                                $text  = $lang
                                    ->hasKey(
                                        $key = strtoupper(
                                            'TPL_'
                                            . $template->name
                                            . '_'
                                            . $extension
                                            . '_'
                                            . $view
                                            . '_LAYOUT_'
                                            . $value
                                        )
                                    )
                                    ? Text::_($key) : $value;
                                $groups[$template->name]['items'][] = HTMLHelper::_('select.option', $template->element . ':' . $value, $text);
                            }
                        }
                    }
                }
            }

            // Compute attributes for the grouped list
            $attr = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
            $attr .= $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';

            // Prepare HTML code
            $html = [];

            // Compute the current selected values
            $selected = [$this->value];

            // Add a grouped list
            $html[] = HTMLHelper::_(
                'select.groupedlist',
                $groups,
                $this->name,
                ['id' => $this->id, 'group.id' => 'id', 'list.attr' => $attr, 'list.select' => $selected]
            );

            return implode($html);
        }

        return '';
    }
}
