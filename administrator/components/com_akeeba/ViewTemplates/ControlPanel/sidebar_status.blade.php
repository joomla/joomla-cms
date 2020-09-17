<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<div class="akeeba-panel">
    <header class="akeeba-block-header">
        <h3>@lang('COM_AKEEBA_CPANEL_LABEL_STATUSSUMMARY')</h3>
    </header>

    <div>
        {{-- Backup status summary --}}
        {{ $this->statusCell }}

        {{-- Warnings --}}
        @if($this->countWarnings)
            <div>
                {{ $this->detailsCell }}
            </div>
            <hr />
        @endif

        {{-- Version --}}
        <p class="ak_version">
            @lang('COM_AKEEBA') {{ AKEEBA_PRO ? 'Professional ' : 'Core'; }} {{ AKEEBA_VERSION }} ({{ AKEEBA_DATE }})
        </p>

        {{-- Changelog --}}
        <a href="#" id="btnchangelog" class="akeeba-btn--primary">CHANGELOG</a>

        <div id="akeeba-changelog" tabindex="-1" role="dialog" aria-hidden="true" style="display:none;">
            <div class="akeeba-renderer-fef">
                <div class="akeeba-panel--info">
                    <header class="akeeba-block-header">
                        <h3>
                            @lang('CHANGELOG')
                        </h3>
                    </header>
                    <div id="DialogBody">
                        {{ $this->formattedChangelog }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Donation CTA --}}
        @if( ! (AKEEBA_PRO))
            <a
                    href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=KDVQPB4EREBPY&source=url"
                    class="akeeba-btn-green">
                Donate via PayPal
            </a>
        @endif

        {{-- Reload update information --}}
        @if (!AKEEBA_PRO)
            <p style="margin: 0.5em 0">
                <a href="index.php?option=com_akeeba&view=ControlPanel&task=reloadUpdateInformation"
                   class="akeeba-btn--dark">
                    @lang('COM_AKEEBA_CPANEL_MSG_RELOADUPDATE')
                </a>
            </p>
        @else
            <a href="index.php?option=com_akeeba&view=ControlPanel&task=reloadUpdateInformation"
               class="akeeba-btn--dark">
                @lang('COM_AKEEBA_CPANEL_MSG_RELOADUPDATE')
            </a>
        @endif

        {{-- Pro upsell --}}
        @if(!AKEEBA_PRO && (time() - $this->lastUpsellDismiss < 1296000))
            <p style="margin: 0.5em 0">
                <a href="https://www.akeeba.com/landing/akeeba-backup.html"
                   class="akeeba-btn--ghost--small">
                    <span class="aklogo-backup-j"></span>
                    @lang('COM_AKEEBA_CONTROLPANEL_BTN_LEARNMORE')
                </a>
            </p>
        @endif
    </div>
</div>
