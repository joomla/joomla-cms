<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

?>
@include('admin:com_akeeba/CommonTemplates/ErrorModal')
@include('admin:com_akeeba/CommonTemplates/ProfileName')

<div class="akeeba-panel--information">
    <div class="akeeba-form-section">
        <div class="AKEEBA_MASTER_FORM_STYLING akeeba-form--inline">
            <label>@lang('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR')</label>
            <span id="ak_roots_container_tab">
			{{ $this->root_select }}
			</span>
        </div>
    </div>
</div>

<div class="akeeba-container--primary">
    <div id="ak_list_container">
        <table id="table-container" class="akeeba-table--striped--dynamic-line-editor">
            <thead>
            <tr>
                <th width="120px">&nbsp;</th>
                <th width="250px">@lang('COM_AKEEBA_FILEFILTERS_LABEL_TYPE')</th>
                <th>@lang('COM_AKEEBA_FILEFILTERS_LABEL_FILTERITEM')</th>
            </tr>
            </thead>
            <tbody id="ak_list_contents" class="table-container">
            </tbody>
        </table>
    </div>
</div>
