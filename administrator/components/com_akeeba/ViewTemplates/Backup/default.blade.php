<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var  $this  \Akeeba\Backup\Admin\View\Backup\Html */

?>
{{-- Configuration Wizard pop-up --}}
@if($this->promptForConfigurationWizard)
	@include('admin:com_akeeba/Configuration/confwiz_modal')
@endif

{{-- The Javascript of the page --}}
@include('admin:com_akeeba/Backup/script')

{{-- Obsolete PHP version warning --}}
@include('admin:com_akeeba/CommonTemplates/phpversion_warning', [
    'softwareName'  => 'Akeeba Backup',
    'minPHPVersion' => '7.1.0',
])

{{-- Backup Setup --}}
<div id="backup-setup" class="akeeba-panel--primary">
    <header class="akeeba-block-header">
        <h3>
            @lang('COM_AKEEBA_BACKUP_HEADER_STARTNEW')
        </h3>
    </header>

	@if($this->hasWarnings && !$this->unwriteableOutput)
	<div id="quirks" class="akeeba-block--{{ $this->hasErrors ? 'failure' : 'warning' }}">
		<h3 class="alert-heading">
			@lang('COM_AKEEBA_BACKUP_LABEL_DETECTEDQUIRKS')
		</h3>
		<p>
			@lang('COM_AKEEBA_BACKUP_LABEL_QUIRKSLIST')
		</p>
		{{ $this->warningsCell }}

	</div>
	@endif

	@if($this->unwriteableOutput)
	<div id="akeeba-fatal-outputdirectory" class="akeeba-block--failure">
		<h3>
			@lang('COM_AKEEBA_BACKUP_ERROR_UNWRITABLEOUTPUT_' . ($this->autoStart ? 'AUTOBACKUP' : 'NORMALBACKUP'))
		</h3>
		<p>
			@sprintf('COM_AKEEBA_BACKUP_ERROR_UNWRITABLEOUTPUT_COMMON', 'index.php?option=com_akeeba&view=Configuration', 'https://www.akeeba.com/warnings/q001.html')
		</p>
	</div>
	@endif

	<form action="index.php" method="post" name="flipForm" id="flipForm"
		  class="akeeba-formstyle-reset akeeba-form--inline akeeba-panel--information"
		  autocomplete="off">

        <div class="akeeba-form-group">
            <label>
		        @lang('COM_AKEEBA_CPANEL_PROFILE_TITLE'): #{{ $this->profileId }}

            </label>
	        @jhtml('select.genericlist', $this->profileList, 'profileid', ['list.select' => $this->profileId, 'id' => 'comAkeebaBackupProfileDropdown', 'list.attr' => ['class' => 'advancedSelect']])
        </div>

        <div class="akeeba-form-group--actions">
            <button class="akeeba-btn--grey" id="comAkeebaBackupFlipProfile">
                <span class="akion-refresh"></span>
		        @lang('COM_AKEEBA_CPANEL_PROFILE_BUTTON')
            </button>
        </div>

        <div class="akeeba-hidden-fields-container">
            <input type="hidden" name="option" value="com_akeeba"/>
            <input type="hidden" name="view" value="Backup"/>
            <input type="hidden" name="returnurl" value="{{{ $this->returnURL }}}"/>
            <input type="hidden" name="description" id="flipDescription" value=""/>
            <input type="hidden" name="comment" id="flipComment" value=""/>
            <input type="hidden" name="@token(true)" value="1"/>
        </div>
	</form>

	<form id="dummyForm" class="akeeba-form--horizontal" style="display: {{ $this->unwriteableOutput ? 'none' : 'block' }};">
		<div class="akeeba-form-group">
			<label for="backup-description">
				@lang('COM_AKEEBA_BACKUP_LABEL_DESCRIPTION')
			</label>
            <input type="text" name="description" value="{{{ $this->description }}}"
                   maxlength="255" size="80" id="backup-description" class="input-xxlarge" autocomplete="off" />
            <span class="akeeba-help-text">@lang('COM_AKEEBA_BACKUP_LABEL_DESCRIPTION_HELP')</span>
		</div>

		@if($this->showJPSPassword)
		<div class="akeeba-form-group">
			<label for="jpskey">
				@lang('COM_AKEEBA_CONFIG_JPS_KEY_TITLE')
			</label>
            <input type="password" name="jpskey" value="{{{ $this->jpsPassword }}}" size="50" id="jpskey" autocomplete="off" />
            <span class="akeeba-help-text">@lang('COM_AKEEBA_CONFIG_JPS_KEY_DESCRIPTION')</span>
		</div>
		@endif

		@if($this->showANGIEPassword)
		<div class="akeeba-form-group">
			<label for="angiekey">
				@lang('COM_AKEEBA_CONFIG_ANGIE_KEY_TITLE')
			</label>
            <input type="password" name="angiekey" value="{{{ $this->ANGIEPassword }}}"  size="50" id="angiekey" autocomplete="off" />
            <span class="akeeba-help-text">@lang('COM_AKEEBA_CONFIG_ANGIE_KEY_DESCRIPTION')</span>
		</div>
		@endif

		<div class="akeeba-form-group">
			<label for="comment">
				@lang('COM_AKEEBA_BACKUP_LABEL_COMMENT')
			</label>
            <textarea id="comment" rows="5" cols="73" class="input-xxlarge">{{ $this->comment }}</textarea>
            <span class="akeeba-help-text">@lang('COM_AKEEBA_BACKUP_LABEL_COMMENT_HELP')</span>
		</div>

        <div class="akeeba-form-group--pull-right">
            <div class="akeeba-form-group--actions">
                <button class="akeeba-btn--primary" id="backup-start">
                    <span class="akion-play"></span>
			        @lang('COM_AKEEBA_BACKUP_LABEL_START')
                </button>

                <a class="akeeba-btn--orange" id="backup-default" href="#">
                    <span class="akion-refresh"></span>
			        @lang('COM_AKEEBA_BACKUP_LABEL_RESTORE_DEFAULT')
                </a>
            </div>
        </div>
	</form>
</div>

{{-- Warning for having set an ANGIE password --}}
<div id="angie-password-warning" class="akeeba-block--warning" style="display: none">
    <h3>@lang('COM_AKEEBA_BACKUP_ANGIE_PASSWORD_WARNING_HEADER')</h3>
    <p>@lang('COM_AKEEBA_BACKUP_ANGIE_PASSWORD_WARNING_1')</p>
    <p>@lang('COM_AKEEBA_BACKUP_ANGIE_PASSWORD_WARNING_2')</p>
    <p>@lang('COM_AKEEBA_BACKUP_ANGIE_PASSWORD_WARNING_3')</p>
</div>

{{-- Backup in progress --}}
<div id="backup-progress-pane" style="display: none">
	<div class="akeeba-block--info">
		@lang('COM_AKEEBA_BACKUP_TEXT_BACKINGUP')
	</div>

    <div class="akeeba-panel--primary">
        <header class="akeeba-block-header">
            <h3>
                @lang('COM_AKEEBA_BACKUP_LABEL_PROGRESS')
            </h3>
        </header>

        <div id="backup-progress-content">
            <div id="backup-steps"></div>
            <div id="backup-status" class="backup-steps-container">
                <div id="backup-step"></div>
                <div id="backup-substep"></div>
            </div>
            <div id="backup-percentage" class="akeeba-progress">
                <div class="akeeba-progress-fill" style="width: 0"></div>
            </div>
            <div id="response-timer">
                <div class="color-overlay"></div>
                <div class="text"></div>
            </div>
        </div>
        <span id="ajax-worker"></span>
    </div>

    @if (!AKEEBA_PRO)
    <div>
        <p>
            <em>@lang('COM_AKEEBA_BACKUP_LBL_UPGRADENAG')</em>
        </p>
    </div>
    @endif
</div>

{{-- Backup complete --}}
<div id="backup-complete" style="display: none">
    <div class="akeeba-panel--success">
        <header class="akeeba-block-header">
            <h3>
				@if(empty($this->returnURL))
					@lang('COM_AKEEBA_BACKUP_HEADER_BACKUPFINISHED')
				@else
					@lang('COM_AKEEBA_BACKUP_HEADER_BACKUPWITHRETURNURLFINISHED')
				@endif
            </h3>
        </header>

        <div id="finishedframe">
            <p>
				@if(empty($this->returnURL))
					@lang('COM_AKEEBA_BACKUP_TEXT_CONGRATS')
				@else
					@lang('COM_AKEEBA_BACKUP_TEXT_PLEASEWAITFORREDIRECTION')
				@endif
            </p>

			@if(empty($this->returnURL))
                <a class="akeeba-btn--primary--big" href="index.php?option=com_akeeba&view=Manage">
                    <span class="akion-ios-list"></span>
					@lang('COM_AKEEBA_BUADMIN')
                </a>
                <a class="akeeba-btn--grey" id="ab-viewlog-success" href="index.php?option=com_akeeba&view=Log&latest=1">
                    <span class="akion-ios-search-strong"></span>
					@lang('COM_AKEEBA_LOG')
                </a>
			@endif
        </div>
    </div>
</div>

{{-- Backup warnings --}}
<div id="backup-warnings-panel" style="display:none">
    <div class="akeeba-panel--warning">
        <header class="akeeba-block-header">
            <h3>
				@lang('COM_AKEEBA_BACKUP_LABEL_WARNINGS')
            </h3>
        </header>
        <div id="warnings-list">
        </div>
    </div>
</div>

{{-- Backup retry after error --}}
<div id="retry-panel" style="display: none">
	<div class="akeeba-panel--warning">
        <header class="akeeba-block-header">
            <h3>
		        @lang('COM_AKEEBA_BACKUP_HEADER_BACKUPRETRY')
            </h3>
        </header>
		<div id="retryframe">
			<p>@lang('COM_AKEEBA_BACKUP_TEXT_BACKUPFAILEDRETRY')</p>
			<p>
				<strong>
					@lang('COM_AKEEBA_BACKUP_TEXT_WILLRETRY')
					<span id="akeeba-retry-timeout">0</span>
					@lang('COM_AKEEBA_BACKUP_TEXT_WILLRETRYSECONDS')
				</strong>
				<br/>
				<button class="akeeba-btn--red--small" id="comAkeebaBackupCancelResume">
					<span class="akion-android-cancel"></span>
					@lang('COM_AKEEBA_MULTIDB_GUI_LBL_CANCEL')
				</button>
				<button class="akeeba-btn--green--small" id="comAkeebaBackupResumeBackup">
					<span class="akion-ios-redo"></span>
					@lang('COM_AKEEBA_BACKUP_TEXT_BTNRESUME')
				</button>
			</p>

			<p>@lang('COM_AKEEBA_BACKUP_TEXT_LASTERRORMESSAGEWAS')</p>
			<p id="backup-error-message-retry"></p>
		</div>
	</div>
</div>

{{-- Backup error (halt) --}}
<div id="error-panel" style="display: none">
	<div class="akeeba-panel--red">
        <header class="akeeba-block-header">
            <h3>
		        @lang('COM_AKEEBA_BACKUP_HEADER_BACKUPFAILED')
            </h3>
        </header>

		<div id="errorframe">
			<p>
				@lang('COM_AKEEBA_BACKUP_TEXT_BACKUPFAILED')
			</p>
			<p id="backup-error-message"></p>

			<p>
				@lang('COM_AKEEBA_BACKUP_TEXT_READLOGFAIL' . (AKEEBA_PRO ? 'PRO' : ''))
			</p>

			<div class="akeeba-block--info" id="error-panel-troubleshooting">
				<p>
					@if(AKEEBA_PRO)
					@lang('COM_AKEEBA_BACKUP_TEXT_RTFMTOSOLVEPRO')
					@endif

					@sprintf('COM_AKEEBA_BACKUP_TEXT_RTFMTOSOLVE', 'https://www.akeeba.com/documentation/akeeba-backup-documentation/troubleshoot-backup.html?utm_source=akeeba_backup&utm_campaign=backuperrorlink')
				</p>
				<p>
					@if(AKEEBA_PRO)
					@sprintf('COM_AKEEBA_BACKUP_TEXT_SOLVEISSUE_PRO', 'https://www.akeeba.com/support.html?utm_source=akeeba_backup&utm_campaign=backuperrorpro')
					@else
					@sprintf('COM_AKEEBA_BACKUP_TEXT_SOLVEISSUE_CORE', 'https://www.akeeba.com/subscribe.html?utm_source=akeeba_backup&utm_campaign=backuperrorcore','https://www.akeeba.com/support.html?utm_source=akeeba_backup&utm_campaign=backuperrorcore')
					@endif

					@sprintf('COM_AKEEBA_BACKUP_TEXT_SOLVEISSUE_LOG', 'index.php?option=com_akeeba&view=Log&latest=1')
				</p>
			</div>

			@if(AKEEBA_PRO)
			<a class="akeeba-btn--green" id="ab-alice-error" href="index.php?option=com_akeeba&view=Alice">
				<span class="akion-medkit"></span>
				@lang('COM_AKEEBA_BACKUP_ANALYSELOG')
			</a>
			@endif

			<a class="akeeba-btn--primary" href="https://www.akeeba.com/documentation/akeeba-backup-documentation/troubleshoot-backup.html?utm_source=akeeba_backup&utm_campaign=backuperrorbutton">
				<span class="akion-ios-book"></span>
				@lang('COM_AKEEBA_BACKUP_TROUBLESHOOTINGDOCS')
			</a>

            <a class="akeeba-btn-grey" id="ab-viewlog-error" href="index.php?option=com_akeeba&view=Log&latest=1">
				<span class="akion-ios-search-strong"></span>
				@lang('COM_AKEEBA_LOG')
			</a>
		</div>
	</div>
</div>
