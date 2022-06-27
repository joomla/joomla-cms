<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Task;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * The TaskOption class is used as a utility container for available plugin-provided task routines.
 * Each task-supporting plugin calls the {@see TaskOptions::addOptions()} method with an array of TaskOption constructor
 * argument pairs as argument. Internally, the TaskOption object generates the routine title and description from the
 * language constant prefix.
 *
 * @since  4.1.0
 *
 * @property-read  string $desc             The routine description.
 * @property-read  string $id               The routine ID.
 * @property-read  string $langConstPrefix  The routine's language constant prefix.
 * @property-read  string $title            The routine title.
 */
class TaskOption
{
    /**
     * Task routine title
     *
     * @var string
     * @since  4.1.0
     */
    protected $title;

    /**
     * Task routine description.
     *
     * @var string
     * @since  4.1.0
     */
    protected $desc;

    /**
     * Routine type-ID.
     *
     * @var string
     * @since  4.1.0
     */
    protected $id;

    /**
     * @var string
     * @since  4.1.0
     */
    protected $langConstPrefix;

    /**
     * TaskOption constructor.
     *
     * @param   string  $type             A unique ID string for a plugin task routine.
     * @param   string  $langConstPrefix  The Language constant prefix $p. Expects $p . _TITLE and $p . _DESC to exist.
     *
     * @since  4.1.0
     */
    public function __construct(string $type, string $langConstPrefix)
    {
        $this->id              = $type;
        $this->title           = Text::_("${langConstPrefix}_TITLE");
        $this->desc            = Text::_("${langConstPrefix}_DESC");
        $this->langConstPrefix = $langConstPrefix;
    }

    /**
     * Magic method to allow read-only access to private properties.
     *
     * @param   string  $name  The object property requested.
     *
     * @return  ?string
     *
     * @since  4.1.0
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        // Trigger a deprecation for the 'type' property (replaced with {@see id}).
        if ($name === 'type') {
            try {
                Log::add(
                    sprintf(
                        'The %1$s property is deprecated. Use %2$s instead.',
                        $name,
                        'id'
                    ),
                    Log::WARNING,
                    'deprecated'
                );
            } catch (\RuntimeException $e) {
                // Pass
            }

            return $this->id;
        }

        return null;
    }
}
