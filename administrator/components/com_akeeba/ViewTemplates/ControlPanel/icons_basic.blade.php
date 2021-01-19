<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<section class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3>@lang('COM_AKEEBA_CPANEL_HEADER_BASICOPS')</h3>
    </header>

    <div class="akeeba-grid">
	    @if($this->permissions['backup'])
            <a class="akeeba-action--green"
               href="index.php?option=com_akeeba&view=Backup">
                <span class="akion-play"></span>
	            @lang('COM_AKEEBA_BACKUP')
            </a>
	    @endif

	    @if($this->permissions['download'] && AKEEBA_PRO)
            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=Transfer">
                <span class="akion-android-open"></span>
	            @lang('COM_AKEEBA_TRANSFER')
            </a>
	    @endif

        <a class="akeeba-action--teal"
            href="index.php?option=com_akeeba&view=Manage">
            <span class="akion-ios-list"></span>
	        @lang('COM_AKEEBA_BUADMIN')
        </a>

	    @if($this->permissions['configure'])
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Configuration">
                <span class="akion-ios-gear"></span>
	            @lang('COM_AKEEBA_CONFIG')
            </a>
	    @endif

	    @if($this->permissions['configure'])
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Profiles">
                <span class="akion-person-stalker"></span>
	            @lang('COM_AKEEBA_PROFILES')
            </a>
	    @endif
    </div>
</section>
