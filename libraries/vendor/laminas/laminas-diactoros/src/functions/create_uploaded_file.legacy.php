<?php

/**
 * @see       https://github.com/laminas/laminas-diactoros for the canonical source repository
 * @copyright https://github.com/laminas/laminas-diactoros/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-diactoros/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Zend\Diactoros;

use function Laminas\Diactoros\createUploadedFile as laminas_createUploadedFile;

/**
 * @deprecated Use Laminas\Diactoros\createUploadedFile instead
 */
function createUploadedFile(array $spec) : UploadedFile
{
    return laminas_createUploadedFile(...func_get_args());
}
