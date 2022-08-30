<?php

/**
 * @package     Joomla.Site
 * @subpackage  Layout
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   Form    $form       The form instance for render the section
 * @var   string  $basegroup  The base group name
 * @var   string  $group      Current group name
 * @var   array   $buttons    Array of the buttons that will be rendered
 */
?>

<div class="subform-repeatable-group" data-base-name="<?php echo $basegroup; ?>" data-group="<?php echo $group; ?>">
    <?php if (!empty($buttons)) : ?>
    <div class="btn-toolbar text-end">
        <div class="btn-group">
            <?php if (!empty($buttons['add'])) :
                ?><button type="button" class="group-add btn btn-sm btn-success" aria-label="<?php echo Text::_('JGLOBAL_FIELD_ADD'); ?>"><span class="icon-plus icon-white" aria-hidden="true"></span> </button><?php
            endif; ?>
            <?php if (!empty($buttons['remove'])) :
                ?><button type="button" class="group-remove btn btn-sm btn-danger" aria-label="<?php echo Text::_('JGLOBAL_FIELD_REMOVE'); ?>"><span class="icon-minus icon-white" aria-hidden="true"></span> </button><?php
            endif; ?>
            <?php if (!empty($buttons['move'])) :
                ?><button type="button" class="group-move btn btn-sm btn-primary" aria-label="<?php echo Text::_('JGLOBAL_FIELD_MOVE'); ?>"><span class="icon-arrows-alt icon-white" aria-hidden="true"></span> </button><?php
            endif; ?>
        </div>
    </div>
    <?php endif; ?>

<?php foreach ($form->getGroup('') as $field) : ?>
    <?php echo $field->renderField(); ?>
<?php endforeach; ?>
</div>
