<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Editors.tinymce
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   \Joomla\CMS\Form\Form          $form  Form with extra options for the set
 * @var   \Joomla\CMS\Layout\FileLayout  $this  Context
 */
?>
<div class="setoptions-form-wrapper">
<?php foreach ($form->getFieldset('basic') as $field) : ?>
    <?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
