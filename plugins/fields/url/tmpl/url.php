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
$schemes    = (array) $fieldParams->get('schemes', []);
$isMailOnly = \count($schemes) === 1 && \in_array('mailto', $schemes, true);

if ($isMailOnly) {
    $email      = stripos($value, 'mailto:') === 0 ? substr($value, 7) : $value;
    $href       = 'mailto:' . $email;
    $attributes = '';
    $text       = $fieldParams->get('show_url', 0) ? htmlspecialchars($email) : Text::_('JVISIT_LINK');
} else {
    $href = $value;

    if (!Uri::isInternal($value)) {
        $attributes = ' rel="nofollow noopener noreferrer" target="_blank"';
        $text       = Text::_('JVISIT_WEBSITE');
    } else {
        $attributes = '';
        $text       = Text::_('JVISIT_LINK');
    }

    if ($fieldParams->get('show_url', 0)) {
        $text = htmlspecialchars($value);
    }
}

echo sprintf(
    '<a href="%s"%s>%s</a>',
    htmlspecialchars($href),
    $attributes,
    $text
);
