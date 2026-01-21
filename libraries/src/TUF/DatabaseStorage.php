<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Tuf\Metadata\StorageBase;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects;

/**
 * @since  5.1.0
 *
 * @internal Currently this class is only used for Joomla! updates and will be extended in the future to support 3rd party updates
 *           Don't extend this class in your own code, it is subject to change without notice.
 */
class DatabaseStorage extends StorageBase
{
    public const METADATA_COLUMNS = ['root', 'targets', 'snapshot', 'timestamp', 'mirrors'];

    /**
     * The TUF table object
     *
     * @var Table
     */
    protected $table;

    protected $container = [];

    /**
     * Initialize the DatabaseStorage class
     *
     * @param TableInterface $table The table object that represents the metadata row
     */
    public function __construct(TableInterface $table)
    {
        $this->table = $table;

        foreach (self::METADATA_COLUMNS as $column) {
            if ($this->table->$column === null) {
                continue;
            }

            $this->write($column, $this->table->$column);
        }
    }


    public function read(string $name): ?string
    {
        return $this->container[$name] ?? null;
    }

    public function write(string $name, string $data): void
    {
        $this->container[$name] = $data;
    }

    public function delete(string $name): void
    {
        unset($this->container[$name]);
    }

    public function persist(): bool
    {
        $data = [];

        foreach (self::METADATA_COLUMNS as $column) {
            if (!\array_key_exists($column, $this->container)) {
                continue;
            }

            $data[$column] = $this->container[$column];
        }

        return $this->table->save($data);
    }
}
