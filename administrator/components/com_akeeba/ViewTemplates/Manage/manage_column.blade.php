<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Helper\Utils;

/** @var  \Akeeba\Backup\Admin\View\Manage\Html $this */
/** @var  array $record */

if (!isset($record['remote_filename']))
{
	$record['remote_filename'] = '';
}

$archiveExists    = $record['meta'] == 'ok';
$showManageRemote = in_array($record['meta'], array(
		'ok', 'remote'
	)) && !empty($record['remote_filename']) && (AKEEBA_PRO == 1);
$showUploadRemote = $this->permissions['backup'] && $archiveExists && empty($record['remote_filename']) && ($this->enginesPerProfile[$record['profile_id']] != 'none') && ($record['meta'] != 'obsolete') && (AKEEBA_PRO == 1);
$showDownload     = $this->permissions['download'] && $archiveExists;
$showViewLog      = $this->permissions['backup'] && isset($record['backupid']) && !empty($record['backupid']);
$postProcEngine   = '';
$thisPart         = '';
$thisID           = urlencode($record['id']);

if ($showUploadRemote)
{
	$postProcEngine   = $this->enginesPerProfile[$record['profile_id']];
	$showUploadRemote = !empty($postProcEngine);
}

?>
<div style="display: none">
    <div id="akeeba-buadmin-{{ (int)$record['id'] }}" tabindex="-1">
        <div class="akeeba-renderer-fef">
            <h4>@lang('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO')</h4>

            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVEEXISTS')</strong>
                <br />
                @if($record['meta'] == 'ok')
                    <span class="akeeba-label--success">
				@lang('JYES')
			</span>
                @else
                    <span class="akeeba-label--failure">
				@lang('JNO')
			</span>
                @endif
            </p>
            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVEPATH' . ($archiveExists ? '' : '_PAST'))</strong>
                <br />
                <span class="akeeba-label--information">
				{{{ Utils::getRelativePath(JPATH_SITE, dirname($record['absolute_path'])) }}}
				</span>
            </p>
            <p>
                <strong>@lang('COM_AKEEBA_BUADMIN_LBL_ARCHIVENAME' . ($archiveExists ? '' : '_PAST'))</strong>
                <br />
                <code>
                    {{{ $record['archivename'] }}}
                </code>
            </p>
        </div>

    </div>

    @if($showDownload)
        <div id="akeeba-buadmin-download-{{ (int)$record['id'] }}" tabindex="-2" role="dialog">
            <div class="akeeba-renderer-fef">
                <div class="akeeba-block--warning">
                    <h4>
                        @lang('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_TITLE')
                    </h4>
                    <p>
                        @lang('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_WARNING')
                    </p>
                </div>

                @if($record['multipart'] < 2)
                    <a class="akeeba-btn--primary--small comAkeebaManageDownloadButton"
                       data-id="{{{ $record['id'] }}}">
                        <span class="akion-ios-download"></span>
                        @lang('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD')
                    </a>
                @endif
                @if($record['multipart'] >= 2)
                    <div>
                        @sprintf('COM_AKEEBA_BUADMIN_LBL_DOWNLOAD_PARTS', (int)$record['multipart'])
                    </div>
                    @for($count = 0; $count < $record['multipart']; $count++)
                    @if($count > 0)
                    &bull;
                @endif
                <a class="akeeba-btn--small--dark comAkeebaManageDownloadButton"
                   data-id="{{{ $record['id'] }}}"
                   data-part="{{{ $count }}}">
                    <span class="akion-android-download"></span>
                    @sprintf('COM_AKEEBA_BUADMIN_LABEL_PART', $count)
                </a>
                @endfor
                @endif
            </div>
        </div>
    @endif
</div>

@if($showManageRemote)
    <div style="padding-bottom: 3pt;">
        <a class="akeeba-btn--primary akeeba_remote_management_link"
           data-management="index.php?option=com_akeeba&view=RemoteFiles&tmpl=component&task=listactions&id={{ (int)$record['id'] }}"
           data-reload="index.php?option=com_akeeba&view=Manage"
        >
            <span class="akion-cloud"></span>
            @lang('COM_AKEEBA_BUADMIN_LABEL_REMOTEFILEMGMT')
        </a>
    </div>
@elseif($showUploadRemote)
    <a class="akeeba-btn--primary akeeba_upload"
       data-upload="index.php?option=com_akeeba&view=Upload&tmpl=component&task=start&id={{ (int)$record['id'] }}"
       data-reload="index.php?option=com_akeeba&view=Manage"
       title="@sprintf('COM_AKEEBA_TRANSFER_DESC', JText::_("ENGINE_POSTPROC_{$postProcEngine}_TITLE"))">
        <span class="akion-android-upload"></span>
        @lang('COM_AKEEBA_TRANSFER_TITLE')
        (<em>{{{ $postProcEngine }}}</em>)
    </a>
@endif

<div style="padding-bottom: 3pt">
    @if($showDownload)
        <a class="akeeba-btn--{{ $showManageRemote || $showUploadRemote ? 'small--grey' : 'green' }} akeeba_download_button"
           data-dltarget="#akeeba-buadmin-download-{{ (int)$record['id'] }}"
        >
            <span class="akion-android-download"></span>
            @lang('COM_AKEEBA_BUADMIN_LOG_DOWNLOAD')
        </a>
    @endif

    @if($showViewLog)
        <a class="akeeba-btn--grey akeebaCommentPopover"
           {{ ($record['meta'] != 'obsolete') ? '' : 'disabled="disabled"' }}
           href="index.php?option=com_akeeba&view=Log&tag={{{ $record['tag'] }}}.{{{ $record['backupid'] }}}&profileid={{ (int)$record['profile_id'] }}"
           data-original-title="@lang('COM_AKEEBA_BUADMIN_LBL_LOGFILEID')"
           data-content="{{{ $record['backupid'] }}}">
            <span class="akion-ios-search-strong"></span>
            @lang('COM_AKEEBA_LOG')
        </a>
    @endif

    <a class="akeeba-btn--grey--small akeebaCommentPopover akeeba_showinfo_link"
       data-infotarget="#akeeba-buadmin-{{ (int)$record['id'] }}"
       data-content="@lang('COM_AKEEBA_BUADMIN_LBL_BACKUPINFO')"
    >
        <span class="akion-information-circled"></span>
    </a>
</div>
