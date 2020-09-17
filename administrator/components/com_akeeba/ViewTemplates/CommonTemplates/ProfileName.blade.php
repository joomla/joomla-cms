<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();
?>
<div class="akeeba-block--info">
	<strong>@lang('COM_AKEEBA_CPANEL_PROFILE_TITLE')</strong>:
	#{{{ (int)($this->profileId) }}} {{{ $this->profileName }}}
</div>
