<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Contact\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Contact\Site\Helper\RouteHelper;
use Joomla\Registry\Registry;

/**
 * Content Component HTML Helper
 *
 * @since  4.0.0
 */
class Icon
{
	/**
	 * The application
	 *
	 * @var    CMSApplication
	 *
	 * @since  4.0.0
	 */
	private $application;

	/**
	 * Service constructor
	 *
	 * @param   CMSApplication  $application  The application
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplication $application)
	{
		$this->application = $application;
	}

	/**
	 * Method to generate a link to the create item page for the given category
	 *
	 * @param   object    $category  The category information
	 * @param   Registry  $params    The item parameters
	 * @param   array     $attribs   Optional attributes for the link
	 *
	 * @return  string  The HTML markup for the create item link
	 *
	 * @since  4.0.0
	 */
	public static function create($category, $params, $attribs = array())
	{
		$uri = Uri::getInstance();

		$url = 'index.php?option=com_contact&task=contact.add&return=' . base64_encode($uri) . '&id=0&catid=' . $category->id;

		$text = '';

		if ($params->get('show_icons'))
		{
			$text .= '<span class="icon-plus icon-fw" aria-hidden="true"></span>';
		}

		$text .= Text::_('COM_CONTACT_NEW_CONTACT');

		// Add the button classes to the attribs array
		if (isset($attribs['class']))
		{
			$attribs['class'] .= ' btn btn-primary';
		}
		else
		{
			$attribs['class'] = 'btn btn-primary';
		}

		$button = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $button;
	}

	/**
	 * Display an edit icon for the contact.
	 *
	 * This icon will not display in a popup window, nor if the contact is trashed.
	 * Edit access checks must be performed in the calling code.
	 *
	 * @param   object    $contact  The contact information
	 * @param   Registry  $params   The item parameters
	 * @param   array     $attribs  Optional attributes for the link
	 * @param   boolean   $legacy   True to use legacy images, false to use icomoon based graphic
	 *
	 * @return  string   The HTML for the contact edit icon.
	 *
	 * @since   4.0.0
	 */
	public static function edit($contact, $params, $attribs = array(), $legacy = false)
	{
		$user = Factory::getUser();
		$uri  = Uri::getInstance();

		// Ignore if in a popup window.
		if ($params && $params->get('popup'))
		{
			return '';
		}

		// Ignore if the state is negative (trashed).
		if ($contact->published < 0)
		{
			return '';
		}

		// Show checked_out icon if the contact is checked out by a different user
		if (property_exists($contact, 'checked_out')
			&& property_exists($contact, 'checked_out_time')
			&& !is_null($contact->checked_out)
			&& $contact->checked_out !== $user->get('id'))
		{
			$checkoutUser = Factory::getUser($contact->checked_out);
			$date         = HTMLHelper::_('date', $contact->checked_out_time);
			$tooltip      = Text::sprintf('COM_CONTACT_CHECKED_OUT_BY', $checkoutUser->name)
				. ' <br> ' . $date;

			$text = LayoutHelper::render('joomla.content.icons.edit_lock', array('contact' => $contact, 'tooltip' => $tooltip, 'legacy' => $legacy));

			$attribs['aria-describedby'] = 'editcontact-' . (int) $contact->id;
			$output = HTMLHelper::_('link', '#', $text, $attribs);

			return $output;
		}

		$contactUrl = RouteHelper::getContactRoute($contact->slug, $contact->catid, $contact->language);
		$url        = $contactUrl . '&task=contact.edit&id=' . $contact->id . '&return=' . base64_encode($uri);

		if ((int) $contact->published === 0)
		{
			$tooltip = Text::_('COM_CONTACT_EDIT_UNPUBLISHED_CONTACT');
		}
		else
		{
			$tooltip = Text::_('COM_CONTACT_EDIT_PUBLISHED_CONTACT');
		}

		$nowDate = strtotime(Factory::getDate());
		$icon    = $contact->published ? 'edit' : 'eye-slash';

		if (($contact->publish_up !== null && strtotime($contact->publish_up) > $nowDate)
			|| ($contact->publish_down !== null && strtotime($contact->publish_down) < $nowDate
			&& $contact->publish_down !== Factory::getDbo()->getNullDate()))
		{
			$icon = 'eye-slash';
		}

		$aria_described = 'editcontact-' . (int) $contact->id;

		$text = '<span class="icon-' . $icon . '" aria-hidden="true"></span>';
		$text .= Text::_('JGLOBAL_EDIT');
		$text .= '<div role="tooltip" id="' . $aria_described . '">' . $tooltip . '</div>';

		$attribs['aria-describedby'] = $aria_described;
		$output = HTMLHelper::_('link', Route::_($url), $text, $attribs);

		return $output;
	}
}
