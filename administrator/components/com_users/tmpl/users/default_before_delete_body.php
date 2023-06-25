<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;

/** @var \Joomla\CMS\Form\Form $form */
$form = Factory::getContainer()
    ->get(FormFactoryInterface::class)
    ->createForm('beforeDeleteUser', ['control' => 'beforeDeleteUser']);

$form->loadFile('before_delete');

?>

<div class="p-3">
    <?php echo $form->renderFieldset('before_delete_user'); ?>
</div>
