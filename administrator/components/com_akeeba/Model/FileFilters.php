<?php
/**
 * @package   akeebabackup
 * @copyright Copyright (c)2006-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Backup\Admin\Model;

// Protect from unauthorized access
defined('_JEXEC') || die();

use Akeeba\Backup\Admin\Model\Mixin\ExclusionFilter;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use FOF30\Container\Container;
use FOF30\Model\Model;

/**
 * File Filters model
 *
 * Handles the exclusion of files and directories
 */
class FileFilters extends Model
{
	use ExclusionFilter;

	public function __construct(Container $container, array $config)
	{
		parent::__construct($container, $config);

		$this->knownFilterTypes = ['directories', 'files', 'skipdirs', 'skipfiles'];
	}

	/**
	 * Returns a listing of contained directories and files, as well as their exclusion status
	 *
	 * @param   string  $root  The root directory
	 * @param   string  $node  The subdirectory to scan
	 *
	 * @return  array
	 */
	private function &get_listing($root, $node)
	{
		// Initialize the absolute directory root
		$directory = substr($root, 0);

		// Replace stock directory tags, like [SITEROOT]
		$stock_dirs = Platform::getInstance()->get_stock_directories();

		if (!empty($stock_dirs))
		{
			foreach ($stock_dirs as $key => $replacement)
			{
				$directory = str_replace($key, $replacement, $directory);
			}
		}

		$directory = Factory::getFilesystemTools()->TranslateWinPath($directory);

		// Clean and add the node
		$node = Factory::getFilesystemTools()->TranslateWinPath($node);

		// Just a directory separator is treated as no directory at all
		if (($node == '/'))
		{
			$node = '';
		}

		// Trim leading and trailing slashes
		$node = trim($node, '/');

		// Add node to directory
		if (!empty($node))
		{
			$directory .= '/' . $node;
		}

		// Add any required trailing slash to the node to be used below
		if (!empty($node))
		{
			$node .= '/';
		}

		// Get a filters instance
		$filters = Factory::getFilters();

		// Get a listing of folders and process it
		$folders = Factory::getFileLister()->getFolders($directory);
		$folders_out = array();

		if (!empty($folders))
		{
			asort($folders);

			foreach ($folders as $folder)
			{
				$folder = Factory::getFilesystemTools()->TranslateWinPath($folder);

				// Filter out files whose names result to an empty JSON representation
				$json_folder = json_encode($folder);
				$folder      = json_decode($json_folder);

				if (empty($folder))
				{
					continue;
				}

				$test   = $node . $folder;
				$status = array();

				// Check dir/all filter (exclude)
				$result                = $filters->isFilteredExtended($test, $root, 'dir', 'all', $byFilter);
				$status['directories'] = (!$result) ? 0 : (($byFilter == 'directories') ? 1 : 2);

				// Check dir/content filter (skip_files)
				$result              = $filters->isFilteredExtended($test, $root, 'dir', 'content', $byFilter);
				$status['skipfiles'] = (!$result) ? 0 : (($byFilter == 'skipfiles') ? 1 : 2);

				// Check dir/children filter (skip_dirs)
				$result             = $filters->isFilteredExtended($test, $root, 'dir', 'children', $byFilter);
				$status['skipdirs'] = (!$result) ? 0 : (($byFilter == 'skipdirs') ? 1 : 2);

				// Add to output array
				$folders_out[ $folder ] = $status;
			}
		}

		unset($folders);
		$folders = $folders_out;

		// Get a listing of files and process it
		$files = Factory::getFileLister()->getFiles($directory);
		$files_out = array();

		if (!empty($files))
		{
			asort($files);

			foreach ($files as $file)
			{
				// Filter out files whose names result to an empty JSON representation
				$json_file = json_encode($file);
				$file      = json_decode($json_file);

				if (empty($file))
				{
					continue;
				}

				$test   = $node . $file;
				$status = [];

				// Check file/all filter (exclude)
				$result          = $filters->isFilteredExtended($test, $root, 'file', 'all', $byFilter);
				$status['files'] = (!$result) ? 0 : (($byFilter == 'files') ? 1 : 2);
				$status['size']  = $this->formatSize(@filesize($directory . '/' . $file), 1);

				// Add to output array
				$files_out[$file] = $status;
			}
		}

		unset($files);
		$files = $files_out;

		// Return a compiled array
		$retarray = array(
			'folders' => $folders,
			'files'   => $files
		);

		return $retarray;

		/* Return array format
		 * [array] :
		 * 		'folders' [array] :
		 * 			(folder_name) => [array]:
		 *				'directories'	=> 0|1|2
		 *				'skipfiles'		=> 0|1|2
		 *				'skipdirs'		=> 0|1|2
		 *		'files' [array] :
		 *			(file_name) => [array]:
		 *				'files'			=> 0|1|2
		 *
		 * Legend:
		 * 0 -> Not excluded
		 * 1 -> Excluded by the direct filter
		 * 2 -> Excluded by another filter (regex, api, an unknown plugin filter...)
		 */
	}

	/**
	 * Glues the current directory crumbs and the child directory into a node string
	 *
	 * @param   array|string  $crumbs  Breadcrumbs in array or JSON encoded array format
	 * @param   string        $child   The child folder (relative to the root defined by crumbs)
	 *
	 * @return  string  The absolute node (path) of the $child
	 */
	private function glue_crumbs(&$crumbs, $child)
	{
		// Construct the full node
		$node = '';

		// Some servers do not decode the crumbs. I don't know why!
		if (!is_array($crumbs) && (substr($crumbs, 0, 1) == '['))
		{
			$crumbs = @json_decode($crumbs);

			if ($crumbs === false)
			{
				$crumbs = array();
			}
		}

		if (!is_array($crumbs))
		{
			$crumbs = array();
		}

		array_walk($crumbs, function ($value, $index) {
			if (in_array(trim($value), array('.', '..')))
			{
				throw new \InvalidArgumentException("Unacceptable folder crumbs");
			}
		});

		if ((stristr($child, '/..') !== false) || (stristr($child, '\..') !== false))
		{
			throw new \InvalidArgumentException("Unacceptable child folder");
		}

		if (!empty($crumbs))
		{
			$node = implode('/', $crumbs);
		}

		if (!empty($node))
		{
			$node .= '/';
		}

		if (!empty($child))
		{
			$node .= $child;
		}

		return $node;
	}

	/**
	 * Returns an array with the listing and filter status of a directory
	 *
	 * @param   string        $root    Root directory
	 * @param   array|string  $crumbs  Breadcrumbs in array or JSON encoded array format, defining the parent directory
	 * @param   string        $child   The child directory we want to scan
	 *
	 * @return array
	 */
	public function make_listing($root, $crumbs = [], $child = '')
	{
		// Construct the full node
		$node = $this->glue_crumbs($crumbs, $child);

		// Create the new crumbs
		if (!is_array($crumbs))
		{
			$crumbs = array();
		}

		if (!empty($child))
		{
			$crumbs[] = $child;
		}

		// Get listing with the filter info
		$listing = $this->get_listing($root, $node);

		// Assemble the array
		$listing['root']   = $root;
		$listing['crumbs'] = $crumbs;

		return $listing;
	}

	/**
	 * Toggle a filter
	 *
	 * @param   string  $root    Root directory
	 * @param   array   $crumbs  Components of the current directory relative to the root
	 * @param   string  $item    The child item of the current directory we want to toggle the filter for
	 * @param   string  $filter  The name of the filter to apply (directories, skipfiles, skipdirs, files)
	 *
	 * @return  array
	 */
	public function toggle($root, $crumbs, $item, $filter)
	{
		$node = $this->glue_crumbs($crumbs, $item);

		return $this->applyExclusionFilter($filter, $root, $node, 'toggle');
	}

	/**
	 * Set a filter
	 *
	 * @param   string  $root    Root directory
	 * @param   array   $crumbs  Components of the current directory relative to the root
	 * @param   string  $item    The child item of the current directory we want to set the filter for
	 * @param   string  $filter  The name of the filter to apply (directories, skipfiles, skipdirs, files)
	 *
	 * @return  array
	 */
	public function setFilter($root, $crumbs, $item, $filter)
	{
		$node = $this->glue_crumbs($crumbs, $item);

		return $this->applyExclusionFilter($filter, $root, $node, 'set');
	}

	/**
	 * Remove a filter
	 *
	 * @param   string  $root    Root directory
	 * @param   array   $crumbs  Components of the current directory relative to the root
	 * @param   string  $item    The child item of the current directory we want to remove the filter for
	 * @param   string  $filter  The name of the filter to apply (directories, skipfiles, skipdirs, files)
	 *
	 * @return  array
	 */
	public function remove($root, $crumbs, $item, $filter)
	{
		$node = $this->glue_crumbs($crumbs, $item);

		return $this->applyExclusionFilter($filter, $root, $node, 'remove');
	}

	/**
	 * Swap a filter
	 *
	 * @param   string  $root    Root directory
	 * @param   array   $crumbs  Components of the current directory relative to the root
	 * @param   string  $item    The child item of the current directory we want to set the filter for
	 * @param   string  $filter  The name of the filter to apply (directories, skipfiles, skipdirs, files)
	 *
	 * @return  array
	 */
	public function swap($root, $crumbs, $old_item, $new_item, $filter)
	{
		$new_node = $this->glue_crumbs($crumbs, $new_item);
		$old_node = $this->glue_crumbs($crumbs, $old_item);

		return $this->applyExclusionFilter($filter, $root, $new_node, 'swap', $old_node);
	}

	/**
	 * Retrieves the filters as an array. Used for the tabular filter editor.
	 *
	 * @param   string  $root  The root node to search filters on
	 *
	 * @return  array  A collection of hash arrays containing node and type for each filtered element
	 */
	public function &get_filters($root)
	{
		return $this->getTabularFilters($root);
	}

	/**
	 * Resets the filters
	 *
	 * @param   string  $root  Root directory
	 *
	 * @return  array
	 */
	public function resetFilters($root)
	{
		$this->resetAllFilters($root);

		return $this->make_listing($root);
	}

	/**
	 * Handles a request coming in through AJAX. Basically, this is a simple proxy to the model methods.
	 *
	 * @return  array
	 */
	public function doAjax()
	{
		$action = $this->getState('action');
		$verb   = array_key_exists('verb', get_object_vars($action)) ? $action->verb : null;

		if (!array_key_exists('crumbs', get_object_vars($action)))
		{
			$action->crumbs = '';
		}

		$ret_array = array();

		switch ($verb)
		{
			// Return a listing for the normal view
			case 'list':
				$ret_array = $this->make_listing($action->root, $action->crumbs, $action->node);
				break;

			// Toggle a filter's state
			case 'toggle':
				$ret_array = $this->toggle($action->root, $action->crumbs, $action->node, $action->filter);
				break;

			// Set a filter (used by the editor)
			case 'set':
				$ret_array = $this->setFilter($action->root, $action->crumbs, $action->node, $action->filter);
				break;

			// Swap a filter (used by the editor)
			case 'swap':
				$ret_array =
					$this->swap($action->root, $action->crumbs, $action->old_node, $action->new_node, $action->filter);
				break;

			case 'tab':
				$ret_array = $this->get_filters($action->root);
				break;

			// Reset filters
			case 'reset':
				$ret_array = $this->resetFilters($action->root);
				break;
		}

		return $ret_array;
	}

	/**
	 * Format the size of the file (given in bytes) to something human readable, e.g. 123 MB
	 *
	 * @param   int  $bytes     The file size in bytes
	 * @param   int  $decimals  How many decimals you want (default: 0)
	 *
	 * @return  string  The human-readable, formatted size
	 */
	private function formatSize($bytes, $decimals = 0)
	{
		$bytes  = empty($bytes) ? 0 : (int) $bytes;
		$format = empty($decimals) ? '%0u' : '%0.' . $decimals . 'f';

		$uom = [
			'TB' => 1048576 * 1048576,
			'GB' => 1024 * 1048576,
			'MB' => 1048576,
			'KB' => 1024,
			'B'  => 1,
		];

		// Whole bytes cannot have decimal positions
		if (!empty($decimals))
		{
			unset($uom['B']);
		}

		foreach ($uom as $unit => $byteSize)
		{
			if (floatval($bytes) >= $byteSize)
			{
				return sprintf($format, $bytes / $byteSize) . ' ' . $unit;
			}
		}

		// If the number is either too big or too small,
		return sprintf('%0u B', $bytes);
	}

}
