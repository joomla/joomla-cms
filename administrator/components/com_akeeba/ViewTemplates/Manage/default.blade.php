<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html $this */
?>
@if (version_compare(JVERSION, '3.999.999', 'lt'))
@jhtml('formbehavior.chosen')
@endif

@if($this->promptForBackupRestoration)
    @include('admin:com_akeeba/Manage/howtorestore_modal')
@endif

<div class="akeeba-block--info">
    <h4>@lang('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_LEGEND')</h4>
    <p>
		@sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_' . (AKEEBA_PRO ? 'PRO' : 'CORE'), 'http://akee.ba/abrestoreanywhere', 'index.php?option=com_akeeba&view=Transfer', 'https://www.akeeba.com/latest-kickstart-core.zip')
    </p>
    <p>
        @if (!AKEEBA_PRO)
            @sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_CORE_INFO_ABOUT_PRO', 'https://www.akeeba.com/products/akeeba-backup.html')
        @endif
    </p>
</div>

<div id="j-main-container">
    <form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form">

        <section class="akeeba-panel--33-66 akeeba-filter-bar-container">
            <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
                <div class="akeeba-filter-element akeeba-form-group">
                    <input type="text" name="description" placeholder="@lang('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION')"
                           id="filter_description"
                           value="{{{ $this->fltDescription }}}"
                           title="@lang('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION')" />
                </div>

                <div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
                    @jhtml('calendar', $this->fltFrom, 'from', 'from', '%Y-%m-%d', array('class' => 'input-small'))
                </div>

                <div class="akeeba-filter-element akeeba-form-group akeeba-filter-joomlacalendarfix">
                    @jhtml('calendar', $this->fltTo, 'to', 'to', '%Y-%m-%d', array('class' => 'input-small'))
                </div>

                <div class="akeeba-filter-element akeeba-form-group">
                    <button class="akeeba-btn--grey akeeba-btn--icon-only akeeba-btn--small akeeba-hidden-phone"
                            type="submit" title="@lang('JSEARCH_FILTER_SUBMIT')">
                        <span class="akion-search"></span>
                    </button>
                </div>

                <div class="akeeba-filter-element akeeba-form-group">
                    {{-- Joomla 3.x: Chosen does not work with attached event handlers, only with inline event scripts (e.g. onchange) --}}
                    @if (version_compare(JVERSION, '3.999.999', 'lt'))
                        @jhtml('select.genericlist', $this->profilesList, 'profile', ['list.select' => $this->fltProfile, 'list.attr' => ['class' => 'advancedSelect', 'onchange' => 'document.forms.adminForm.submit();'], 'id' => 'comAkeebaManageProfileSelector'])
                    @else
                        @jhtml('select.genericlist', $this->profilesList, 'profile', ['list.select' => $this->fltProfile, 'list.attr' => ['class' => 'advancedSelect'], 'id' => 'comAkeebaManageProfileSelector'])
                    @endif
                </div>

                <div class="akeeba-filter-element akeeba-form-group">
                    {{-- Joomla 3.x: Chosen does not work with attached event handlers, only with inline event scripts (e.g. onchange) --}}
                    @if (version_compare(JVERSION, '3.999.999', 'lt'))
                        @jhtml('select.genericlist', $this->frozenList, 'frozen', ['list.select' => $this->fltFrozen, 'list.attr' => ['class' => 'advancedSelect', 'onchange' => 'document.forms.adminForm.submit();'], 'id' => 'comAkeebaManageFrozenSelector'])
                    @else
                        @jhtml('select.genericlist', $this->frozenList, 'frozen', ['list.select' => $this->fltFrozen, 'list.attr' => ['class' => 'advancedSelect'], 'id' => 'comAkeebaManageFrozenSelector'])
                    @endif
                </div>
            </div>

			<div class="akeeba-filter-bar akeeba-filter-bar--right">
				@jhtml('FEFHelper.browse.orderheader', null, $this->sortFields, $this->getPagination(), $this->lists->order, $this->lists->order_Dir)
			</div>
        </section>

        <table class="akeeba-table akeeba-table--striped" id="itemsList">
            <thead>
            <tr>
                <th width="32">
                    @jhtml('FEFHelper.browse.checkall')
                </th>
                <th width="48" class="akeeba-hidden-phone">
					@sortgrid('id', 'COM_AKEEBA_BUADMIN_LABEL_ID')
                </th>
                <th>
                    @sortgrid('frozen', 'COM_AKEEBA_BUADMIN_LABEL_FROZEN')
                </th>
                <th>
					@sortgrid('description', 'COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION')
                </th>
                <th class="akeeba-hidden-phone">
					@sortgrid('profile_id', 'COM_AKEEBA_BUADMIN_LABEL_PROFILEID')
                </th>
                <th width="80">
                    @lang('COM_AKEEBA_BUADMIN_LABEL_DURATION')
                </th>
                <th width="40">
                    @lang('COM_AKEEBA_BUADMIN_LABEL_STATUS')
                </th>
                <th width="80" class="akeeba-hidden-phone">
                    @lang('COM_AKEEBA_BUADMIN_LABEL_SIZE')
                </th>
                <th class="akeeba-hidden-phone">
                    @lang('COM_AKEEBA_BUADMIN_LABEL_MANAGEANDDL')
                </th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <td colspan="11" class="center">
                    {{ $this->pagination->getListFooter() }}
                </td>
            </tr>
            </tfoot>
            <tbody>
            @if(empty($this->items))
                <tr>
                    <td colspan="11" class="center">
                        @lang('COM_AKEEBA_BACKUP_STATUS_NONE')
                    </td>
                </tr>
            @endif
            @if( ! (empty($this->items)))
				<?php $id = 1; $i = 0; ?>
                @foreach($this->items as $record)
					<?php
					$id = 1 - $id;
					list($originDescription, $originIcon) = $this->getOriginInformation($record);
					list($startTime, $duration, $timeZoneText) = $this->getTimeInformation($record);
					list($statusClass, $statusIcon) = $this->getStatusInformation($record);
					$profileName = $this->getProfileName($record);

					$frozenIcon  = 'akion-waterdrop';
					$frozenTask  = 'freeze';
					$frozenTitle = \JText::_('COM_AKEEBA_BUADMIN_LABEL_ACTION_FREEZE');

					if ($record['frozen'])
                    {
	                    $frozenIcon  = 'akion-ios-snowy';
	                    $frozenTask  = 'unfreeze';
	                    $frozenTitle = \JText::_('COM_AKEEBA_BUADMIN_LABEL_ACTION_UNFREEZE');
                    }
					?>
                    <tr class="row{{ $id }}">
                        <td>@jhtml('grid.id', ++$i, $record['id'])</td>
                        <td class="akeeba-hidden-phone">
                            {{{ $record['id'] }}}
                        </td>
                        <td>
                            <a href="#" onclick="return listItemTask('cb{{ $i }}', '{{$frozenTask}}')" title="{{$frozenTitle}}">
                                <span class="{{ $frozenIcon }}"></span>
                            </a>
                        </td>
                        <td>
						<span class="{{ $originIcon }} akeebaCommentPopover" rel="popover"
                              title="@lang('COM_AKEEBA_BUADMIN_LABEL_ORIGIN')"
                              data-content="{{{ $originDescription }}}"></span>
                            @if( ! (empty($record['comment'])))
                                <span class="akion-help-circled akeebaCommentPopover" rel="popover"
                                      data-content="{{{ $record['comment'] }}}"></span>
                            @endif
                            <a href="{{{ JUri::base() }}}index.php?option=com_akeeba&view=Manage&task=showcomment&id={{{ $record['id'] }}}">
                                {{{ empty($record['description']) ? JText::_('COM_AKEEBA_BUADMIN_LABEL_NODESCRIPTION') : $record['description'] }}}

                            </a>
                            <br />
                            <div class="akeeba-buadmin-startdate" title="@lang('COM_AKEEBA_BUADMIN_LABEL_START')">
                                <small>
                                    <span class="akion-calendar"></span>
                                    {{{ $startTime }}} {{{ $timeZoneText }}}
                                </small>
                            </div>
                        </td>
                        <td class="akeeba-hidden-phone">
                            #{{{ (int)$record['profile_id'] }}}. {{{ $profileName }}}

                            <br />
                            <small>
                                <em>{{{ $this->translateBackupType($record['type']) }}}</em>
                            </small>
                        </td>
                        <td>
                            {{{ $duration }}}
                        </td>
                        <td>
						<span class="{{ $statusClass }} akeebaCommentPopover" rel="popover"
                              title="@lang('COM_AKEEBA_BUADMIN_LABEL_STATUS')"
                              data-content="@lang('COM_AKEEBA_BUADMIN_LABEL_STATUS_' . $record['meta'])">
							<span class="{{ $statusIcon }}"></span>
						</span>
                        </td>
                        <td class="akeeba-hidden-phone">
                            @if($record['meta'] == 'ok')
                                {{{ $this->formatFilesize($record['size']) }}}

                            @elseif($record['total_size'] > 0)
                                <i>{{ $this->formatFilesize($record['total_size']) }}</i>
                                @else
                                &mdash;
                            @endif
                        </td>
                        <td class="akeeba-hidden-phone">
                            @include('admin:com_akeeba/Manage/manage_column', ['record' => &$record])
                        </td>
                    </tr>
                @endforeach
            @endif
            </tbody>
        </table>

        <div class="akeeba-hidden-fields-container">
            <input type="hidden" name="option" id="option" value="com_akeeba" />
            <input type="hidden" name="view" id="view" value="Manage" />
            <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
            <input type="hidden" name="task" id="task" value="default" />
            <input type="hidden" name="filter_order" id="filter_order" value="{{{ $this->lists->order }}}" />
            <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="{{{ $this->lists->order_Dir }}}" />
            <input type="hidden" name="@token(true)" value="1" />
        </div>
    </form>
</div>
