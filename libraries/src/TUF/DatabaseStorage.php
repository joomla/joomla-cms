<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\Tuf;
use Joomla\Database\DatabaseDriver;
use Tuf\Metadata\StorageBase;

\defined('JPATH_PLATFORM') or die;

/**
 * @since  __DEPLOY_VERSION__
 */
class DatabaseStorage extends StorageBase
{
    /**
     * The Tuf table object
     *
     * @var Table
     */
    protected $table;

    /**
     * Initialize the DatabaseStorage class
     *
     * @param DatabaseDriver $db A database connector object
     * @param integer $extensionId The extension ID where the storage should be implemented for
     */
    public function __construct(DatabaseDriver $db, int $extensionId)
    {
        $this->table = new Tuf($db);

        $this->table->load(['extension_id' => $extensionId]);

        foreach (["root_json", "targets_json", "snapshot_json", "timestamp_json", "mirrors_json"] as $column) {
            if ($this->table->$column === null) {
                continue;
            }

            $this->write(explode("_", $column, 2)[0], $this->table->$column);
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
}
