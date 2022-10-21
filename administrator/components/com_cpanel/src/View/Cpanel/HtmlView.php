<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Cpanel\Administrator\View\Cpanel;

use Joomla\CMS\Factory;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * HTML View class for the Cpanel component
 *
 * @since  1.0
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Array of cpanel modules
     *
     * @var  array
     */
    protected $modules = null;

    /**
     * Array of cpanel modules
     *
     * @var  array
     */
    protected $quickicons = null;

    /**
     * Moduleposition to load
     *
     * @var  string
     */
    protected $position = null;

    /**
     * Execute and display a template script.
     *
     * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
     *
     * @return  void
     */
    public function display($tpl = null)
    {
        $app = Factory::getApplication();
        $dashboard = $app->input->getCmd('dashboard', '');

        $position = OutputFilter::stringURLSafe($dashboard);

        // Generate a title for the view cpanel
        if (!empty($dashboard)) {
            $parts     = explode('.', $dashboard);
            $component = $parts[0];

            if (strpos($component, 'com_') === false) {
                $component = 'com_' . $component;
            }

            // Need to load the language file
            $lang = Factory::getLanguage();
            $lang->load($component, JPATH_BASE)
            || $lang->load($component, JPATH_ADMINISTRATOR . '/components/' . $component);
            $lang->load($component);

            // Lookup dashboard attributes from component manifest file
            $manifestFile = JPATH_ADMINISTRATOR . '/components/' . $component . '/' . str_replace('com_', '', $component) . '.xml';

            if (is_file($manifestFile)) {
                $manifest = simplexml_load_file($manifestFile);

                if ($dashboardManifests = $manifest->dashboards) {
                    foreach ($dashboardManifests->children() as $dashboardManifest) {
                        if ((string) $dashboardManifest === $dashboard) {
                            $title = Text::_((string) $dashboardManifest->attributes()->title);
                            $icon  = (string) $dashboardManifest->attributes()->icon;

                            break;
                        }
                    }
                }
            }

            if (empty($title)) {
                // Try building a title
                $prefix = strtoupper($component) . '_DASHBOARD';

                $sectionkey = !empty($parts[1]) ? '_' . strtoupper($parts[1]) : '';
                $key = $prefix . $sectionkey . '_TITLE';
                $keyIcon = $prefix . $sectionkey . '_ICON';

                // Search for a component title
                if ($lang->hasKey($key)) {
                    $title = Text::_($key);
                } else {
                    // Try with a string from CPanel
                    $key = 'COM_CPANEL_DASHBOARD_' . $parts[0] . '_TITLE';

                    if ($lang->hasKey($key)) {
                        $title = Text::_($key);
                    } else {
                        $title = Text::_('COM_CPANEL_DASHBOARD_BASE_TITLE');
                    }
                }

                // Define the icon
                if (empty($parts[1])) {
                    // Default core icons.
                    if ($parts[0] === 'components') {
                        $icon = 'icon-puzzle-piece';
                    } elseif ($parts[0] === 'system') {
                        $icon = 'icon-wrench';
                    } elseif ($parts[0] === 'help') {
                        $icon = 'icon-info-circle';
                    } elseif ($lang->hasKey($keyIcon)) {
                        $icon = Text::_($keyIcon);
                    } else {
                        $icon = 'icon-home';
                    }
                } elseif ($lang->hasKey($keyIcon)) {
                    $icon = Text::_($keyIcon);
                }
            }
        } else {
            // Home Dashboard
            $title = Text::_('COM_CPANEL_DASHBOARD_BASE_TITLE');
            $icon = 'icon-home';
        }

        // Set toolbar items for the page
        ToolbarHelper::title($title, $icon . ' cpanel');
        ToolbarHelper::help('screen.cpanel');

        // Display the cpanel modules
        $this->position = $position ? 'cpanel-' . $position : 'cpanel';
        $this->modules = ModuleHelper::getModules($this->position);

        $quickicons = $position ? 'icon-' . $position : 'icon';
        $this->quickicons = ModuleHelper::getModules($quickicons);

        parent::display($tpl);
    }
}
