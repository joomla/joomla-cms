<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$category      = $displayData['category'];
$tooltip       = $displayData['tooltip'];
$ariaDescribed = 'editcategory-' . (int) $category->id;
$icon          = (int) $category->published === 1 ? 'edit' : 'eye-slash';
?>
<span class="icon-<?php echo $icon; ?>" aria-hidden="true"></span>
    <?php echo Text::_('JGLOBAL_EDIT'); ?>
<div role="tooltip" id="<?php echo $ariaDescribed; ?>">
    <?php echo $tooltip; ?>
</div>
