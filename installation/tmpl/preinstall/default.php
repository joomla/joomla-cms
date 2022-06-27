<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

HTMLHelper::_('behavior.formvalidator');

/** @var \Joomla\CMS\Installation\View\Preinstall\HtmlView $this */
?>
<div id="installer-view" class="container" data-page-name="preinstall">
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="j-install-step active">
                <div class="j-install-step-header">
                    <span class="icon-check" aria-hidden="true"></span> <?php echo Text::_('INSTL_PRECHECK_TITLE'); ?>
                </div>
                <div class="j-install-step-form">
                    <?php foreach ($this->options as $option) : ?>
                        <?php if ($option->state === 'JNO' || $option->state === false) : ?>
                            <div class="alert preinstall-alert">
                                <div class="alert-icon">
                                    <span class="alert-icon icon-exclamation-triangle" aria-hidden="true"></span>
                                </div>
                                <div class="alert-text">
                                    <strong><?php echo $option->label; ?></strong>
                                    <p class="form-text small"><?php echo $option->notice; ?></p>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
