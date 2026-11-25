<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

// Strip extension if given
$icon = empty($displayData['icon']) ? 'dot-circle' : preg_replace('#\.[^ .]*$#', '', $displayData['icon']);
?>
<h1 class="page-title">
    <?php echo LayoutHelper::render('joomla.icon.iconclass', ['icon' => $icon]); ?>
    <?php echo $displayData['title']; ?>
</h1>
