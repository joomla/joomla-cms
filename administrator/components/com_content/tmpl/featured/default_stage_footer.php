<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->document->getWebAssetManager();
$wa->useScript('com_content.admin-articles-stage');

?>
<button class="btn btn-secondary" type="button" data-bs-dismiss="modal">
    <?php echo Text::_('JCANCEL'); ?>
</button>
<button id="stage-submit-button-id" class="btn btn-success" type="button" data-submit-task="">
    <?php echo Text::_('JGLOBAL_STAGE_PROCESS'); ?>
</button>
