<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete           Autocomplete attribute for the field.
 * @var   boolean  $autofocus              Is autofocus enabled?
 * @var   string   $class                  Classes for the input.
 * @var   string   $description            Description of the field.
 * @var   boolean  $disabled               Is this field disabled?
 * @var   string   $group                  Group the field belongs to. <fields> section in form XML.
 * @var   boolean  $hidden                 Is this field hidden in the form?
 * @var   string   $hint                   Placeholder for the field.
 * @var   string   $id                     DOM id of the field.
 * @var   string   $label                  Label of the field.
 * @var   string   $labelclass             Classes to apply to the label.
 * @var   boolean  $multiple               Does this field support multiple values?
 * @var   string   $name                   Name of the input field.
 * @var   string   $onchange               Onchange attribute for the field.
 * @var   string   $onclick                Onclick attribute for the field.
 * @var   string   $pattern                Pattern (Reg Ex) of value of the form field.
 * @var   boolean  $readonly               Is this field read only?
 * @var   boolean  $repeat                 Allows extensions to duplicate elements.
 * @var   boolean  $required               Is this field required?
 * @var   integer  $size                   Size attribute of the input.
 * @var   boolean  $spellcheck             Spellcheck state for the form field.
 * @var   string   $validate               Validation rules to apply.
 * @var   string   $value                  Value attribute of the field.
 * @var   array    $options                Options available for this field.
 * @var   array    $privacynote            The privacy note that needs to be displayed
 * @var   array    $translateLabel         Should the label be translated?
 * @var   array    $translateDescription   Should the description be translated?
 * @var   array    $translateHint          Should the hint be translated?
 * @var   array    $privacyArticle         The Article ID holding the Privancy Article
 * $var   object   $article                The Article object
 */

// Get the label text from the XML element, defaulting to the element name.
$text = $label ? (string) $label : (string) $name;
$text = $translateLabel ? Text::_($text) : $text;

// Set required to true as this field is not displayed at all if not required.
$required = true;

JHtml::_('behavior.modal');

// Build the class for the label.
$class = !empty($description) ? 'hasPopover' : '';
$class = $class . ' required';
$class = !empty($labelclass) ? $class . ' ' . $labelclass : $class;

// Add the opening label tag and main attributes.
$label = '<label id="' . $id . '-lbl" for="' . $id . '" class="' . $class . '"';

// If a description is specified, use it to build a tooltip.
if (!empty($description))
{
	$label .= ' title="' . htmlspecialchars(trim($text, ':'), ENT_COMPAT, 'UTF-8') . '"';
	$label .= ' data-content="' . htmlspecialchars(
		$translateDescription ? Text::_($description) : $description,
		ENT_COMPAT,
		'UTF-8'
	) . '"';
}

if (Factory::getLanguage()->isRtl())
{
	$label .= ' data-placement="left"';
}

$attribs          = array();
$attribs['class'] = 'modal';
$attribs['rel']   = '{handler: \'iframe\', size: {x:800, y:500}}';

if ($article)
{
	$link = JHtml::_('link', Route::_($article->link . '&tmpl=component'), $text, $attribs);
}
else
{
	$link = $text;
}

// Add the label text and closing tag.
$label .= '>' . $link . '<span class="star">&#160;*</span></label>';

echo $label;
