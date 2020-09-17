<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var  \Akeeba\Backup\Admin\View\Manage\Html $this */

// Make sure we only ever add this HTML and JS once per page
if (defined('AKEEBA_VIEW_JAVASCRIPT_HOWTORESTORE'))
{
	return;
}

define('AKEEBA_VIEW_JAVASCRIPT_HOWTORESTORE', 1);

$this->container->platform->getDocument()->addScriptOptions('akeeba.Manage.ShowHowToRestoreModal', 1);

?>
<div id="akeeba-config-howtorestore-bubble">
    <div class="akeeba-renderer-fef">
        <h4>@lang('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_LEGEND')</h4>
        <p>
            @sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_' . (AKEEBA_PRO ? 'PRO' : 'CORE'), 'http://akee.ba/abrestoreanywhere', 'index.php?option=com_akeeba&view=Transfer', 'https://www.akeeba.com/latest-kickstart-core.zip')
        </p>
        <p>
            @if (!AKEEBA_PRO)
                @sprintf('COM_AKEEBA_BUADMIN_LABEL_HOWDOIRESTORE_TEXT_CORE_INFO_ABOUT_PRO', 'https://www.akeeba.com/products/akeeba-backup.html')
            @endif
        </p>

        <div>
            <a class="akeeba-btn--primary" id="comAkeebaManageCloseHowToRestoreModal">
                <span class="akion-close"></span>
                @lang('COM_AKEEBA_BUADMIN_BTN_REMINDME')
            </a>
            <a href="index.php?option=com_akeeba&view=Manage&task=hidemodal" class="akeeba-btn--green">
                <span class="akion-checkmark-circled"></span>
                @lang('COM_AKEEBA_BUADMIN_BTN_DONTSHOWTHISAGAIN')
            </a>
        </div>
    </div>
</div>
