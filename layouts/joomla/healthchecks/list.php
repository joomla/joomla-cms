<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Layout variables
 * -----------------
 * @var   array   $items          Array of items to display
 * @var   string  $listId         Optional list ID
 * @var   string  $listClass      Optional additional CSS classes
 * @var   string  $itemClass      Optional CSS class for list items
 * @var   string  $type           List type: 'ul' (default), 'ol', or 'div'
 * @var   callable $renderer      Optional callback function to render each item
 */

// Extract layout data
$items      = $displayData['items'] ?? [];
$listId     = $displayData['id'] ?? '';
$listClass  = $displayData['class'] ?? '';
$itemClass  = $displayData['itemClass'] ?? '';
$type       = $displayData['type'] ?? 'ul';
$renderer   = $displayData['renderer'] ?? null;
$filterStatus = 'healthy';

if (isset($displayData['status'])) {
    switch ($displayData['status']) {
        case 'warning':
            $filterStatus = 'warning';
            break;
        case 'error':
            $filterStatus = 'critical';
            break;
        case 'success':
        default:
            $filterStatus = 'healthy';
    }
}

// Build CSS classes
$cssClasses = ['list'];
if (!empty($listClass)) {
    $cssClasses[] = $listClass;
}

// Determine the wrapper tag based on type
$wrapperTag = in_array($type, ['ul', 'ol', 'div']) ? $type : 'ul';
$itemTag = ($wrapperTag === 'div') ? 'div' : 'li';
?>
<<?php echo $wrapperTag; ?> class="<?php echo implode(' ', $cssClasses); ?>"<?php echo $listId ? ' id="' . htmlspecialchars($listId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?> data-healthcheck-status="<?php echo $filterStatus; ?>">
    <?php foreach ($items as $index => $item) : ?>
        <<?php echo $itemTag; ?><?php echo $itemClass ? ' class="' . htmlspecialchars($itemClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
            <?php
            // Use custom renderer if provided
            if ($renderer && is_callable($renderer)) {
                echo call_user_func($renderer, $item, $index);
            } else {
                // Fallback to basic rendering
                if (is_object($item)) {
                    echo htmlspecialchars($item->title ?? $item->name ?? $item->label ?? 'Item ' . ($index + 1), ENT_QUOTES, 'UTF-8');
                } elseif (is_array($item)) {
                    echo htmlspecialchars($item['title'] ?? $item['name'] ?? $item['label'] ?? 'Item ' . ($index + 1), ENT_QUOTES, 'UTF-8');
                } else {
                    echo htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                }
            }
            ?>
        </<?php echo $itemTag; ?>>
    <?php endforeach; ?>
</<?php echo $wrapperTag; ?>>
