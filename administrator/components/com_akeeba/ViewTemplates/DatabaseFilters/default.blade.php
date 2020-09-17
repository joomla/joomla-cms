<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

/** @var \Akeeba\Backup\Admin\View\DatabaseFilters\Html $this */
?>
@include('admin:com_akeeba/CommonTemplates/ErrorModal')
@include('admin:com_akeeba/CommonTemplates/ProfileName')

<div class="AKEEBA_MASTER_FORM_STYLING akeeba-form--inline akeeba-panel--info">
    <div class="akeeba-form-group">
        <label>@lang('COM_AKEEBA_DBFILTER_LABEL_ROOTDIR')</label>
        {{ $this->root_select }}
    </div>
    <div class="akeeba-form-group--actions">
        <button class="akeeba-btn--green" id="comAkeebaDatabaseFiltersExcludeNonCMS">
            <span class="akion-ios-flag"></span>
            @lang('COM_AKEEBA_DBFILTER_LABEL_EXCLUDENONCORE')
        </button>
        <button class="akeeba-btn--red" id="comAkeebaDatabaseFiltersNuke">
            <span class="akion-ios-loop-strong"></span>
            @lang('COM_AKEEBA_DBFILTER_LABEL_NUKEFILTERS')
        </button>
    </div>
</div>

<div id="ak_main_container" class="akeeba-container--100">
</div>

<div class="akeeba-panel--info">
    <header class="akeeba-block-header">
        <h3>
            @lang('COM_AKEEBA_DBFILTER_LABEL_TABLES')
        </h3>
    </header>
    <div id="tables"></div>
</div>
