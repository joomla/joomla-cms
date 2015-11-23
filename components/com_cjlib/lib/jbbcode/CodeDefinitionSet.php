<?php

namespace JBBCode;

require_once 'CodeDefinition.php';

use JBBCode\CodeDefinition;

/**
 * An interface for sets of code definitons.
 *
 * @author jbowens
 */
interface CodeDefinitionSet
{

    /**
     * Retrieves the CodeDefinitions within this set as an array.
     */
    public function getCodeDefinitions();

}
