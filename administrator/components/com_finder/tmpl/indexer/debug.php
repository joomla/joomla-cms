<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

/** @var Joomla\Component\Finder\Administrator\View\Indexer\HtmlView $this */

Text::script('COM_FINDER_INDEXER_MESSAGE_COMPLETE', true);

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
    ->useScript('com_finder.debug');

?>

<form action="<?php echo Route::_('index.php?option=com_finder&layout=debug'); ?>" method="post" name="adminForm" id="debug-form">
    <div class="form-horizontal">
        <div class="card mt-3">
            <div class="card-body">
                <fieldset class="adminform p-4">
                    <div class="alert alert-info">
                        <h2 class="alert-heading"><?php echo Text::_('COM_FINDER_INDEXER_MSG_DEBUGGING_INDEXING'); ?></h2>
                        <?php echo Text::_('COM_FINDER_INDEXER_MSG_DEBUGGING_INDEXING_TEXT'); ?>
                    </div>
                    <?php echo $this->form->renderField('plugin'); ?>
                    <?php echo $this->form->renderField('id'); ?>

                    <input id="finder-indexer-token" type="hidden" name="<?php echo Factory::getSession()->getFormToken(); ?>" value="1">
                </fieldset>
            </div>
        </div>
    </div>
</form>

<div class="form-horizontal">
    <div class="card mt-3">
        <div class="card-body">
            <fieldset class="adminform">
                <legend><?php echo Text::_('COM_FINDER_INDEXER_OUTPUT_AREA_TITLE'); ?></legend>
                <div id="indexer-output" class="border p-3" style="min-height:200px;">

                </div>
            </fieldset>
        </div>
    </div>
</div>



