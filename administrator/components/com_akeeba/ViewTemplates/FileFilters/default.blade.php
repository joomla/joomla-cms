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
        <label>
            @lang('COM_AKEEBA_FILEFILTERS_LABEL_ROOTDIR')
        </label>
        <span>{{ $this->root_select }}</span>
    </div>
    <div class="akeeba-form-group--actions">
        <button class="akeeba-btn--red" id="comAkeebaFileFiltersNuke">
            <span class="akion-ios-trash"></span>
            @lang('COM_AKEEBA_FILEFILTERS_LABEL_NUKEFILTERS')
        </button>

        <a class="akeeba-btn--grey" href="index.php?option=com_akeeba&view=FileFilters&task=tabular">
            <span class="akion-ios-list-outline"></span>
            @lang('COM_AKEEBA_FILEFILTERS_LABEL_VIEWALL')
        </a>
    </div>
</div>

<div id="ak_crumbs_container" class="akeeba-panel--100 akeeba-panel--information">
    <div>
        <ul id="ak_crumbs" class="akeeba-breadcrumb"></ul>
    </div>
</div>

<div id="ak_main_container" class="akeeba-container--50-50">
    <div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
                    @lang('COM_AKEEBA_FILEFILTERS_LABEL_DIRS')
                </h3>
            </header>
            <div id="folders"></div>
        </div>
    </div>

    <div>
        <div class="akeeba-panel--info">
            <header class="akeeba-block-header">
                <h3>
                    @lang('COM_AKEEBA_FILEFILTERS_LABEL_FILES')
                </h3>
            </header>
            <div id="files"></div>
        </div>
    </div>
</div>
