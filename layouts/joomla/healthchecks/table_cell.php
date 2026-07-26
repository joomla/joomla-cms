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

switch ($type) {
    case 'badge':
        $badgeClass = $column['badgeClass'] ?? 'secondary';
        if (isset($column['badgeClass']) && \is_callable($column['badgeClass'])) {
            $badgeClass = \call_user_func($column['badgeClass'], $value, $item, $rowIndex);
        }

        echo '<span class="badge bg-' . htmlspecialchars($badgeClass, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</span>';
        break;

    case 'link':
        $url = $column['url'] ?? '';
        if (isset($column['url']) && \is_callable($column['url'])) {
            $url = \call_user_func($column['url'], $value, $item, $rowIndex);
        }

        $title = $column['linkTitle'] ?? '';
        if (isset($column['linkTitle']) && \is_callable($column['linkTitle'])) {
            $title = \call_user_func($column['linkTitle'], $value, $item, $rowIndex);
        }

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
        $progressClass = $column['progressClass'] ?? 'primary';
        if (isset($column['progressClass']) && \is_callable($column['progressClass'])) {
            $progressClass = \call_user_func($column['progressClass'], $value, $item, $rowIndex);
        }

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
        $iconClass = $column['iconClass'] ?? 'fas fa-info';
        if (isset($column['iconClass']) && \is_callable($column['iconClass'])) {
            $iconClass = \call_user_func($column['iconClass'], $value, $item, $rowIndex);
        }

        echo '<span class="' . htmlspecialchars($iconClass, ENT_QUOTES, 'UTF-8') . '" aria-hidden="true"></span>';
        break;

    case 'custom':
        if (isset($column['renderer']) && \is_callable($column['renderer'])) {
            echo \call_user_func($column['renderer'], $value, $item, $rowIndex, $column);
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

