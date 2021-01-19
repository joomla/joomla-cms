<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

// All of the buttons in this panel require the Configure privilege
if (!$this->permissions['configure'])
{
	return;
}
?>
@if(AKEEBA_PRO)
    <section class="akeeba-panel--info">
        <header class="akeeba-block-header">
            <h3>@lang('COM_AKEEBA_CPANEL_HEADER_ADVANCED')</h3>
        </header>

        <div class="akeeba-grid">
            @if($this->permissions['configure'])
                <a class="akeeba-action--teal"
                   href="index.php?option=com_akeeba&view=Schedule">
                    <span class="akion-calendar"></span>
                    @lang('COM_AKEEBA_SCHEDULE')
                </a>
            @endif

            @if($this->permissions['configure'])
                <a class="akeeba-action--orange"
                   href="index.php?option=com_akeeba&view=Discover">
                    <span class="akion-ios-download"></span>
                    @lang('COM_AKEEBA_DISCOVER')
                </a>
            @endif

            @if($this->permissions['configure'])
                <a class="akeeba-action--orange"
                   href="index.php?option=com_akeeba&view=S3Import">
                    <span class="akion-ios-cloud-download"></span>
                    @lang('COM_AKEEBA_S3IMPORT')
                </a>
            @endif
        </div>
    </section>
@endif
