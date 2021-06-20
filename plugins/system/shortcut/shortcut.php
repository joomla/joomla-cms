<?php
/**
 * @package     Joomla.Plugins
 * @subpackage  System.shortcut
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
	 
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
                $wa->registerScript('shortcut', $this->_basePath . '/js/shortcut.js', [], ['defer' => true]);
            }
            $wa->useScript('shortcut');
            return true;
        }
        return true;
    }
}
