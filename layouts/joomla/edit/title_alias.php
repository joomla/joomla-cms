<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$form  = $displayData->getForm();

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

?>
<div class="row title-alias form-vertical mb-3">
	<div class="col-12 col-md-6">
		<?php echo $title ? $form->renderField($title) : ''; ?>
	</div>
	<div class="col-12 col-md-6">
		<?php echo $form->renderField('alias'); ?>
	</div>
</div>
