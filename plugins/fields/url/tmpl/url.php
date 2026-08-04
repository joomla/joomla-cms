<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Fields.URL
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

$value = $field->value;

if ($value == '') {
    return;
}

// If "mailto" is the only allowed scheme, the stored value is a plain e-mail address (see
// plugins/fields/url onCustomFieldsPrepareDom()). Build the mailto: link here so the scheme
// never has to be typed by the user or shown to the visitor. Values saved before this fix may
// still carry a manually typed "mailto:" prefix (the previously documented workaround); strip
// it either way so old and new data render identically. See GH #37029.
$schemes = (array) $fieldParams->get('schemes', []);

if (\count($schemes) === 1 && reset($schemes) === 'mailto') {
    $email = preg_replace('/^mailto:/i', '', $value);
    $href  = 'mailto:' . $email;
    $text  = $fieldParams->get('show_url', 0) ? htmlspecialchars($email) : Text::_('JVISIT_LINK');

    echo sprintf('<a href="%s">%s</a>', htmlspecialchars($href), $text);

    return;
}

$attributes = '';

if (!Uri::isInternal($value)) {
    $attributes = ' rel="nofollow noopener noreferrer" target="_blank"';
    $text       = Text::_('JVISIT_WEBSITE');
} else {
    $text       = Text::_('JVISIT_LINK');
}

if ($fieldParams->get('show_url', 0)) {
    $text = htmlspecialchars($value);
}

echo sprintf(
    '<a href="%s"%s>%s</a>',
    htmlspecialchars($value),
    $attributes,
    $text
);
