<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('_JEXEC') || die();

?>
<form action="index.php" method="post" name="adminForm" id="adminForm"
      class="akeeba-form--horizontal--with-hidden akeeba-panel--information">
    <div class="akeeba-form-group">
    </div>

    <div class="akeeba-form-group">
        <label for="description">
            @jhtml('tooltip', \Joomla\CMS\Language\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION_TOOLTIP'), '', '', \Joomla\CMS\Language\Text::_('COM_AKEEBA_PROFILES_LABEL_DESCRIPTION'))
        </label>
        <input type="text" name="description" class="span6" id="description" value="{{{ $this->item->description }}}" />
    </div>

    <div class="akeeba-hidden-fields-container">
        <input type="hidden" name="option" value="com_akeeba" />
        <input type="hidden" name="view" value="Profiles" />
        <input type="hidden" name="boxchecked" id="boxchecked" value="0" />
        <input type="hidden" name="task" id="task" value="save" />
        <input type="hidden" name="id" id="id" value="{{ (int)$this->item->id }}" />
        <input type="hidden" name="@token(true)" value="1" />
    </div>
</form>
