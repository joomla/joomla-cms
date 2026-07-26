<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/**
 * Layout variables
 * -----------------
 * @var   array   $columns        Array of column definitions
 * @var   array   $data           Array of data rows
 * @var   string  $tableId        Optional table ID
 * @var   string  $tableClass     Optional additional CSS classes
 * @var   string  $caption        Optional table caption for accessibility
 * @var   bool    $striped        Whether to add striped rows (default: true)
 * @var   bool    $hover          Whether to add hover effect (default: true)
 * @var   bool    $responsive     Whether to make table responsive (default: true)
 */

// Extract layout data
$columns      = $displayData['columns'] ?? [];
$data         = $displayData['data'] ?? [];
$tableId      = $displayData['id'] ?? '';
$tableClass   = $displayData['class'] ?? '';
$caption      = $displayData['caption'] ?? '';
$striped      = $displayData['striped'] ?? true;
$hover        = $displayData['hover'] ?? true;
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

// Build table CSS classes
$cssClasses = ['table', 'mt-3'];
if ($striped) {
    $cssClasses[] = 'table-striped';
}
if ($hover) {
    $cssClasses[] = 'table-hover';
}
if (!empty($tableClass)) {
    $cssClasses[] = $tableClass;
}
?>
<div class="table-responsive" data-healthcheck-status="<?php echo $filterStatus; ?>">
    <table class="<?php echo implode(' ', $cssClasses); ?>"<?php echo $tableId ? ' id="' . htmlspecialchars($tableId, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
        <?php if ($caption) : ?>
            <caption><?php echo htmlspecialchars($caption, ENT_QUOTES, 'UTF-8'); ?></caption>
        <?php endif; ?>
        <thead>
            <tr>
                <?php foreach ($columns as $column) : ?>
                    <?php
                    $title = $column['title'] ?? $column['key'] ?? '';
                    $width = $column['width'] ?? '';
                    $align = $column['align'] ?? '';
                    $scope = $column['scope'] ?? 'col';
                    ?>
                    <th scope="<?php echo htmlspecialchars($scope, ENT_QUOTES, 'UTF-8'); ?>"
                        <?php echo $width ? ' class="' . htmlspecialchars($width, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
                        <?php echo $align ? ' style="text-align: ' . htmlspecialchars($align, ENT_QUOTES, 'UTF-8') . ';"' : ''; ?>>
                        <?php echo htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?>
                    </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data as $rowIndex => $item) : ?>
                <tr>
                    <?php foreach ($columns as $columnIndex => $column) : ?>
                        <?php
                        $align = $column['align'] ?? '';
                        $cellClass = $column['cellClass'] ?? '';
                        if ($cellClass && is_callable($column['cellClass'])) {
                            $cellClass = call_user_func($column['cellClass'], $item, $rowIndex, $columnIndex);
                        }
                        ?>
                        <td<?php echo $cellClass ? ' class="' . htmlspecialchars($cellClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>
                            <?php echo $align ? ' style="text-align: ' . htmlspecialchars($align, ENT_QUOTES, 'UTF-8') . ';"' : ''; ?>>
                            <?php echo LayoutHelper::render('joomla.healthchecks.table_cell', [
                                'column'   => $column,
                                'item'     => $item,
                                'rowIndex' => $rowIndex,
                            ]); ?>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
