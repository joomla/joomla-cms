<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$id     = $displayData['id'];
$margin = (strpos($id, 'toolbar-options') === false) ? '' : ' ml-auto';
?>
<div class="btn-wrapper<?php echo $margin; ?>" <?php echo $id; ?>>
	<?php echo $displayData['action']; ?>
</div>
