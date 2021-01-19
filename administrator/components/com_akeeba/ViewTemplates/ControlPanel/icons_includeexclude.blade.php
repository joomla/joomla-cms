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
        <h3>@lang('COM_AKEEBA_CPANEL_HEADER_INCLUDEEXCLUDE')</h3>
    </header>

    <div class="akeeba-grid">
        @if(AKEEBA_PRO)
            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=MultipleDatabases">
                <span class="akion-arrow-swap"></span>
	            @lang('COM_AKEEBA_MULTIDB')
            </a>

            <a class="akeeba-action--green"
                href="index.php?option=com_akeeba&view=IncludeFolders">
                <span class="akion-folder"></span>
	            @lang('COM_AKEEBA_INCLUDEFOLDER')
            </a>
        @endif

        <a class="akeeba-action--red"
            href="index.php?option=com_akeeba&view=FileFilters">
            <span class="akion-filing"></span>
	        @lang('COM_AKEEBA_FILEFILTERS')
        </a>

        <a class="akeeba-action--red"
            href="index.php?option=com_akeeba&view=DatabaseFilters">
            <span class="akion-ios-grid-view"></span>
	        @lang('COM_AKEEBA_DBFILTER')
        </a>

        @if(AKEEBA_PRO)
            <a class="akeeba-action--red"
                href="index.php?option=com_akeeba&view=RegExFileFilters">
                <span class="akion-ios-folder"></span>
	            @lang('COM_AKEEBA_REGEXFSFILTERS')
            </a>

            <a class="akeeba-action--red"
                href="index.php?option=com_akeeba&view=RegExDatabaseFilters">
                <span class="akion-ios-box"></span>
	            @lang('COM_AKEEBA_REGEXDBFILTERS')
            </a>
        @endif

    </div></section>
