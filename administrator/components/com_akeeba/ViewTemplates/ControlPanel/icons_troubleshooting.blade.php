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
        <h3>@lang('COM_AKEEBA_CPANEL_HEADER_TROUBLESHOOTING')</h3>
    </header>

    <div class="akeeba-grid">
	    @if($this->permissions['backup'])
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Log">
                <span class="akion-ios-search-strong"></span>
	            @lang('COM_AKEEBA_LOG')
            </a>
	    @endif

	    @if(AKEEBA_PRO && $this->permissions['configure'])
            <a class="akeeba-action--teal"
                href="index.php?option=com_akeeba&view=Alice">
                <span class="akion-medkit"></span>
	            @lang('COM_AKEEBA_ALICE')
            </a>
	    @endif
    </div>
</section>
