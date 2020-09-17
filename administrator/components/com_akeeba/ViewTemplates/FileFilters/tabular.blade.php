<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

/** @var \Akeeba\Backup\Admin\View\FileFilters\Html $this */
?>
@include('admin:com_akeeba/CommonTemplates/ErrorModal')
@include('admin:com_akeeba/CommonTemplates/ProfileName')

<div class="AKEEBA_MASTER_FORM_STYLING akeeba-form--inline akeeba-panel--info">
    <div class="akeeba-form-group">
        <label>@lang('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR')</label>
        {{ $this->root_select }}
    </div>
    <div id="addnewfilter" class="akeeba-form-group--actions">
        <label>
            @lang('COM_AKEEBA_FILEFILTERS_LABEL_ADDNEWFILTER')
        </label>
        <button class="akeeba-btn--grey" id="comAkeebaFileFiltersAddDirectories">
            @lang('COM_AKEEBA_FILEFILTERS_TYPE_DIRECTORIES')
        </button>
        <button class="akeeba-btn--grey" id="comAkeebaFileFiltersAddSkipfiles">
            @lang('COM_AKEEBA_FILEFILTERS_TYPE_SKIPFILES')
        </button>
        <button class="akeeba-btn--grey" id="comAkeebaFileFiltersAddSkipdirs">
            @lang('COM_AKEEBA_FILEFILTERS_TYPE_SKIPDIRS')
        </button>
        <button class="akeeba-btn--grey" id="comAkeebaFileFiltersAddFiles">
            @lang('COM_AKEEBA_FILEFILTERS_TYPE_FILES')
        </button>
    </div>
</div>

<form id="ak_roots_container_tab" class="akeeba-panel--primary">
    <div id="ak_list_container">
        <table id="ak_list_table" class="akeeba-table--striped">
            <thead>
            <tr>
                <td width="250px">@lang('COM_AKEEBA_FILEFILTERS_LABEL_TYPE')</td>
                <td>@lang('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM')</td>
            </tr>
            </thead>
            <tbody id="ak_list_contents">
            </tbody>
        </table>
    </div>
</form>
