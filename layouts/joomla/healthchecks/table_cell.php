<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Layout variables
 * -----------------
 * @var   array  $column
 * @var   mixed  $item
 * @var   int    $rowIndex
 */

$column   = $displayData['column'] ?? [];
$item     = $displayData['item'] ?? null;
$rowIndex = $displayData['rowIndex'] ?? 0;

$key   = $column['key'] ?? '';
$type  = $column['type'] ?? 'text';
$value = \is_object($item) ? ($item->$key ?? '') : ($item[$key] ?? '');

// Security note: only execute trusted Closure instances here.
// We intentionally avoid generic `is_callable()` / `call_user_func()` usage so
// configuration cannot resolve arbitrary string or method callables at runtime.
$resolveOption = static function (string $option, $default = null) use ($column, $value, $item, $rowIndex) {
    $resolved = $column[$option] ?? $default;

    if ($resolved instanceof \Closure) {
        $resolved = $resolved($value, $item, $rowIndex);
    }

    return $resolved;
};

switch ($type) {
    case 'badge':
        $badgeClass = $resolveOption('badgeClass', 'secondary');

        echo '<span class="badge bg-' . htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';
        break;

    case 'link':
        $url   = $resolveOption('url', '');
        $title = $resolveOption('linkTitle', '');

        echo '<a href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"' .
            ($title ? ' title="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '"' : '') . '>' .
            htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</a>';
        break;

    case 'date':
        $format = $column['dateFormat'] ?? Text::_('DATE_FORMAT_LC4');

        echo HTMLHelper::_('date', $value, $format);
        break;

    case 'boolean':
        $trueText   = $column['trueText'] ?? Text::_('JYES');
        $falseText  = $column['falseText'] ?? Text::_('JNO');
        $trueClass  = $column['trueClass'] ?? 'success';
        $falseClass = $column['falseClass'] ?? 'danger';
        $isTrue     = (bool) $value;

        echo '<span class="badge bg-' . ($isTrue ? $trueClass : $falseClass) . '">' .
            ($isTrue ? $trueText : $falseText) . '</span>';
        break;

    case 'progress':
        $percentage    = (float) $value;
        $progressClass = $resolveOption('progressClass', 'primary');

        echo '<div class="progress" style="height: 20px;">'
            . '<div class="progress-bar bg-' . htmlspecialchars($progressClass, ENT_QUOTES, 'UTF-8') . '"'
            . ' role="progressbar"'
            . ' style="width: ' . $percentage . '%;"'
            . ' aria-valuenow="' . $percentage . '"'
            . ' aria-valuemin="0"'
            . ' aria-valuemax="100">' . $percentage . '%'
            . '</div>'
            . '</div>';
        break;

    case 'icon':
        $iconClass = $resolveOption('iconClass', 'fas fa-info');

        echo '<span class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>';
        break;

    case 'custom':
        $renderer = $column['renderer'] ?? null;

        if ($renderer instanceof \Closure) {
            echo $renderer($value, $item, $rowIndex, $column);
            break;
        }

        echo htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        break;

    case 'text':
    default:
        $maxLength = $column['maxLength'] ?? null;
        $text      = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        if ($maxLength && \strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
        }

        echo $text;
}
