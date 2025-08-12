<?php

declare(strict_types=1);

namespace Joomla\CMS\Inflector\Rules\Joomla;

use Doctrine\Inflector\GenericLanguageInflectorFactory;
use Doctrine\Inflector\Rules\Ruleset;

/**
 * The "Joomla Token Language" is designed to fulfill the requirement with different words for singular and plural
 * form of a word. This language is normally not used for user facing content. It is used to find the singular or
 * the plural version of a controller, model or view. It might be used in cases where an uniq token is needed.
 * As example a URI could be possible using this "language" to guarantee to not have same url for 2 different urls.
 *
 * Main changes compared to English is
 * * Removed "uninflectible" rules, which are mostly words with have the same spelling in singular and plural
 * * Adding "es" in case the inflection doesn't find a proper way for pluralize a word ending with a "s".
 *
 * You should not use this "language" for user facing words.
 *
 * Examples for not working pluralization with English:
 * * Creating an MVC called "travel" will fail because "travel" has the same spelling in singular and plural
 * * What we keep is irregular verbs "person" will still be "people" even if it's not optimal for DX
 */
final class InflectorFactory extends GenericLanguageInflectorFactory
{
    protected function getSingularRuleset(): Ruleset
    {
        return Rules::getSingularRuleset();
    }

    protected function getPluralRuleset(): Ruleset
    {
        return Rules::getPluralRuleset();
    }
}
