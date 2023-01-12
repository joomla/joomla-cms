<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Table\Table;

/*
 * References
 *  Support plugins in your component
 * - https://docs.joomla.org/Special:MyLanguage/Supporting_plugins_in_your_component
 *
 * Best way for JSON output
 * - https://groups.google.com/d/msg/joomla-dev-cms/WsC0nA9Fixo/Ur-gPqpqh-EJ
 */

/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$app->allowCache(false);

// Prevent the api url from being indexed
$app->setHeader('X-Robots-Tag', 'noindex, nofollow');

// JInput object
$input = $app->input;

// Requested format passed via URL
$format = strtolower($input->getWord('format', ''));

// Initialize default response and module name
$results = null;
$parts   = null;

// Check for valid format
if (!$format) {
    $results = new InvalidArgumentException(Text::_('COM_AJAX_SPECIFY_FORMAT'), 404);
} elseif ($input->get('module')) {
    /**
     * Module support.
     *
     * modFooHelper::getAjax() is called where 'foo' is the value
     * of the 'module' variable passed via the URL
     * (i.e. index.php?option=com_ajax&module=foo).
     *
     */
    $module   = $input->get('module');
    $table    = Table::getInstance('extension');
    $moduleId = $table->find(['type' => 'module', 'element' => 'mod_' . $module]);

    if ($moduleId && $table->load($moduleId) && $table->enabled) {
        $helperFile = JPATH_BASE . '/modules/mod_' . $module . '/helper.php';

        if (strpos($module, '_')) {
            $parts = explode('_', $module);
        } elseif (strpos($module, '-')) {
            $parts = explode('-', $module);
        }

        if ($parts) {
            $class = 'Mod';

            foreach ($parts as $part) {
                $class .= ucfirst($part);
            }

            $class .= 'Helper';
        } else {
            $class = 'Mod' . ucfirst($module) . 'Helper';
        }

        $method = $input->get('method') ?: 'get';

        $moduleInstance = $app->bootModule('mod_' . $module, $app->getName());

        if ($moduleInstance instanceof \Joomla\CMS\Helper\HelperFactoryInterface && $helper = $moduleInstance->getHelper(substr($class, 3))) {
            $results = method_exists($helper, $method . 'Ajax') ? $helper->{$method . 'Ajax'}() : null;
        }

        if ($results === null && is_file($helperFile)) {
            JLoader::register($class, $helperFile);

            if (method_exists($class, $method . 'Ajax')) {
                // Load language file for module
                $basePath = JPATH_BASE;
                $lang     = Factory::getLanguage();
                $lang->load('mod_' . $module, $basePath)
                ||  $lang->load('mod_' . $module, $basePath . '/modules/mod_' . $module);

                try {
                    $results = call_user_func($class . '::' . $method . 'Ajax');
                } catch (Exception $e) {
                    $results = $e;
                }
            } else {
                // Method does not exist
                $results = new LogicException(Text::sprintf('COM_AJAX_METHOD_NOT_EXISTS', $method . 'Ajax'), 404);
            }
        } elseif ($results === null) {
            // The helper file does not exist
            $results = new RuntimeException(Text::sprintf('COM_AJAX_FILE_NOT_EXISTS', 'mod_' . $module . '/helper.php'), 404);
        }
    } else {
        // Module is not published, you do not have access to it, or it is not assigned to the current menu item
        $results = new LogicException(Text::sprintf('COM_AJAX_MODULE_NOT_ACCESSIBLE', 'mod_' . $module), 404);
    }
} elseif ($input->get('plugin')) {
    /**
     * Plugin support by default is based on the "Ajax" plugin group.
     * An optional 'group' variable can be passed via the URL.
     *
     * The plugin event triggered is onAjaxFoo, where 'foo' is
     * the value of the 'plugin' variable passed via the URL
     * (i.e. index.php?option=com_ajax&plugin=foo)
     *
     */
    $group      = $input->get('group', 'ajax');
    PluginHelper::importPlugin($group);
    $plugin     = ucfirst($input->get('plugin'));

    try {
        $results = Factory::getApplication()->triggerEvent('onAjax' . $plugin);
    } catch (Exception $e) {
        $results = $e;
    }
} elseif ($input->get('template')) {
    /**
     * Template support.
     *
     * tplFooHelper::getAjax() is called where 'foo' is the value
     * of the 'template' variable passed via the URL
     * (i.e. index.php?option=com_ajax&template=foo).
     *
     */
    $template   = $input->get('template');
    $table      = Table::getInstance('extension');
    $templateId = $table->find(['type' => 'template', 'element' => $template]);

    if ($templateId && $table->load($templateId) && $table->enabled) {
        $basePath   = ($table->client_id) ? JPATH_ADMINISTRATOR : JPATH_SITE;
        $helperFile = $basePath . '/templates/' . $template . '/helper.php';

        if (strpos($template, '_')) {
            $parts = explode('_', $template);
        } elseif (strpos($template, '-')) {
            $parts = explode('-', $template);
        }

        if ($parts) {
            $class = 'Tpl';

            foreach ($parts as $part) {
                $class .= ucfirst($part);
            }

            $class .= 'Helper';
        } else {
            $class = 'Tpl' . ucfirst($template) . 'Helper';
        }

        $method = $input->get('method') ?: 'get';

        if (is_file($helperFile)) {
            JLoader::register($class, $helperFile);

            if (method_exists($class, $method . 'Ajax')) {
                // Load language file for template
                $lang = Factory::getLanguage();
                $lang->load('tpl_' . $template, $basePath)
                ||  $lang->load('tpl_' . $template, $basePath . '/templates/' . $template);

                try {
                    $results = call_user_func($class . '::' . $method . 'Ajax');
                } catch (Exception $e) {
                    $results = $e;
                }
            } else {
                // Method does not exist
                $results = new LogicException(Text::sprintf('COM_AJAX_METHOD_NOT_EXISTS', $method . 'Ajax'), 404);
            }
        } else {
            // The helper file does not exist
            $results = new RuntimeException(Text::sprintf('COM_AJAX_FILE_NOT_EXISTS', 'tpl_' . $template . '/helper.php'), 404);
        }
    } else {
        // Template is not assigned to the current menu item
        $results = new LogicException(Text::sprintf('COM_AJAX_TEMPLATE_NOT_ACCESSIBLE', 'tpl_' . $template), 404);
    }
}

// Return the results in the desired format
switch ($format) {
    // JSONinzed
    case 'json':
        echo new JsonResponse($results, null, false, $input->get('ignoreMessages', true, 'bool'));

        break;

    // Handle as raw format
    default:
        // Output exception
        if ($results instanceof Exception) {
            // Log an error
            Log::add($results->getMessage(), Log::ERROR);

            // Set status header code
            $app->setHeader('status', $results->getCode(), true);

            // Echo exception type and message
            $out = get_class($results) . ': ' . $results->getMessage();
        } elseif (is_scalar($results)) {
            // Output string/ null
            $out = (string) $results;
        } else {
            // Output array/ object
            $out = implode((array) $results);
        }

        echo $out;

        break;
}
