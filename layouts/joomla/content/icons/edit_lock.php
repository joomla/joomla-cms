<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

$tooltip = $displayData['tooltip'];

?>
<span class="hasTooltip fas fa-lock" title="<?php echo HTMLHelper::tooltipText($tooltip . '', 0); ?>"></span>
<?php echo Text::_('JLIB_HTML_CHECKED_OUT'); ?>
