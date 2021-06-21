<?php
// no direct access
defined( '_JEXEC' ) or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Language\Text;

/**
 * Skipto plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  __DEPLOY_VERSION__
 */

class PlgSystemShortcut extends CMSPlugin
{

    protected $app;
    protected $_basePath = 'media/plg_system_shortcut';
    public function onBeforeCompileHead()
    {
        if ($this->app->isClient('administrator'))
        {
            $wa = $this->app->getDocument()->getWebAssetManager();

            if (!$wa->assetExists('script', 'shortcut'))
            {
                $wa->registerScript('shortcut', $this->_basePath . '/js/shortcut.js', [], ['defer' => true , 'type' => 'module']);
            }
            $wa->useScript('shortcut');
            return true;
        }
        return true;
    }
}
