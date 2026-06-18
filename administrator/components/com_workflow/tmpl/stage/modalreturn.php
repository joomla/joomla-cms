<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_workflow
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$item = $this->get('Item');

$icon     = 'icon-check';
$title    = $item ? $item->title : '';
$content  = '';
$data     = ['contentType' => 'com_workflow.stage'];

if ($item) {
    $data['id']        = $item->id;
    $data['title']     = $item->title;
    $data['workflow']  = $item->workflow_id ?? '';
}

// Add Content select script
/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('modal-content-select');

// The data for Content select script
$this->getDocument()->addScriptOptions('content-select-on-load', $data, false);
?>

<div class="px-4 py-5 my-5 text-center">
    <span class="fa-8x mb-4 <?php echo $icon; ?>" aria-hidden="true"></span>
    <h1 class="display-5 fw-bold"><?php echo $title; ?></h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            <?php echo $content; ?>
        </p>
    </div>
</div>
