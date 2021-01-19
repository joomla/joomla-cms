<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<section class="akeeba-panel--primary">

    <header class="akeeba-block-header">
        <h3>@lang('COM_AKEEBA_CPANEL_HEADER_QUICKBACKUP')</h3>
    </header>

    <div class=" akeeba-grid">
	    @foreach($this->quickIconProfiles as $qiProfile)
            <a class="akeeba-action--green"
               href="index.php?option=com_akeeba&view=Backup&autostart=1&profileid={{ (int) $qiProfile->id }}&@token(true)=1">
                <span class="akion-play"></span>
                <span>{{{ $qiProfile->description }}}</span>
            </a>
	    @endforeach
    </div>

</section>
