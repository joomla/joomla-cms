<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  mod_healthcheck
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\Healthcheck\Administrator\Helper;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Module\Healthcheck\Administrator\Event\HealthChecksEvent;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_healthcheck
 *
 * @since  __DEPLOY_VERSION__
 */
class HealthCheckHelper
{
    /**
     * Stack to hold gauges
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $gauges = [];

    /**
     * Stack to hold buttons
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $buttons = [];

    /**
     * Stack to hold lists
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $lists = [];

    /**
     * Stack to hold tables
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $tables = [];

    /**
     * Stack to hold reports
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $reports = [];

    /**
     * Stack to hold leading information
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $leading = [];

    /**
     * Stack to hold footer information
     *
     * @var     array[]
     * @since   __DEPLOY_VERSION__
     */
    protected $footer = [];

    /**
     * Generic helper method to get health check data from plugins
     *
     * @param   Registry             $params          The module parameters
     * @param   string               $type            The type of data (gauges, buttons, lists, etc.)
     * @param   string               $eventName       The event name to trigger
     * @param   array                $defaults        Default values for the data items
     * @param   array                $requiredFields  Required fields for validation
     * @param   CMSApplication|null  $application     The application
     *
     * @return  array  An array of health check data items
     *
     * @since   __DEPLOY_VERSION__
     */
    protected function getHealthCheckData(
        Registry $params,
        string $type,
        string $eventName,
        array $defaults,
        array $requiredFields,
        ?CMSApplication $application = null
    ) {
        if ($application == null) {
            $application = Factory::getApplication();
        }

        $key      = (string) $params;
        $context  = (string) $params->get('context', 'general');
        $property = $type; // gauges, buttons, lists, etc.

        if (!isset($this->{$property}[$key])) {
            // Load mod_healthcheck language file in case this method is called before rendering the module
            $application->getLanguage()->load('mod_healthcheck');

            $this->{$property}[$key] = [];

            PluginHelper::importPlugin('healthcheck');

            $eventResult = $application->getDispatcher()->dispatch(
                $eventName,
                new HealthChecksEvent($eventName, ['context' => $context])
            );

            $result = $eventResult->getArgument('result');

            if (empty($result)) {
                return [];
            }

            foreach ($result as $response) {
                if (!\is_array($response)) {
                    continue;
                }

                foreach ($response as $item) {
                    $item = array_merge($defaults, $item);

                    // Validate required fields
                    $isValid = true;
                    foreach ($requiredFields as $fieldGroup) {
                        $hasAnyRequired = false;
                        foreach ($fieldGroup as $field) {
                            if (!\is_null($item[$field])) {
                                $hasAnyRequired = true;
                                break;
                            }
                        }
                        if (!$hasAnyRequired) {
                            $isValid = false;
                            break;
                        }
                    }

                    if ($isValid) {
                        $this->{$property}[$key][] = $item;
                    }
                }
            }
        }

        return $this->{$property}[$key];
    }

    /**
     * Helper method to return gauge list.
     *
     * This method returns the array by reference so it can be
     * used to add custom gauges or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of gauges
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getGauges(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'score'                   => null,
            'unit'                    => null,
            'score_min'               => null,
            'score_max'               => null,
            'score_threshold_warning' => null,
            'score_threshold_success' => null,
            'label'                   => null,
            'sublabel'                => null,
            'note'                    => null,
            'link'                    => null,
            'link_title'              => null,
            'access'                  => true,
            'class'                   => null,
            'group'                   => 'general',
        ];

        $requiredFields = [
            ['score'], // Must have score
            ['unit'],  // Must have unit
        ];

        return $this->getHealthCheckData(
            $params,
            'gauges',
            'onHealthcheckGetGauges',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return button list.
     *
     * This method returns the array by reference so it can be
     * used to add custom buttons or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of buttons
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getButtons(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'link'        => null,
            'image'       => null,
            'amount'      => null,
            'text'        => null,
            'name'        => null,
            'linkadd'     => null,
            'linkaddicon' => null,
            'access'      => true,
            'status'      => null,
            'group'       => 'general',
        ];

        $requiredFields = [
            ['link'],
            ['text', 'name'], // Must have either text or name
        ];

        return $this->getHealthCheckData(
            $params,
            'buttons',
            'onHealthcheckGetIcons',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return list list.
     *
     * This method returns the array by reference so it can be
     * used to add custom lists or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of lists
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getLists(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'items'  => null,
            'access' => true,
            'class'  => null,
            'group'  => 'general',
        ];

        $requiredFields = [
            ['items'],
        ];

        return $this->getHealthCheckData(
            $params,
            'lists',
            'onHealthcheckGetLists',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return table list.
     *
     * This method returns the array by reference so it can be
     * used to add custom tables or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of tables
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getTables(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'columns' => null,
            'data'    => null,
            'caption' => null,
            'access'  => true,
            'class'   => null,
            'group'   => 'general',
            'helper'  => $this,
        ];

        $requiredFields = [
            ['columns'],
            ['data'], // Must have either text or name
        ];

        return $this->getHealthCheckData(
            $params,
            'tables',
            'onHealthcheckGetTables',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return report list.
     *
     * This method returns the array by reference so it can be
     * used to add custom reports or remove default ones.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of reports
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getReports(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'link'    => null,
            'image'   => null,
            'text'    => null,
            'name'    => null,
            'linkadd' => null,
            'access'  => true,
            'class'   => null,
            'group'   => 'general',
        ];

        $requiredFields = [
            ['link'],
            ['text', 'name'], // Must have either text or name
        ];

        return $this->getHealthCheckData(
            $params,
            'reports',
            'onHealthcheckGetReports',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return leading information.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of leading information
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getLeading(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'info'   => null,
            'access' => true,
            'class'  => null,
            'group'  => 'general',
        ];

        $requiredFields = [
            ['info'], // Must have info
        ];

        return $this->getHealthCheckData(
            $params,
            'leading',
            'onHealthcheckGetLeading',
            $defaults,
            $requiredFields,
            $application
        );
    }

    /**
     * Helper method to return footer information.
     *
     * @param   Registry         $params       The module parameters
     * @param   ?CMSApplication  $application  The application
     *
     * @return  array  An array of footer information
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getFooter(Registry $params, ?CMSApplication $application = null)
    {
        $defaults = [
            'info'   => null,
            'access' => true,
            'class'  => null,
            'group'  => 'general',
        ];

        $requiredFields = [
            ['info'], // Must have info
        ];

        return $this->getHealthCheckData(
            $params,
            'footer',
            'onHealthcheckGetFooter',
            $defaults,
            $requiredFields,
            $application
        );
    }

    // Function to render cell content based on column type
    public function renderTableCellContent($column, $item, $rowIndex)
    {
        $key   = $column['key'] ?? '';
        $type  = $column['type'] ?? 'text';
        $value = \is_object($item) ? ($item->$key ?? '') : ($item[$key] ?? '');

        switch ($type) {
            case 'badge':
                $badgeClass = $column['badgeClass'] ?? 'secondary';
                if (\is_callable($column['badgeClass'])) {
                    $badgeClass = \call_user_func($column['badgeClass'], $value, $item, $rowIndex);
                }

                return '<span class="badge bg-' . htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';

            case 'link':
                $url = $column['url'] ?? '';
                if (\is_callable($column['url'])) {
                    $url = \call_user_func($column['url'], $value, $item, $rowIndex);
                }
                $title = $column['linkTitle'] ?? '';
                if (\is_callable($column['linkTitle'])) {
                    $title = \call_user_func($column['linkTitle'], $value, $item, $rowIndex);
                }

                return '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' .
                    ($title ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '') . '>' .
                    htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</a>';

            case 'date':
                $format = $column['dateFormat'] ?? Text::_('DATE_FORMAT_LC4');

                return HTMLHelper::_('date', $value, $format);

            case 'boolean':
                $trueText   = $column['trueText'] ?? Text::_('JYES');
                $falseText  = $column['falseText'] ?? Text::_('JNO');
                $trueClass  = $column['trueClass'] ?? 'success';
                $falseClass = $column['falseClass'] ?? 'danger';
                $isTrue     = (bool) $value;

                return '<span class="badge bg-' . ($isTrue ? $trueClass : $falseClass) . '">' .
                    ($isTrue ? $trueText : $falseText) . '</span>';

            case 'progress':
                $percentage    = (float) $value;
                $progressClass = $column['progressClass'] ?? 'primary';
                if (\is_callable($column['progressClass'])) {
                    $progressClass = \call_user_func($column['progressClass'], $value, $item, $rowIndex);
                }

                return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar bg-' . htmlspecialchars($progressClass, ENT_QUOTES, 'UTF-8') . '"
                             role="progressbar"
                             style="width: ' . $percentage . '%;"
                             aria-valuenow="' . $percentage . '"
                             aria-valuemin="0"
                             aria-valuemax="100">' . $percentage . '%
                        </div>
                    </div>';

            case 'icon':
                $iconClass = $column['iconClass'] ?? 'fas fa-info';
                if (\is_callable($column['iconClass'])) {
                    $iconClass = \call_user_func($column['iconClass'], $value, $item, $rowIndex);
                }

                return '<span class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>';

            case 'custom':
                if (isset($column['renderer']) && \is_callable($column['renderer'])) {
                    return \call_user_func($column['renderer'], $value, $item, $rowIndex, $column);
                }

                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

            case 'text':
            default:
                $maxLength = $column['maxLength'] ?? null;
                $text      = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                if ($maxLength && \strlen($text) > $maxLength) {
                    $text = substr($text, 0, $maxLength) . '...';
                }

                return $text;
        }
    }
}
