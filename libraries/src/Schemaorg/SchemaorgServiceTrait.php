<?php

namespace Joomla\CMS\Schemaorg;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Trait for component schemaorg service.
 *
 * @since  4.0.0
 */
trait SchemaorgServiceTrait
{
    /**
     * Get a MVC factory
     *
     * @return  MVCFactoryInterface
     *
     * @since   4.0.0
     */
    abstract public function getMVCFactory(): MVCFactoryInterface;

    /**
     * Check if the functionality is supported by the component
     * The variable $supportSchemaFunctionality has the following structure
     * [
     *   'core.featured' => [
     *     'com_content.article',
     *   ],
     *   'core.state' => [
     *     'com_content.article',
     *   ],
     * ]
     *
     * @param   string  $functionality  The functionality
     * @param   string  $context        The context of the functionality
     *
     * @return boolean
     */
    public function supportSchemaFunctionality($functionality, $context): bool
    {
        if (empty($this->supportedFunctionality[$functionality])) {
            return false;
        }

        if (!is_array($this->supportedFunctionality[$functionality])) {
            return true;
        }

        return in_array($context, $this->supportedFunctionality[$functionality], true);
    }
}
