<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') || die();

/** @var \FOF30\View\DataView\Html $this */

// Make sure we only ever add this HTML and JS once per page
if (defined('AKEEBA_VIEW_JAVASCRIPT_CONFWIZ_MODAL'))
{
	return;
}

define('AKEEBA_VIEW_JAVASCRIPT_CONFWIZ_MODAL', 1);

$js = <<< JS
akeeba.Loader.add('akeeba.System', function(){
    akeeba.System.documentReady(function(){
        akeeba.System.addEventListener('comAkeebaConfigurationWizardModalClose', 'click', function() {
          akeeba.System.configurationWizardModal.close();
        });

        setTimeout(function() {
          akeeba.System.configurationWizardModal = akeeba.Modal.open({
            inherit: '#akeeba-config-confwiz-bubble',
            width: '80%'
        });
        }, 500);
    });
});

JS;

$this->container->template->addJSInline($js);
?>

<div id="akeeba-config-confwiz-bubble" class="modal fade" role="dialog"
     aria-labelledby="DialogLabel" aria-hidden="true" style="display: none;">
    <div class="akeeba-renderer-fef">
        <h4>
            @lang('COM_AKEEBA_CONFIG_HEADER_CONFWIZ')
        </h4>
        <div>
            <p>
                @lang('COM_AKEEBA_CONFIG_LBL_CONFWIZ_INTRO')
            </p>
            <p>
                <a href="index.php?option=com_akeeba&view=ConfigurationWizard"
                   class="akeeba-btn--green akeeba-btn--big">
                    <span class="akion-flash"></span>
                    @lang('COM_AKEEBA_CONFWIZ')
                </a>
            </p>
            <p>
                @lang('COM_AKEEBA_CONFIG_LBL_CONFWIZ_AFTER')
            </p>
        </div>
        <div>
            <a href="#" class="akeeba-btn--ghost akeeba-btn--small" id="comAkeebaConfigurationWizardModalClose"
               onclick="akeeba.System.configurationWizardModal.close();">
                <span class="akion-close"></span>
                @lang('JCANCEL')
            </a>
        </div>
    </div>
</div>
