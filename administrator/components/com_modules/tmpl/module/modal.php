<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->addInlineScript('
    document.addEventListener("DOMContentLoaded", function() {
        const saveCloseButton = window.parent.document.getElementById("btnModalSaveAndClose");
        if (saveCloseButton) {
          saveCloseButton.classList.remove("hidden");
        }
    });
');

?>
<button id="applyBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.apply');"></button>
<button id="saveBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.save');"></button>
<button id="closeBtn" type="button" class="hidden" onclick="Joomla.submitbutton('module.cancel');"></button>

<div class="container-popup">
    <?php $this->setLayout('edit'); ?>
    <?php echo $this->loadTemplate(); ?>
</div>
