<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_cpanel
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Registry\Registry;

/** @var \Joomla\Component\Cpanel\Administrator\View\Cpanel\HtmlView $this */

// Load JavaScript message titles
Text::script('ERROR');
Text::script('WARNING');
Text::script('NOTICE');
Text::script('MESSAGE');

Text::script('COM_CPANEL_UNPUBLISH_MODULE_SUCCESS');
Text::script('COM_CPANEL_UNPUBLISH_MODULE_ERROR');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('com_cpanel.admin-cpanel')
    ->useScript('joomla.dialog-autocreate');

$user = $this->getCurrentUser();

// Set up the modal options that will be used for module editor
$popupOptions = [
    'popupType'  => 'iframe',
    'src'        => Route::_('index.php?option=com_cpanel&task=addModule&position=' . $this->position, false),
    'textHeader' => Text::_('COM_CPANEL_ADD_MODULE_MODAL_TITLE'),
];
?>
<div id="cpanel-modules">
    <div class="cpanel-modules <?php echo $this->position; ?>">
        <div class="card-columns">
        <?php if ($this->quickicons) :
            foreach ($this->quickicons as $iconmodule) {
                $modParams = new Registry($iconmodule->params);

                echo ModuleHelper::renderModule($iconmodule, [
                   'style' => 'well',
                   'class' => 'quickicons-for-' . $modParams->get('context', ''),
                ]);
            }
        endif;
        foreach ($this->modules as $module) {
            echo ModuleHelper::renderModule($module, ['style' => 'well']);
        }
        ?>
        <?php if ($user->authorise('core.admin', 'com_modules') && $user->authorise('core.create', 'com_modules')) : ?>
            <div class="module-wrapper">
                <div class="card">
                    <button type="button" class="cpanel-add-module"
                            data-joomla-dialog="<?php echo htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES)) ?>"
                            data-close-on-message data-reload-on-close>
                        <div class="cpanel-add-module-icon">
                            <span class="icon-plus-square" aria-hidden="true"></span>
                        </div>
                        <span><?php echo Text::_('COM_CPANEL_ADD_DASHBOARD_MODULE'); ?></span>
                    </button>
                </div>
            </div>
        <?php endif; ?>
        </div>
    </div>
</div>
