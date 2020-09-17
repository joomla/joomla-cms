<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

/** @var  \Akeeba\Backup\Admin\View\Log\Html  $this */

?>
@if(isset($this->logs) && count($this->logs))
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--inline">
    <div class="akeeba-form-group">
        <label for="tag">@lang('COM_AKEEBA_LOG_CHOOSE_FILE_TITLE')</label>

        {{-- Joomla 3.x: Chosen does not work with attached event handlers, only with inline event scripts (e.g. onchange) --}}
        @if (version_compare(JVERSION, '3.999.999', 'lt'))
            @jhtml('select.genericlist', $this->logs, 'tag', ['list.select' => $this->tag, 'list.attr' => ['class' => 'advancedSelect', 'onchange' => 'document.forms.adminForm.submit();'], 'id' => 'comAkeebaLogTagSelector'])
        @else
            @jhtml('select.genericlist', $this->logs, 'tag', ['list.select' => $this->tag, 'list.attr' => ['class' => 'advancedSelect'], 'id' => 'comAkeebaLogTagSelector'])
        @endif
    </div>

	@if(!empty($this->tag))
        <div class="akeeba-form-group--actions">
            <a class="akeeba-btn--primary" href="{{{ JUri::base() }}}index.php?option=com_akeeba&view=Log&task=download&tag={{{ $this->tag }}}">
                <span class="akion-ios-download"></span>
		        @lang('COM_AKEEBA_LOG_LABEL_DOWNLOAD')
            </a>
        </div>
	@endif

    <div class="akeeba-hidden-fields-container">
        <input name="option" value="com_akeeba" type="hidden" />
        <input name="view" value="Log" type="hidden" />
        <input type="hidden" name="@token(true)" value="1" />
    </div>

</form>
@endif

@if(!empty($this->tag))
    @if ($this->logTooBig)
        <div class="akeeba-block--warning">
            <p>
                @sprintf('COM_AKEEBA_LOG_SIZE_WARNING', number_format($this->logSize / (1024 * 1024), 2))
            </p>
            <a class="akeeba-btn--dark" id="showlog" href="#">
                @lang('COM_AKEEBA_LOG_SHOW_LOG')
            </a>
        </div>
    @endif

    <div id="iframe-holder" class="akeeba-panel--primary" style="display: {{ $this->logTooBig ? 'none' : 'block' }};">
		@if(!$this->logTooBig)
            <iframe
                src="index.php?option=com_akeeba&view=Log&task=iframe&format=raw&tag={{ urlencode($this->tag) }}"
                width="99%" height="400px">
            </iframe>
		@endif
    </div>
@endif

@if( ! (isset($this->logs) && count($this->logs)))
<div class="akeeba-block--failure">
	@lang('COM_AKEEBA_LOG_NONE_FOUND')
</div>
@endif
