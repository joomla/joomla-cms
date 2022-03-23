<?php


namespace Tuf;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Class that represents a Delegated TUF role.
 */
class DelegatedRole extends Role
{

    /**
     * @var string[]
     */
    protected $paths;

    /**
     * @var bool
     */
    protected $terminating;

    /**
     * @return bool
     */
    public function isTerminating(): bool
    {
        return $this->terminating;
    }

    /**
     * DelegatedRole constructor.
     *
     * @param string $name
     * @param int $threshold
     * @param array $keyIds
     * @param array $paths
     * @param bool $terminating
     */
    private function __construct(string $name, int $threshold, array $keyIds, array $paths, bool $terminating)
    {
        parent::__construct($name, $threshold, $keyIds);
        $this->paths = $paths;
        $this->terminating = $terminating;
    }

    public static function createFromMetadata(\ArrayObject $roleInfo, string $name = null): Role
    {
        $roleConstraints = static::getRoleConstraints();
        $roleConstraints->fields += [
            'name' => new Required(
                [
                    new Type('string'),
                    new NotBlank(),
                ]
            ),
            'terminating' => new Required(new Type('boolean')),
            'paths' => new Required(new Type('array')),
        ];
        static::validate($roleInfo, $roleConstraints);
        return new static(
            $roleInfo['name'],
            $roleInfo['threshold'],
            $roleInfo['keyids'],
            $roleInfo['paths'],
            $roleInfo['terminating']
        );
    }

    /**
     * Determines whether a target matches a path for this role.
     *
     * @param string $target
     *   The path of the target file.
     *
     * @return bool
     *   True if there is path match or no path criteria is set for the role, or
     *   false otherwise.
     */
    public function matchesPath(string $target): bool
    {
        if ($this->paths) {
            foreach ($this->paths as $path) {
                if (fnmatch($path, $target)) {
                    return true;
                }
            }
            return false;
        }
        // If no paths are set then any target is a match.
        return true;
    }
}
