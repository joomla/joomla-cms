<?php
/**
 * @package     FOF
 * @copyright   Copyright (c)2010-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license     GNU GPL version 3 or later
 */

defined('_JEXEC') || die;

/**
 * Template for Edit (form) views using the FEF renderer
 *
 * Use this by extending it (I'm using -at- instead of the at-sign)
 * -at-extends('any:lib_fof30/Common/edit')
 *
 * Override the following sections in your Blade template:
 *
 * edit-page-top
 *      Content to put above the form
 *
 * edit-page-bottom
 *      Content to put below the form
 *
 * edit-form-body
 *      The page's body, inside the form
 *
 * edit-hidden-fields
 *      [ Optional ] Any additional hidden INPUTs to add to the form. By default this is empty.
 *      The default hidden fields (option, view, task, ordering fields, boxchecked and token) can
 *      not be removed.
 *
 * Do not override any other section. The overridden sections should be closed with -at-override instead of -at-stop.
 *//** @var  FOF30\View\DataView\Html  $this */

?>

@section('edit-form-body')
    {{-- Put your form body in this section --}}
@stop

@section('edit-hidden-fields')
    {{-- Put your additional hidden fields in this section --}}
@stop

@yield('edit-page-top')

{{-- Administrator form for browse views --}}
<form action="index.php" method="post" name="adminForm" id="adminForm" class="akeeba-form--horizontal">
    {{-- Main form body --}}
    @yield('edit-form-body')
    {{-- Hidden form fields --}}
    <div class="akeeba-hidden-fields-container">
        @section('browse-default-hidden-fields')
            <input type="hidden" name="option" id="option" value="{{{ $this->getContainer()->componentName }}}"/>
            <input type="hidden" name="view" id="view" value="{{{ $this->getName() }}}"/>
            <input type="hidden" name="task" id="task" value="{{{ $this->getTask() }}}"/>
            <input type="hidden" name="id" id="id" value="{{{ $this->getItem()->getId() }}}"/>
            <input type="hidden" name="@token()" value="1"/>
        @show
        @yield('edit-hidden-fields')
    </div>
</form>

@yield('edit-page-bottom')
