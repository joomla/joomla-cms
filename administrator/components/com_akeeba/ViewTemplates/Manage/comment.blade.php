<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html  $this */

?>
<form name="adminForm" id="adminForm" action="index.php" method="post" class="akeeba-form--horizontal">
	<div class="akeeba-form-group">
		<label for="description">
			@lang('COM_AKEEBA_BUADMIN_LABEL_DESCRIPTION')
		</label>
        <input type="text" name="description" id="description" maxlength="255" size="50"
               value="{{{ $this->record['description'] }}}" />
	</div>

	<div class="akeeba-form-group">
		<label for="comment">
			@lang('COM_AKEEBA_BUADMIN_LABEL_COMMENT')
		</label>
        @editor('comment',  $this->record['comment'], '100%', '400', '60', '20', array())
	</div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba" />
        <input type="hidden" name="task" value="" />
        <input type="hidden" name="view" value="Manage" />
        <input type="hidden" name="id" value="{{ (int)$this->record['id'] }}" />
        <input type="hidden" name="@token(true)" value="1" />
    </div>
</form>
