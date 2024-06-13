<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2015 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

/** @var \Joomla\Component\Redirect\Administrator\View\Links\HtmlView $this */

$published = $this->state->get('filter.published');
$params    = $this->params;
$separator = $params->get('separator', '|');
?>

<div class="container">
    <div class="row">
        <div class="control-group col-md-12 mt-3">
            <label for="batch_urls" class="form-label"><?php echo Text::sprintf('COM_REDIRECT_BATCH_TIP', $separator); ?></label>
            <div class="controls">
                <textarea class="form-control" rows="10" id="batch_urls" name="batch_urls"></textarea>
            </div>
        </div>
        <div class="btn-toolbar pb-3">
            <joomla-toolbar-button task="links.batch" class="ms-auto">
                <button type="button" class="btn btn-success"><?php echo Text::_('JGLOBAL_BATCH_PROCESS'); ?></button>
            </joomla-toolbar-button>
        </div>
    </div>
</div>
