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
{{-- Display various possible warnings about issues which directly affect the user's experience --}}
@include('admin:com_akeeba/ControlPanel/warnings')

{{-- Update notification container --}}
<div id="updateNotice"></div>

<div class="akeeba-container--66-33">
    <div>
	    {{-- Active profile switch --}}
	    @include('admin:com_akeeba/ControlPanel/profile')

	    {{-- One Click Backup icons --}}
	    @if( ! (empty($this->quickIconProfiles)) && $this->permissions['backup'])
		    @include('admin:com_akeeba/ControlPanel/oneclick')
	    @endif

	    {{-- Basic operations --}}
	    @include('admin:com_akeeba/ControlPanel/icons_basic')

	    {{-- Core Upgrade --}}
	    @include('admin:com_akeeba/ControlPanel/upgrade')

	    {{-- Troubleshooting --}}
	    @include('admin:com_akeeba/ControlPanel/icons_troubleshooting')

	    {{-- Advanced operations --}}
	    @include('admin:com_akeeba/ControlPanel/icons_advanced')

	    {{-- Include / Exclude data --}}
	    @if($this->permissions['configure'])
		    @include('admin:com_akeeba/ControlPanel/icons_includeexclude')
	    @endif


    </div>
    <div>
	    {{-- Status Summary --}}
	    @include('admin:com_akeeba/ControlPanel/sidebar_status')

	    {{-- Backup stats --}}
	    @include('admin:com_akeeba/ControlPanel/sidebar_backup')
    </div>

</div>

{{-- Footer --}}
@include('admin:com_akeeba/ControlPanel/footer')

{{-- Usage statistics collection IFRAME --}}
@if ($this->statsIframe)
    {{ $this->statsIframe }}
@endif
