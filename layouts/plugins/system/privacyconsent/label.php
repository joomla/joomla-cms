<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.privacyconsent
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

extract($displayData);

/**
 * Layout variables
 * -----------------
 * @var   string   $autocomplete           Autocomplete attribute for the field.
 * @var   boolean  $autofocus              Is autofocus enabled?
 * @var   string   $class                  Classes for the input.
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
 * @var   array    $translateHint          Should the hint be translated?
 * @var   array    $privacyArticle         The Article ID holding the Privacy Article.
 * @var   object   $article                The Article object.
 * @var   object   $privacyLink            Link to the privacy article or menu item.
 */

// Get the label text from the XML element, defaulting to the element name.
$text = $label ? (string) $label : (string) $name;
$text = $translateLabel ? Text::_($text) : $text;

// Set required to true as this field is not displayed at all if not required.
$required = true;

// Build the class for the label.
$class = 'required';
$class = !empty($labelclass) ? $class . ' ' . $labelclass : $class;

if ($privacyLink) {
    $attribs = [
        'data-bs-toggle' => 'modal',
        'data-bs-target' => '#consentModal',
        'class' => 'required',
    ];

    $link = HTMLHelper::_('link', Route::_($privacyLink . '&tmpl=component'), $text, $attribs);

    echo HTMLHelper::_(
        'bootstrap.renderModal',
        'consentModal',
        [
            'url'    => Route::_($privacyLink . '&tmpl=component'),
            'title'  => $text,
            'height' => '100%',
            'width'  => '100%',
            'bodyHeight'  => 70,
            'modalWidth'  => 80,
            'footer' => '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" aria-hidden="true">'
                . Text::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</button>',
        ]
    );
} else {
    $link = '<span class="' . $class . '">' . $text . '</span>';
}

// Add the label text and star.
$label = $link . '<span class="star" aria-hidden="true">&#160;*</span>';

echo $label;
