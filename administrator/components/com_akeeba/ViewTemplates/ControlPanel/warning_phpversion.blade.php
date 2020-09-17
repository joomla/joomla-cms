<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

use FOF30\Date\Date;

?>
{{-- Old PHP version reminder --}}
@include('admin:com_akeeba/CommonTemplates/phpversion_warning', [
    'softwareName'  => 'Akeeba Backup',
    'minPHPVersion' => '7.1.0',
])
