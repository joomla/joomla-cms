<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright   (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\Event;

\defined('_JEXEC') or die;

use BadMethodCallException;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Event\AbstractImmutableEvent;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Event object for fetch media file.
 *
 * @since  __DEPLOY_VERSION__
 */
final class FetchMediaFileEvent extends AbstractImmutableEvent
{
	/**
	 * Constructor.
	 *
	 * @param   string  $name       The event name.
	 * @param   array   $arguments  The event arguments.
	 *
	 * @throws  BadMethodCallException
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function __construct($name, array $arguments = array())
	{
		parent::__construct($name, $arguments);

		// Check for required arguments
		if (!\array_key_exists('file', $arguments) || !is_object($arguments['file']))
		{
			throw new BadMethodCallException("Argument 'file' of event $name is not of the expected type");
		}
	}

	/**
	 * Validate $value to have all attributes with a valid type
	 *
	 * Validation based on \Joomla\Component\Media\Administrator\Adapter\AdapterInterface::getFile()
	 *
	 * Properties validated:
	 * - type:          The type can be file or dir
	 * - name:          The name of the file
	 * - path:          The relative path to the root
	 * - extension:     The file extension
	 * - size:          The size of the file
	 * - create_date:   The date created
	 * - modified_date: The date modified
	 * - mime_type:     The mime type
	 * - width:         The width, when available
	 * - height:        The height, when available
	 *
	 * Generation based on \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter::getPathInformation()
	 *
	 * Properties generated:
	 * - created_date_formatted:  DATE_FORMAT_LC5 formatted string based on create_date
	 * - modified_date_formatted: DATE_FORMAT_LC5 formatted string based on modified_date
	 *
	 * @param   \stdClass  $value  The value to set
	 *
	 * @return \stdClass
	 *
	 * @since   __DEPLOY_VERSION__
	 *
	 * @throws BadMethodCallException
	 */
	protected function setFile(\stdClass $value): \stdClass
	{
		// Make immutable object
		$value = clone $value;

		// Only "dir" or "file" is allowed
		if (!isset($value->type) || ($value->type !== 'dir' && $value->type !== 'file'))
		{
			throw new BadMethodCallException("Property 'type' of argument 'file' of event {$this->name} has a wrong value. Valid: 'dir' or 'file'");
		}

		// Non empty string
		if (empty($value->name) || !is_string($value->name))
		{
				throw new BadMethodCallException("Property 'name' of argument 'file' of event {$this->name} has a wrong value. Valid: non empty string");
		}

		if ($value->type === 'file'))
		{
		  // Non empty string
		  if (empty($value->path) || !is_string($value->path))
		  {
				  throw new BadMethodCallException("Property 'path' of argument 'file' of event {$this->name} has a wrong value. Valid: non empty string");
		  }
  
		  // A string
		  if (!isset($value->extension) || !is_string($value->extension))
		  {
				  throw new BadMethodCallException("Property 'extension' of argument 'file' of event {$this->name} has a wrong value. Valid: string");
		  }
		}

		// An empty string or an integer
		if (!isset($value->size) ||
			(!is_integer($value->size) && !is_string($value->size)) ||
			(is_string($value->size) && $value->size !== '')
		)
		{
				throw new BadMethodCallException("Property 'size' of argument 'file' of event {$this->name} has a wrong value. Valid: empty string or integer");
		}

		// A string
		if (!isset($value->mime_type) || !is_string($value->mime_type))
		{
				throw new BadMethodCallException("Property 'mime_type' of argument 'file' of event {$this->name} has a wrong value. Valid: string");
		}

		// An integer
		if (!isset($value->width) || !is_integer($value->width))
		{
				throw new BadMethodCallException("Property 'width' of argument 'file' of event {$this->name} has a wrong value. Valid: integer");
		}

		// An integer
		if (!isset($value->height) || !is_integer($value->height))
		{
				throw new BadMethodCallException("Property 'height' of argument 'file' of event {$this->name} has a wrong value. Valid: integer");
		}

		// A ISO 8601 date string
		if (empty($value->create_date)) {
				throw new BadMethodCallException("Property 'create_date' of argument 'file' of event {$this->name} has a wrong value. Valid: ISO 8601 date string");
		}

		// Validate date format
		$date = Date::createFromFormat(\DATE_ISO8601, $value->create_date);
		if (!$date) {
				throw new BadMethodCallException("Property 'create_date' of argument 'file' of event {$this->name} has a wrong value. Valid: ISO 8601 date string");
		}

		// Create formated string based on \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter::getPathInformation()
		$value->create_date_formatted   = HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC5'));

		// A ISO 8601 date string
		if (empty($value->modified_date)) {
				throw new BadMethodCallException("Property 'modified_date' of argument 'file' of event {$this->name} has a wrong value. Valid: ISO 8601 date string");
		}

		// Validate date format
		$date = Date::createFromFormat(\DATE_ISO8601, $value->modified_date);
		if (!$date) {
				throw new BadMethodCallException("Property 'modified_date' of argument 'file' of event {$this->name} has a wrong value. Valid: ISO 8601 date string");
		}

		// Create formated string based on \Joomla\Plugin\Filesystem\Local\Adapter\LocalAdapter::getPathInformation()
		$value->modified_date_formatted   = HTMLHelper::_('date', $date, Text::_('DATE_FORMAT_LC5'));

		return $value;
	}
}
