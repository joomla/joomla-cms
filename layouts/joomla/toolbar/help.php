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

HTMLHelper::_('behavior.core');
HTMLHelper::_('webcomponent', ['joomla-toolbar-button' => 'system/webcomponents/joomla-toolbar-button.min.js'], ['relative' => true, 'version' => 'auto', 'detectBrowser' => false, 'detectDebug' => true]);

$id     = $displayData['id'] ?? '';
$doTask = $displayData['doTask'];
$text   = $displayData['text'];
?>
<joomla-toolbar-button <?php echo $id; ?> execute="<?php echo $doTask; ?>" rel="help" class="btn btn-outline-info btn-sm">
	<span class="fa fa-question" aria-hidden="true"></span>
	<?php echo $text; ?>
</joomla-toolbar-button>
