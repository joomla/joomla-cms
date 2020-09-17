<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

/**
 * Call this template with:
 * [
 * 	'returnURL' => 'index.php?......'
 * ]
 * to set up a custom return URL
 */
?>
@if (version_compare(JVERSION, '3.999.999', 'lt'))
	@jhtml('formbehavior.chosen')
@endif

<div class="akeeba-panel">
	<form action="index.php" method="post" name="switchActiveProfileForm" id="switchActiveProfileForm" class="akeeba-form--inline">
		<input type="hidden" name="option" value="com_akeeba" />
		<input type="hidden" name="view" value="ControlPanel" />
		<input type="hidden" name="task" value="SwitchProfile" />
		@if(isset($returnURL))
		<input type="hidden" name="returnurl" value="{{ $returnURL }}" />
		@endif
		<input type="hidden" name="@token(true)" value="1" />

		<div class="akeeba-form-group">
			<label>
				@lang('COM_AKEEBA_CPANEL_PROFILE_TITLE'): #{{ $this->profileId }}
			</label>

			{{-- Joomla 3.x: Chosen does not work with attached event handlers, only with inline event scripts (e.g. onchange) --}}
			@if (version_compare(JVERSION, '3.999.999', 'lt'))
				@jhtml('select.genericlist', $this->profileList, 'profileid', ['list.select' => $this->profileId, 'id' => 'comAkeebaControlPanelProfileSwitch', 'list.attr' => ['class' => 'advancedSelect', 'onchange' => 'document.forms.switchActiveProfileForm.submit();']])
			@else
				@jhtml('select.genericlist', $this->profileList, 'profileid', ['list.select' => $this->profileId, 'id' => 'comAkeebaControlPanelProfileSwitch', 'list.attr' => ['class' => 'advancedSelect']])
			@endif
		</div>

		<div class="akeeba-form-group--actions">
			<button class="akeeba-btn akeeba-hidden-phone" type="submit">
				<span class="akion-forward"></span>
				@lang('COM_AKEEBA_CPANEL_PROFILE_BUTTON')
			</button>
		</div>
	</form>
</div>
