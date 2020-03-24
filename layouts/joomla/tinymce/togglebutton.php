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

$name = $displayData;

?>
<div class="toggle-editor btn-toolbar float-right clearfix mt-3">
	<div class="btn-group">
		<button type="button" class="btn btn-secondary" href="#"
			onclick="tinyMCE.execCommand('mceToggleEditor', false, '<?php echo $name; ?>');return false;"
		>
			<span class="fas fa-eye" aria-hidden="true"></span>
			<?php echo Text::_('PLG_TINY_BUTTON_TOGGLE_EDITOR'); ?>
		</button>
	</div>
</div>
