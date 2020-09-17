<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

// Only show in the Core version with a 10% probability
if (AKEEBA_PRO) return;

// Only show if it's at least 15 days since the last time the user dismissed the upsell
if (time() - $this->lastUpsellDismiss < 1296000) return;

?>
<div class="akeeba-panel--orange">
    <header class="akeeba-block-header">
        <h3>
            <span class="akion-ios-star"></span>
            @lang('COM_AKEEBA_CONTROLPANEL_HEAD_PROUPSELL')
        </h3>
    </header>

    <p>@lang('COM_AKEEBA_CONTROLPANEL_HEAD_LBL_PROUPSELL_1')</p>

    <p class="akeeba-block--info">@sprintf('COM_AKEEBA_CONTROLPANEL_HEAD_LBL_DISCOUNT',
        base64_decode('SVdBTlRJVEFMTA=='))</p>

    <p>@lang('COM_AKEEBA_CONTROLPANEL_HEAD_LBL_PROUPSELL_2')</p>

    <p>
        <a href="https://www.akeeba.com/landing/akeeba-backup.html"
           class="akeeba-btn--large--primary">
            <span class="aklogo-backup-j"></span>
            @lang('COM_AKEEBA_CONTROLPANEL_BTN_LEARNMORE')
        </a>

        <a href="@route('index.php?view=ControlPanel&task=dismissUpsell')" class="akeeba-btn--ghost--small">
            <span class="akion-ios-alarm"></span>
            @lang('COM_AKEEBA_CONTROLPANEL_BTN_HIDE')
        </a>
    </p>
</div>
