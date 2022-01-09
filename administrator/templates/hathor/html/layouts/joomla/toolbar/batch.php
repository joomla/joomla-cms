<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Template.hathor
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$title = $displayData['title'];

?>
<button type="button" data-toggle="modal" data-target="#collapseModal" class="btn btn-small">
	<span class="icon-32-batch" title="<?php echo $title; ?>"></span>
	<?php echo $title; ?>
</button>
