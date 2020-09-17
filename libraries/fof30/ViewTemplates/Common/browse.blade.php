<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 3 or later
 */

defined('_JEXEC') || die;

/**
 * Template for Browse views using the FEF renderer
 *
 * Use this by extending it (I'm using -at- instead of the actual at-sign)
 * -at-extends('any:lib_fof30/Common/browse')
 *
 * Override the following sections in your Blade template:
 *
 * browse-page-top
 *      Content to put above the form
 *
 * browse-page-bottom
 *      Content to put below the form
 *
 * browse-filters
 *      Filters to place above the table. They are placed inside an inline form. Wrap them in
 *      <div class="akeeba-filter-element akeeba-form-group">
 *
 * browse-table-header
 *      The table header. At the very least you need to add the table column headers. You can
 *      optionally add one or more <tr> with filters at the top.
 *
 * browse-table-body-withrecords
 *      Loop through the records and create <tr>s.
 *
 * browse-table-body-norecords
 *      [ Optional ] The <tr> to show when no records are present. Default is the "no records" text.
 *
 * browse-table-footer
 *      [ Optional ] The table footer. By default that's just the pagination footer.
 *
 * browse-hidden-fields
 *      [ Optional ] Any additional hidden INPUTs to add to the form. By default this is empty.
 *      The default hidden fields (option, view, task, ordering fields, boxchecked and token) can
 *      not be removed.
 *
 * Do not override any other section. The overridden sections should be closed with -at-override instead of -at-stop.
 *//** @var  FOF30\View\DataView\Html  $this */

$ajaxOrderingSupport = $this->hasAjaxOrderingSupport();
?>

{{-- Allow tooltips, used in grid headers --}}
@if (version_compare(JVERSION, '3.999.999', 'le'))
    @jhtml('behavior.tooltip')
@endif
{{-- Allow SHIFT+click to select multiple rows --}}
@jhtml('behavior.multiselect')


@section('browse-filters')
{{-- Filters above the table. --}}
@stop

@section('browse-table-header')
{{-- Table header. Column headers and optional filters displayed above the column headers. --}}
@stop

@section('browse-table-body-norecords')
{{-- Table body shown when no records are present. --}}
<tr>
    <td colspan="99">
        @lang($this->getContainer()->componentName . '_COMMON_NORECORDS')
    </td>
</tr>
@stop

@section('browse-table-body-withrecords')
{{-- Table body shown when records are present. --}}
<?php $i = 0; ?>
@foreach($this->items as $row)
<tr>
    {{-- You need to implement me! --}}
</tr>
@endforeach
@stop

@section('browse-table-footer')
    {{-- Table footer. The default is showing the pagination footer. --}}
    <tr>
        <td colspan="99" class="center">
            {{ $this->pagination->getListFooter() }}
        </td>
    </tr>
@stop

@section('browse-hidden-fields')
    {{-- Put your additional hidden fields in this section --}}
@stop

@yield('browse-page-top')

{{-- Administrator form for browse views --}}
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form">
    {{-- Filters and ordering --}}
    <section class="akeeba-panel--33-66 akeeba-filter-bar-container">
        <div class="akeeba-filter-bar akeeba-filter-bar--left akeeba-form-section akeeba-form--inline">
            @yield('browse-filters')
        </div>
		<div class="akeeba-filter-bar akeeba-filter-bar--right">
			@jhtml('FEFHelper.browse.orderjs', $this->lists->order)
			@jhtml('FEFHelper.browse.orderheader', $this)
		</div>
	</section>

    <table class="akeeba-table akeeba-table--striped--hborder--hover" id="itemsList">
        <thead>
        @yield('browse-table-header')
        </thead>
        <tfoot>
        @yield('browse-table-footer')
        </tfoot>
        <tbody
                @if(($ajaxOrderingSupport !== false) && $ajaxOrderingSupport['saveOrder'])
                class="js-draggable"
                data-url="{{ $ajaxOrderingSupport['saveOrderURL'] }}"
                data-direction="{{ strtolower($this->getModel()->getState('filter_order_Dir', null, 'cmd')) }}"
                data-nested="{{ ($this->getModel() instanceof \FOF30\Model\TreeModel) ? 'true' : 'false' }}"
                @endif
        >
        @unless(count($this->items))
            @yield('browse-table-body-norecords')
        @else
            @yield('browse-table-body-withrecords')
        @endunless
        </tbody>
    </table>

    {{-- Hidden form fields --}}
    <div class="akeeba-hidden-fields-container">
        @section('browse-default-hidden-fields')
            <input type="hidden" name="option" id="option" value="{{{ $this->getContainer()->componentName }}}"/>
            <input type="hidden" name="view" id="view" value="{{{ $this->getName() }}}"/>
            <input type="hidden" name="boxchecked" id="boxchecked" value="0"/>
            <input type="hidden" name="task" id="task" value="{{{ $this->getTask() }}}"/>
            <input type="hidden" name="filter_order" id="filter_order" value="{{{ $this->lists->order }}}"/>
            <input type="hidden" name="filter_order_Dir" id="filter_order_Dir" value="{{{ $this->lists->order_Dir }}}"/>
            <input type="hidden" name="@token()" value="1"/>
        @show
        @yield('browse-hidden-fields')
    </div>
</form>

@yield('browse-page-bottom')
