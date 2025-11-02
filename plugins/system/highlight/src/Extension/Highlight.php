<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.highlight
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Highlight\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Event\Application\AfterDispatchEvent;
use Joomla\CMS\Event\Finder\ResultEvent;
use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to highlight terms.
 *
 * @since  2.5
 */
final class Highlight extends CMSPlugin implements SubscriberInterface
{
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
            'onAfterDispatch' => 'onAfterDispatch',
            'onFinderResult'  => 'onFinderResult',
        ];
    }

    /**
     * Method to catch the onAfterDispatch event.
     *
     * This is where we setup the click-through content highlighting for.
     * The highlighting is done with JavaScript so we just
     * need to check a few parameters and the JHtml behavior will do the rest.
     *
     * @param  AfterDispatchEvent $event  The event object
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onAfterDispatch(AfterDispatchEvent $event): void
    {
        // Check that we are in the site application.
        if (!$event->getApplication()->isClient('site')) {
            return;
        }

        // Set the variables.
        $input     = $event->getApplication()->getInput();
        $extension = $input->get('option', '', 'cmd');

        // Check if the highlighter is enabled.
        if (!ComponentHelper::getParams($extension)->get('highlight_terms', 1)) {
            return;
        }

        // Check if the highlighter should be activated in this environment.
        if ($input->get('tmpl', '', 'cmd') === 'component' || $event->getApplication()->getDocument()->getType() !== 'html') {
            return;
        }

        // Get the terms to highlight from the request.
        $terms = $input->request->get('highlight', null, 'base64');
        $terms = $terms ? json_decode(base64_decode($terms)) : null;

        // Check the terms.
        if (empty($terms)) {
            return;
        }

        // Clean the terms array.
        $filter     = InputFilter::getInstance();

        $cleanTerms = [];

        foreach ($terms as $term) {
            $cleanTerms[] = htmlspecialchars($filter->clean($term, 'string'));
        }

        /** @var \Joomla\CMS\Document\HtmlDocument $doc */
        $doc = $event->getApplication()->getDocument();

        // Activate the highlighter.
        if (!empty($cleanTerms)) {
            $doc->getWebAssetManager()->useScript('highlight');
            $doc->addScriptOptions(
                'highlight',
                [[
                    'class'     => 'js-highlight',
                    'highLight' => $cleanTerms,
                ]]
            );
        }

        // Adjust the component buffer.
        $buf = $doc->getBuffer('component');
        $buf = '<div class="js-highlight">' . $buf . '</div>';
        $doc->setBuffer($buf, 'component');
    }

    /**
     * Method to catch the onFinderResult event.
     *
     * @param  ResultEvent $event  The event object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public function onFinderResult(ResultEvent $event): void
    {
        static $params;

        if (\is_null($params)) {
            $params = ComponentHelper::getParams('com_finder');
        }

        $item  = $event->getItem();
        $query = $event->getQuery();

        // Get the route with highlighting information.
        if (
            !empty($query->highlight)
            && empty($item->mime)
            && $params->get('highlight_terms', 1)
        ) {
            $uri = new Uri($item->route);
            $uri->setVar('highlight', base64_encode(json_encode(\array_slice($query->highlight, 0, 10))));
            $item->route = $uri->toString();
        }
    }
}
