<?php
/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

$form  = $displayData->getForm();

$title = $form->getField('title') ? 'title' : ($form->getField('name') ? 'name' : '');

?>
<section  id="title-alias" class="title-alias" aria-labelledby="title-alias">
	<fieldset class="form-vertical form-no-margin">
		<div class="row">
			<div class="col-lg-7 edit-title">
				<?php echo $title ? $form->renderField($title) : ''; ?>
			</div>
			<div class="col-lg-5 edit-alias">
				<?php echo $form->renderField('alias'); ?>
			</div>
		</div>
	</fieldset>
</section>