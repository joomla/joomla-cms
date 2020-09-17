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
        <span>{{ $this->root_select }}</span>
    </div>
    <div id="addnewfilter" class="akeeba-form-group--actions">
        <label>
            @lang('COM_AKEEBA_FILEFILTERS_LABEL_ADDNEWFILTER')
        </label>

        <button class="akeeba-btn--grey" id="comAkeebaDatabaseFiltersAddNewTables">
            @lang('COM_AKEEBA_DBFILTER_TYPE_TABLES')
        </button>

        <button class="akeeba-btn--grey" id="comAkeebaDatabaseFiltersAddNewTableData">
            @lang('COM_AKEEBA_DBFILTER_TYPE_TABLEDATA')
        </button>
    </div>
</div>

<div class="akeeba-panel--primary">
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
</div>
