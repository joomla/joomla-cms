<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

/** @var \Akeeba\Backup\Admin\View\Browser\Html $this */

Text::script('COM_AKEEBA_CONFIG_UI_ROOTDIR', true);

?>
@if(empty($this->folder))
    <form action="index.php" method="post" name="adminForm" id="adminForm">
        <input type="hidden" name="option" value="com_akeeba" />
        <input type="hidden" name="view" value="Browser" />
        <input type="hidden" name="format" value="html" />
        <input type="hidden" name="tmpl" value="component" />
        <input type="hidden" name="folder" id="folder" value="" />
        <input type="hidden" name="processfolder" id="processfolder" value="0" />
        <input type="hidden" name="@token(true)" value="1" />
    </form>
@endif

@if(!(empty($this->folder)))
    <div class="akeeba-panel--100 akeeba-panel--primary">
        <div>
            <form action="index.php" method="get" name="adminForm" id="adminForm"
                  class="akeeba-form--inline akeeba-form--with-hidden">
                <span title="@lang($this->writable ? 'COM_AKEEBA_CPANEL_LBL_WRITABLE' : 'COM_AKEEBA_CPANEL_LBL_UNWRITABLE')"
                      class="{{ $this->writable ? 'akeeba-label--green' : 'akeeba-label--red' }}"
                >
                    <span class="{{ $this->writable ? 'akion-checkmark-circled' : 'akion-ios-close' }}"></span>
                </span>
                <input type="text" name="folder" id="folder" value="{{{ $this->folder }}}" />

                <button class="akeeba-btn--primary" id="comAkeebaBrowserGo">
                    <span class="akion-folder"></span>
                    @lang('COM_AKEEBA_BROWSER_LBL_GO')
                </button>

                <button class="akeeba-btn--green" id="comAkeebaBrowserUseThis">
                    <span class="akion-share"></span>
                    @lang('COM_AKEEBA_BROWSER_LBL_USE')
                </button>

                <div class="akeeba-hidden-fields-container">
                    <input type="hidden" name="folderraw" id="folderraw"
                           value="{{{ $this->folder_raw }}}" />
                    <input type="hidden" name="@token(true)" value="1" />
                    <input type="hidden" name="option" value="com_akeeba" />
                    <input type="hidden" name="view" value="Browser" />
                    <input type="hidden" name="tmpl" value="component" />
                </div>
            </form>
        </div>
    </div>

    @if(count($this->breadcrumbs))
        <div class="akeeba-panel--100 akeeba-panel--information">
            <div>
                <ul class="akeeba-breadcrumb">
					<?php $i = 0 ?>
                    @foreach($this->breadcrumbs as $crumb)
						<?php $i++; ?>
                        <li class="{{ ($i < count($this->breadcrumbs)) ? '' : 'active' }}">
                            @if($i < count($this->breadcrumbs))
                                <a href="{{{ Uri::base() . "index.php?option=com_akeeba&view=Browser&tmpl=component&folder=" . urlencode($crumb['folder']) }}}">
                                    {{{ $crumb['label'] }}}
                                </a>
                                <span class="divider">&bull;</span>
                            @else
                                {{{ $crumb['label'] }}}
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="akeeba-panel--100 akeeba-panel">
        <div>
            @if(count($this->subfolders))
                <table class="akeeba-table akeeba-table--striped">
                    <tr>
                        <td>
                            <a class="akeeba-btn--dark--small"
                               href="{{{ Uri::base() }}}index.php?option=com_akeeba&view=Browser&tmpl=component&folder={{{ $this->parent }}}">
                                <span class="akion-arrow-up-a"></span>
                                @lang('COM_AKEEBA_BROWSER_LBL_GOPARENT')
                            </a>
                        </td>
                    </tr>
                    @foreach($this->subfolders as $subfolder)
                        <tr>
                            <td>
                                <a class="akeeba-browser-folder" href="{{{ Uri::base() }}}index.php?option=com_akeeba&view=Browser&tmpl=component&folder={{{ $this->folder . '/' . $subfolder }}}">{{{ $subfolder }}}</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            @else
                @if(!$this->exists)
                    <div class="akeeba-block--failure">
                        @lang('COM_AKEEBA_BROWSER_ERR_NOTEXISTS')
                    </div>
                @elseif(!$this->inRoot)
                    <div class="akeeba-block--warning">
                        @lang('COM_AKEEBA_BROWSER_ERR_NONROOT')
                    </div>
                @elseif($this->openbasedirRestricted)
                    <div class="akeeba-block--failure">
                        @lang('COM_AKEEBA_BROWSER_ERR_BASEDIR')
                    </div>
                @else
                    <table class="akeeba-table--striped">
                        <tr>
                            <td>
                                <a class="akeeba-btn--dark--small"
                                   href="{{{ Uri::base() }}}index.php?option=com_akeeba&view=Browser&tmpl=component&folder={{{ $this->parent }}}">
                                    <span class="akion-arrow-up-a"></span>
                                    @lang('COM_AKEEBA_BROWSER_LBL_GOPARENT')
                                </a>
                            </td>
                        </tr>
                    </table>
                @endif{{-- secondary block --}}
            @endif {{-- count($this->subfolders) --}}
        </div>
    </div>
@endif
