<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Extension.joomla
 *
 * @copyright   (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Extension\Joomlaupdate\Extension;

use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The extension plugin for com_joomlaupdate
 *
 * @since   5.3.0
 */
final class Joomlaupdate extends CMSPlugin implements SubscriberInterface
{
    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     *
     * @since   5.3.0
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return array
     *
     * @since   5.3.0
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onExtensionAfterSave' => 'onExtensionAfterSave',
        ];
    }

    /**
     * After update of an extension
     *
     * @param   AfterSaveEvent $event  Event instance.
     *
     * @return  void
     *
     * @since   5.3.0
     */
    public function onExtensionAfterSave(AfterSaveEvent $event): void
    {
        $context = $event->getContext();
        $item    = $event->getItem();

        if ($context !== 'com_config.component') {
            return;
        }

        if ($item->element !== 'com_joomlaupdate') {
            return;
        }

        /** @var UpdateModel $updateModel */
        $updateModel = $this->getApplication()->bootComponent('com_joomlaupdate')
            ->getMVCFactory()->createModel('Update', 'Administrator', ['ignore_request' => true]);

        if (!$updateModel) {
            return;
        }

        $params = new Registry($item->params);

        // Apply updated config
        $updateModel->applyUpdateSite(
            $params->get('updatesource'),
            $params->get('customurl'),
        );
    }
}
