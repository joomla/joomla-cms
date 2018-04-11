<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   JForm        $form    Form with extra options for the set
 * @var   JLayoutFile  $this    Context
 */

?>
<div class="setoptions-form-wrapper">
<?php foreach ($form->getGroup(null) as $field) : ?>
	<?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
