<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  User.terms
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

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
 * @var   array    $termsnote              The terms note that needs to be displayed
 * @var   array    $translateLabel         Should the label be translated?
 * @var   array    $translateHint          Should the hint be translated?
 * @var   array    $termsArticle           The Article ID holding the Terms Article
 */

echo '<div class="alert alert-info">' . $termsnote . '</div>';
