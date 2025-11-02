<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2023 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Plugins\Administrator\View\Plugin\HtmlView $this */

$icon     = 'icon-check';
$title    = $this->item ? Text::_($this->item->name) : '';
$content  = $this->item ? $this->item->folder . '/' . $this->item->element : '';
$data     = ['contentType' => 'com_plugins.plugin'];

if ($this->item && $this->item->extension_id) {
    $data['id']    = $this->item->extension_id;
    $data['title'] = $title;
    $data['type']  = $this->item->folder;
    $data['name']  = $this->item->element;
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
