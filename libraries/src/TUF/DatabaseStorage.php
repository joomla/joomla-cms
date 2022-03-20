<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\TUF;

use Joomla\CMS\Table\Table;

\defined('JPATH_PLATFORM') or die;

/**
 * @since  VERSION
 */
class DatabaseStorage implements \ArrayAccess
{
	private Table $table;

	public function __construct()
	{
		$this->table = new \Joomla\CMS\Table\Tuf();
	}

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset)
    {
        return file_exists($this->pathWithBasePath($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset)
    {
		// return $this->table->load('');
        return file_get_contents($this->pathWithBasePath($offset));
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value)
    {
		// return $this->table->store('');
        file_put_contents($this->pathWithBasePath($offset), $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset)
    {
		// return $this->table->delete('');
        @unlink($this->pathWithBasePath($offset));
    }
}
