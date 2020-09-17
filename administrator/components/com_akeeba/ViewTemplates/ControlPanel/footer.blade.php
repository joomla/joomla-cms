<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/** @var $this \Akeeba\Backup\Admin\View\ControlPanel\Html */

// Protect from unauthorized access
defined('_JEXEC') || die();

?>
<div class="row-fluid footer akeebabackup-footer">
	<div class="span12">
		<p style="height: 6em">
			@sprintf('Copyright &copy;2006-%s <a href="https://www.akeeba.com">Akeeba Ltd</a>. All Rights Reserved.', date('Y'))
			<br/>
			Akeeba Backup is Free Software and is distributed under the terms of the <a
					href="http://www.gnu.org/licenses/gpl-3.0.html">GNU General Public License</a>, version 3 or - at
			your option - any later version.
			@if(AKEEBA_PRO != 1)
				<br/>If you use Akeeba Backup Core, please post a rating and a review at the <a
						href="https://extensions.joomla.org/extensions/extension/access-a-security/site-security/akeeba-backup/">Joomla!
					Extensions Directory</a>.
			@endif
		</p>
	</div>
</div>
