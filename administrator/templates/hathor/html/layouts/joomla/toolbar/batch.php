<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = $displayData['title'];

?>
<a data-toggle="modal" data-target="#collapseModal" class="btn btn-small">
	<span class="icon-32-batch" title="<?php echo $title; ?>"></span>
	<?php echo $title; ?>
</a>
