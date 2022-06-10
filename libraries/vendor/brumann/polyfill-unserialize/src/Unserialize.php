<?php

namespace Brumann\Polyfill;

final class Unserialize
{
    /**
     * @see https://secure.php.net/manual/en/function.unserialize.php
     *
     * @param string $serialized Serialized data
     * @param array $options Associative array containing options
     *
     * @return mixed
     */
    public static function unserialize($serialized, array $options = array())
    {
        if (PHP_VERSION_ID >= 70000) {
            return \unserialize($serialized, $options);
        }
        if (!array_key_exists('allowed_classes', $options) || true === $options['allowed_classes']) {
            return \unserialize($serialized);
        }
        $allowedClasses = $options['allowed_classes'];
        if (false === $allowedClasses) {
            $allowedClasses = array();
        }
        if (!is_array($allowedClasses)) {
            $allowedClasses = array();
            trigger_error(
                'unserialize(): allowed_classes option should be array or boolean',
                E_USER_WARNING
            );
        }

        $worker = new DisallowedClassesSubstitutor($serialized, $allowedClasses);

        return \unserialize($worker->getSubstitutedSerialized());
    }
}
