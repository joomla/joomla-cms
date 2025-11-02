<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\View;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Dynamically modify the frontend template when showing a MFA captive page.
 *
 * @since 4.2.0
 */
trait SiteTemplateTrait
{
    /**
     * Set a specific site template style in the frontend application
     *
     * @return  void
     * @throws  \Exception
     * @since   4.2.0
     */
    private function setSiteTemplateStyle(): void
    {
        $app           = Factory::getApplication();
        $templateStyle = (int) ComponentHelper::getParams('com_users')->get('captive_template', '');

        if (empty($templateStyle) || !$app->isClient('site')) {
            return;
        }

        $itemId = $app->getInput()->get('Itemid');

        if (!empty($itemId)) {
            return;
        }

        $app->getInput()->set('templateStyle', $templateStyle);

        try {
            $refApp      = new \ReflectionObject($app);
            $refTemplate = $refApp->getProperty('template');
            $refTemplate->setAccessible(true);
            $refTemplate->setValue($app, null);
        } catch (\ReflectionException) {
            return;
        }

        $template = $app->getTemplate(true);

        $app->set('theme', $template->template);
        $app->set('themeParams', $template->params);
    }
}
