<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$article = $displayData['article'];
$tooltip = $displayData['tooltip'];

$aria_described = 'editarticle-' . (int) $article->id;
?>
<span class="hasTooltip fas fa-lock"  aria-hidden="false"></span>
	<?php echo Text::_('JLIB_HTML_CHECKED_OUT'); ?>
<div role="tooltip" id="<?php echo $aria_described; ?>" aria-live="polite">
	<?php echo $tooltip; ?>
</div>
