<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

?>

<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form akeeba-form--with-hidden">
    @include('admin:com_akeeba/CommonTemplates/ProfileName')

    <section class="akeeba-panel--50-50 akeeba-filter-bar-container">
        <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
            <div class="akeeba-filter-element akeeba-form-group">
                <input type="text" name="description" id="description"
                       value="{{{ $this->getModel()->getState('description', '', 'string') }}}" size="30"
                       class="akeebaGridViewAutoSubmitOnChange"
                       placeholder="@lang('COM_AKEEBA_PROFILES_COLLABEL_DESCRIPTION')"
                />
            </div>
        </div>
        <div class="akeeba-filter-bar akeeba-filter-bar--right">
            <div class="akeeba-filter-element akeeba-form-group">
                <label for="limit" class="element-invisible">
                    @lang('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC')
                </label>
                {{ $this->pagination->getLimitBox() }}
            </div>

            <div class="akeeba-filter-element akeeba-form-group">
                <label for="directionTable" class="element-invisible">
                    @lang('JFIELD_ORDERING_DESC')
                </label>
                <select name="directionTable" id="directionTable"
                        class="input-medium custom-select akeebaGridViewOrderTable">
                    <option value="">
                        @lang('JFIELD_ORDERING_DESC')
                    </option>
                    <option value="asc" {{ ($this->getLists()->order_Dir == 'asc') ? 'selected="selected"' : '' }}>
                        @lang('JGLOBAL_ORDER_ASCENDING')
                    </option>
                    <option value="desc" {{ ($this->getLists()->order_Dir == 'desc') ? 'selected="selected"' : '' }}>
                        @lang('JGLOBAL_ORDER_DESCENDING')
                    </option>
                </select>
            </div>

            <div class="akeeba-filter-element akeeba-form-group">
                <label for="sortTable" class="element-invisible">
                    @lang('JGLOBAL_SORT_BY')
                </label>
                <select name="sortTable" id="sortTable" class="input-medium custom-select akeebaGridViewOrderTable">
                    <option value="">
                        @lang('JGLOBAL_SORT_BY')
                    </option>
                    @jhtml('select.options', $this->sortFields, 'value', 'text', $this->getLists()->order)
                </select>
            </div>
        </div>
    </section>

    <table class="adminlist akeeba-table akeeba-table--striped">
        <thead>
        <tr>
            <th width="20">
                <input type="checkbox" name="toggle" value="" class="akeebaGridViewCheckAll" />
            </th>
            <th width="40">
                @jhtml('grid.sort', 'JGRID_HEADING_ID', 'id', $this->lists->order_Dir, $this->lists->order, 'browse')
            </th>
            <th width="20%"></th>
            <th>
                @jhtml('grid.sort', 'COM_AKEEBA_PROFILES_COLLABEL_DESCRIPTION', 'description', $this->lists->order_Dir, $this->lists->order, 'browse')
            </th>
            <th>
                @lang('COM_AKEEBA_CONFIG_QUICKICON_LABEL')
            </th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <td colspan="11">
                {{ $this->pagination->getListFooter() }}

            </td>
        </tr>
        </tfoot>
        <tbody>
		<?php $i = 0; ?>
        @foreach( $this->items as $profile )
            <tr>
                <td>
                    @jhtml('grid.id', ++$i, $profile->id)
                </td>
                <td>
                    {{ (int) $profile->id }}
                </td>
                <td>
                    <a class="akeeba-btn akeeba-btn--small akeeba-btn--primary"
                       href="index.php?option=com_akeeba&task=SwitchProfile&profileid={{ (int)$profile->id }}&returnurl={{ base64_encode(\Joomla\CMS\Uri\Uri::base() . 'index.php?option=com_akeeba&view=Configuration') }}&@token(true)=1">
                        <span class="icon-cog icon-white"></span>
                        @lang('COM_AKEEBA_CONFIG_UI_CONFIG')
                    </a>
                    &nbsp;
                    <a class="akeeba-btn akeeba-btn--small akeeba-btn--dark"
                       href="index.php?option=com_akeeba&view=Profile&task=read&id={{ $profile->id }}&basename={{ \Joomla\CMS\Application\ApplicationHelper::stringURLSafe($profile->description) }}&format=json&@token(true)=1">
                        <span class="icon-download"></span>
                        @lang('COM_AKEEBA_PROFILES_BTN_EXPORT')
                    </a>
                </td>
                <td>
                    <a href="index.php?option=com_akeeba&amp;view=Profiles&amp;task=edit&amp;id={{ (int) $profile->id }}">
                        {{{ $profile->description }}}
                    </a>
                </td>
                <td>
                    @jhtml('FEFHelper.browse.published', $profile->quickicon, $i, 'quickicon_')
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba" />
        <input type="hidden" name="view" value="Profiles" />
        <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
        <input type="hidden" name="task" id="task" value="browse" />
        <input type="hidden" name="hidemainmenu" id="hidemainmenu" value="0" />
        <input type="hidden" name="filter_order" id="filter_order"
               value="{{{ $this->lists->order }}}" />
        <input type="hidden" name="filter_order_Dir" id="filter_order_Dir"
               value="{{{ $this->lists->order_Dir }}}" />
        <input type="hidden" name="@token(true)" value="1" />
    </div>
</form>

<form action="index.php" method="post" name="importForm" enctype="multipart/form-data"
      id="importForm"
      class="akeeba-form akeeba-form--inline akeeba-panel--primary"
>
    <input type="hidden" name="option" value="com_akeeba" />
    <input type="hidden" name="view" value="Profiles" />
    <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
    <input type="hidden" name="task" id="task" value="import" />
    <input type="hidden" name="@token(true)" value="1" />

    <input type="file" name="importfile" class="input-medium" />

    <button class="akeeba-btn akeeba-btn--green">
        <span class="icon-upload icon-white"></span>
        @lang('COM_AKEEBA_PROFILES_HEADER_IMPORT')
    </button>

    <span class="help-inline">
		@lang('COM_AKEEBA_PROFILES_LBL_IMPORT_HELP')
	</span>
</form>
