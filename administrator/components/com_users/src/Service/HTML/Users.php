<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Users\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\ParameterType;

/**
 * Extended Utility class for the Users component.
 *
 * @since  2.5
 */
class Users
{
	/**
	 * Display an image.
	 *
	 * @param   string  $src  The source of the image
	 *
	 * @return  string  A <img> element if the specified file exists, otherwise, a null string
	 *
	 * @since   2.5
	 * @throws  \Exception
	 */
	public function image($src)
	{
		$src = preg_replace('#[^A-Z0-9\-_\./]#i', '', $src);
		$file = JPATH_SITE . '/' . $src;

		Path::check($file);

		if (!file_exists($file))
		{
			return '';
		}

		return '<img src="' . Uri::root() . $src . '" alt="">';
	}

	/**
	 * Displays an icon to add a note for this user.
	 *
	 * @param   integer  $userId  The user ID
	 *
	 * @return  string  A link to add a note
	 *
	 * @since   2.5
	 */
	public function addNote($userId)
	{
		$title = Text::_('COM_USERS_ADD_NOTE');

		return '<a href="' . Route::_('index.php?option=com_users&task=note.add&u_id=' . (int) $userId)
			. '" class="btn btn-secondary btn-sm"><span class="icon-plus pe-1" aria-hidden="true">'
			. '</span>' . $title . '</a>';
	}

	/**
	 * Displays an icon to filter the notes list on this user.
	 *
	 * @param   integer  $count   The number of notes for the user
	 * @param   integer  $userId  The user ID
	 *
	 * @return  string  A link to apply a filter
	 *
	 * @since   2.5
	 */
	public function filterNotes($count, $userId)
	{
		if (empty($count))
		{
			return '';
		}

		$title = Text::_('COM_USERS_FILTER_NOTES');

		return '<a href="' . Route::_('index.php?option=com_users&view=notes&filter[search]=uid:' . (int) $userId)
			. '" class="dropdown-item"><span class="icon-list pe-1" aria-hidden="true"></span>' . $title . '</a>';
	}

	/**
	 * Displays a note icon.
	 *
	 * @param   integer  $count   The number of notes for the user
	 * @param   integer  $userId  The user ID
	 *
	 * @return  string  A link to a modal window with the user notes
	 *
	 * @since   2.5
	 */
	public function notes($count, $userId)
	{
		if (empty($count))
		{
			return '';
		}

		$title = Text::plural('COM_USERS_N_USER_NOTES', $count);

		return '<button  type="button" data-bs-target="#userModal_' . (int) $userId . '" id="modal-' . (int) $userId
			. '" data-bs-toggle="modal" class="dropdown-item"><span class="icon-eye pe-1" aria-hidden="true"></span>' . $title . '</button>';
	}

	/**
	 * Renders the modal html.
	 *
	 * @param   integer  $count   The number of notes for the user
	 * @param   integer  $userId  The user ID
	 *
	 * @return  string   The html for the rendered modal
	 *
	 * @since   3.4.1
	 */
	public function notesModal($count, $userId)
	{
		if (empty($count))
		{
			return '';
		}

		$title = Text::plural('COM_USERS_N_USER_NOTES', $count);
		$footer = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">'
			. Text::_('JTOOLBAR_CLOSE') . '</button>';

		return HTMLHelper::_(
			'bootstrap.renderModal',
			'userModal_' . (int) $userId,
			array(
				'title'       => $title,
				'backdrop'    => 'static',
				'keyboard'    => true,
				'closeButton' => true,
				'footer'      => $footer,
				'url'         => Route::_('index.php?option=com_users&view=notes&tmpl=component&layout=modal&filter[user_id]=' . (int) $userId),
				'height'      => '300px',
				'width'       => '800px',
			)
		);

	}

	/**
	 * Build an array of block/unblock user states to be used by jgrid.state,
	 * State options will be different for any user
	 * and for currently logged in user
	 *
	 * @param   boolean  $self  True if state array is for currently logged in user
	 *
	 * @return  array  a list of possible states to display
	 *
	 * @since  3.0
	 */
	public function blockStates( $self = false)
	{
		if ($self)
		{
			$states = array(
				1 => array(
					'task'           => 'unblock',
					'text'           => '',
					'active_title'   => 'COM_USERS_TOOLBAR_BLOCK',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'unpublish',
					'inactive_class' => 'unpublish',
				),
				0 => array(
					'task'           => 'block',
					'text'           => '',
					'active_title'   => '',
					'inactive_title' => 'COM_USERS_USERS_ERROR_CANNOT_BLOCK_SELF',
					'tip'            => true,
					'active_class'   => 'publish',
					'inactive_class' => 'publish',
				)
			);
		}
		else
		{
			$states = array(
				1 => array(
					'task'           => 'unblock',
					'text'           => '',
					'active_title'   => 'COM_USERS_TOOLBAR_UNBLOCK',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'unpublish',
					'inactive_class' => 'unpublish',
				),
				0 => array(
					'task'           => 'block',
					'text'           => '',
					'active_title'   => 'COM_USERS_TOOLBAR_BLOCK',
					'inactive_title' => '',
					'tip'            => true,
					'active_class'   => 'publish',
					'inactive_class' => 'publish',
				)
			);
		}

		return $states;
	}

	/**
	 * Build an array of activate states to be used by jgrid.state,
	 *
	 * @return  array  a list of possible states to display
	 *
	 * @since  3.0
	 */
	public function activateStates()
	{
		$states = array(
			1 => array(
				'task'           => 'activate',
				'text'           => '',
				'active_title'   => 'COM_USERS_TOOLBAR_ACTIVATE',
				'inactive_title' => '',
				'tip'            => true,
				'active_class'   => 'unpublish',
				'inactive_class' => 'unpublish',
			),
			0 => array(
				'task'           => '',
				'text'           => '',
				'active_title'   => '',
				'inactive_title' => 'COM_USERS_ACTIVATED',
				'tip'            => true,
				'active_class'   => 'publish',
				'inactive_class' => 'publish',
			)
		);

		return $states;
	}

	/**
	 * Get the sanitized value
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  mixed  String/void
	 *
	 * @since   1.6
	 */
	public function value($value)
	{
		if (is_string($value))
		{
			$value = trim($value);
		}

		if (empty($value))
		{
			return Text::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
		}

		elseif (!is_array($value))
		{
			return htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
		}
	}

	/**
	 * Get the space symbol
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  string
	 *
	 * @since   1.6
	 */
	public function spacer($value)
	{
		return '';
	}

	/**
	 * Get the sanitized template style
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  mixed  String/void
	 *
	 * @since   1.6
	 */
	public function templatestyle($value)
	{
		if (empty($value))
		{
			return static::value($value);
		}
		else
		{
			$db = Factory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__template_styles'))
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $value, ParameterType::INTEGER);
			$db->setQuery($query);
			$title = $db->loadResult();

			if ($title)
			{
				return htmlspecialchars($title, ENT_COMPAT, 'UTF-8');
			}
			else
			{
				return static::value('');
			}
		}
	}

	/**
	 * Get the sanitized language
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  mixed  String/void
	 *
	 * @since   1.6
	 */
	public function admin_language($value)
	{
		if (!$value)
		{
			return static::value($value);
		}

		$path   = LanguageHelper::getLanguagePath(JPATH_ADMINISTRATOR, $value);
		$file   = $path . '/langmetadata.xml';

		if (!is_file($file))
		{
			// For language packs from before 4.0.
			$file = $path . '/' . $value . '.xml';

			if (!is_file($file))
			{
				return static::value($value);
			}
		}

		$result = LanguageHelper::parseXMLLanguageFile($file);

		if ($result)
		{
			return htmlspecialchars($result['name'], ENT_COMPAT, 'UTF-8');
		}

		return static::value($value);
	}

	/**
	 * Get the sanitized language
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  mixed  String/void
	 *
	 * @since   1.6
	 */
	public function language($value)
	{
		if (!$value)
		{
			return static::value($value);
		}

		$path   = LanguageHelper::getLanguagePath(JPATH_SITE, $value);
		$file   = $path . '/langmetadata.xml';

		if (!is_file($file))
		{
			// For language packs from before 4.0.
			$file = $path . '/' . $value . '.xml';

			if (!is_file($file))
			{
				return static::value($value);
			}
		}

		$result = LanguageHelper::parseXMLLanguageFile($file);

		if ($result)
		{
			return htmlspecialchars($result['name'], ENT_COMPAT, 'UTF-8');
		}

		return static::value($value);
	}

	/**
	 * Get the sanitized editor name
	 *
	 * @param   mixed  $value  Value of the field
	 *
	 * @return  mixed  String/void
	 *
	 * @since   1.6
	 */
	public function editor($value)
	{
		if (empty($value))
		{
			return static::value($value);
		}
		else
		{
			$db = Factory::getDbo();
			$lang = Factory::getLanguage();
			$query = $db->getQuery(true)
				->select($db->quoteName('name'))
				->from($db->quoteName('#__extensions'))
				->where($db->quoteName('element') . ' = :element')
				->where($db->quoteName('folder') . ' = ' . $db->quote('editors'))
				->bind(':element', $value);
			$db->setQuery($query);
			$title = $db->loadResult();

			if ($title)
			{
				$lang->load("plg_editors_$value.sys", JPATH_ADMINISTRATOR)
				|| $lang->load("plg_editors_$value.sys", JPATH_PLUGINS . '/editors/' . $value);
				$lang->load($title . '.sys');

				return Text::_($title);
			}
			else
			{
				return static::value('');
			}
		}
	}
}
