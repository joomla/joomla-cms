<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Content\Administrator\View\Article\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_content.admin-article-pagebreak');

$this->eName = Factory::getApplication()->getInput()->getCmd('e_name', '');
$this->eName = preg_replace('#[^A-Z0-9\-\_\[\]]#i', '', $this->eName);
$this->getDocument()->setTitle(Text::_('COM_CONTENT_PAGEBREAK_DOC_TITLE'));

?>
<div class="container-popup">
    <form>
        <div class="control-group">
            <div class="control-label">
                <label for="title"><?php echo Text::_('COM_CONTENT_PAGEBREAK_TITLE'); ?></label>
            </div>
            <div class="controls">
                <input class="form-control" type="text" id="title" name="title">
            </div>
        </div>
        <div class="control-group">
            <div class="control-label">
                <label for="alias"><?php echo Text::_('COM_CONTENT_PAGEBREAK_TOC'); ?></label>
            </div>
            <div class="controls">
                <input class="form-control" type="text" id="alt" name="alt">
            </div>
        </div>

        <button onclick="insertPagebreak('<?php echo $this->eName; ?>');" class="btn btn-success">
            <?php echo Text::_('COM_CONTENT_PAGEBREAK_INSERT_BUTTON'); ?>
        </button>

    </form>
</div>
