<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.Timeestimation
 *
 * @copyright   (C) 2026 Joomla! Project. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\Content\Timeestimation\Extension;

use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\CMS\Event\Model\PrepareFormEvent;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Time Estimation Plugin
 *
 * Uses TWO events:
 *
 *  1. onContentPrepare   – runs first, counts words while $article->text is
 *                          still raw HTML, stores the result on the article object.
 *
 *  2. onContentAfterTitle – runs later, reads the pre-computed values and returns
 *                           the badge HTML as the event result.  Joomla collects
 *                           that string into $this->item->event->afterDisplayTitle,
 *                           which default.php echoes directly below the title:
 *
 *                           <?php echo $this->item->event->afterDisplayTitle; ?>
 *
 */
class Timeestimation extends CMSPlugin implements SubscriberInterface
{
    /**
     * Auto-load the plugin language file.
     *
     * @var boolean
     */


    /**
     * Fallback WPM when the admin param is absent or invalid.
     *
     * @var integer
     */
    private const DEFAULT_WPM = 50;

    /**
     * Map Joomla events → listener methods.
     *
     * @return  array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // Step 1: count words while the raw text is available.
            'onContentPrepare'    => 'prepare',

            // Step 2: return the badge into the afterDisplayTitle template slot.
            'onContentAfterTitle' => 'displayBadge',
        ];
    }

    // -------------------------------------------------------------------------
    // Step 1 — onContentPrepare
    // -------------------------------------------------------------------------

    /**
     * Count words and store reading-time data on the article object.
     *
     * We do the heavy work here because $article->text is fully available
     * during onContentPrepare. By the time onContentAfterTitle fires, Joomla
     * may have already processed the text further.
     *
     * @param   ContentPrepareEvent  $event
     *
     * @return  void
     */
    public function prepare(ContentPrepareEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();

        if (!\in_array($context, ['com_content.article', 'com_content.category', 'com_content.featured'])) {
            return;
        }

        if (empty($article->text)) {
            return;
        }

        $wpm = (int) $this->params->get('words_per_minute', self::DEFAULT_WPM);

        if ($wpm <= 0) {
            $wpm = self::DEFAULT_WPM;
        }

        // Store on the article so displayBadge() can read it without re-counting.
        $article->readingTimeWords   = $this->countWords($article->text);
        $article->readingTimeMinutes = max(1, (int) ceil($article->readingTimeWords / $wpm));
    }

    // -------------------------------------------------------------------------
    // Step 2 — onContentAfterTitle
    // -------------------------------------------------------------------------

    /**
     * Return the badge HTML into the afterDisplayTitle template slot.
     *
     * Joomla accumulates every plugin's return value for this event and stores
     * the combined string in $this->item->event->afterDisplayTitle.
     * default.php then echoes it with:
     *
     *     <?php echo $this->item->event->afterDisplayTitle; ?>
     *
     *
     * @param   AfterTitleEvent  $event
     *
     * @return  void
     */
    public function displayBadge(AfterTitleEvent $event): void
    {
        $context = $event->getContext();
        $article = $event->getItem();

        // Only show on single article view — not in category/blog list cards.
        if ($context !== 'com_content.article') {
            return;
        }

        // Safety check: prepare() may have been skipped (e.g. empty article).
        if (!isset($article->readingTimeMinutes)) {
            return;
        }

        // Return the badge into the event result.
        // Joomla reads this and writes it into $article->event->afterDisplayTitle.
        $event->addResult($this->buildBadge($article->readingTimeMinutes, $article->readingTimeWords));
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Count visible words in an HTML string.
     *
     * @param   string  $html
     *
     * @return  integer
     */
    private function countWords(string $html): int
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\s+/', ' ', trim($text));

        return $text === '' ? 0 : str_word_count($text);
    }

    /**
     * Build the reading-time HTML badge.
     *
     * @param   integer  $minutes
     * @param   integer  $wordCount
     *
     * @return  string
     */
    private function buildBadge(int $minutes, int $wordCount): string
    {
        $minLabel = $minutes === 1 ? 'min read' : 'mins read';

        return '<div class="reading-time-badge">'
             . '<span>&#128337; <strong>' . $minutes . '</strong> ' . $minLabel . '</span>'
             . '<span> (' . $wordCount . ' words)</span>'
             . '</div>';
    }
}
