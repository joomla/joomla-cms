<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var  \Akeeba\Backup\Admin\View\Configuration\Html $this */
?>
{{-- Configuration Wizard pop-up --}}
@if($this->promptForConfigurationWizard)
	@include('admin:com_akeeba/Configuration/confwiz_modal')
@endif

{{-- Modal dialog prototypes --}}
@include('admin:com_akeeba/CommonTemplates/FTPBrowser')
@include('admin:com_akeeba/CommonTemplates/SFTPBrowser')
@include('admin:com_akeeba/CommonTemplates/FTPConnectionTest')
@include('admin:com_akeeba/CommonTemplates/ErrorModal')
@include('admin:com_akeeba/CommonTemplates/FolderBrowser')

@if($this->secureSettings == 1)
    <div class="akeeba-block--success">
		@lang('COM_AKEEBA_CONFIG_UI_SETTINGS_SECURED')
    </div>
@elseif($this->secureSettings == 0)
    <div class="akeeba-block--failure">
		@lang('COM_AKEEBA_CONFIG_UI_SETTINGS_NOTSECURED')
    </div>
@endif

@include('admin:com_akeeba/CommonTemplates/ProfileName')

<div class="akeeba-block--info">
	@lang('COM_AKEEBA_CONFIG_WHERE_ARE_THE_FILTERS')
</div>

<form name="adminForm" id="adminForm" method="post" action="index.php"
      class="akeeba-form--horizontal akeeba-form--with-hidden akeeba-form--configuration">

    <div class="akeeba-panel--info" style="margin-bottom: -1em">
        <header class="akeeba-block-header">
            <h5>
                @lang('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION')
            </h5>
        </header>

        <div class="akeeba-form-group">
            <label for="profilename" rel="popover"
                   data-original-title="@lang('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION')"
                   data-content="@lang('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION_TOOLTIP')">
				@lang('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION')
            </label>
            <input type="text" name="profilename" id="profilename"
                   value="{{{ $this->profileName }}}"/>
        </div>

        <div class="akeeba-form-group">
            <label class="control-label" for="quickicon" rel="popover"
                   data-original-title="@lang('COM_AKEEBA_CONFIG_QUICKICON_LABEL')"
                   data-content="@lang('COM_AKEEBA_CONFIG_QUICKICON_DESC')">
				@lang('COM_AKEEBA_CONFIG_QUICKICON_LABEL')
            </label>
            <div>
                <input type="checkbox" name="quickicon"
                       id="quickicon" {{ $this->quickIcon ? 'checked="checked"' : '' }}/>
            </div>
        </div>
    </div>

    <!-- This div contains dynamically generated user interface elements -->
    <div id="akeebagui">
    </div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba"/>
        <input type="hidden" name="view" value="Configuration"/>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="@token(true)" value="1"/>
    </div>
</form>
