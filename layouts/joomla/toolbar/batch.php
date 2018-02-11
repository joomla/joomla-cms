<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', ['joomla-toolbar-button' => 'system/webcomponents/joomla-toolbar-button.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);

$id    = isset($displayData['id']) ? $displayData['id'] : '';
$title = $displayData['title'];

?>
<joomla-toolbar-button <?php echo $id; ?> execute="jQuery( '#collapseModal' ).modal('show');" list-selection class="btn btn-outline-primary btn-sm">
	<span class="fa fa-square" aria-hidden="true" title="<?php echo $title; ?>"></span>
	<?php echo $title; ?>
</joomla-toolbar-button>
