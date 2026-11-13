<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_media
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Media\Administrator\File;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Class wrapper for uploaded file $_FILE.
 *
 * @since  __DEPLOY_VERSION__
 */
final class TmpFileUpload
{
    /**
     * The file name
     *
     * @var  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected string $name = '';

    /**
     * The file path
     *
     * @var  string
     *
     * @since  __DEPLOY_VERSION__
     */
    protected string $uri = '';

    /**
     * The file size
     *
     * @var  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected int $size = 0;

    /**
     * The upload error code, if any.
     * See https://www.php.net/manual/en/filesystem.constants.php#constant.upload-err-cant-write
     *
     * @var  integer
     *
     * @since  __DEPLOY_VERSION__
     */
    protected int $error = 0;

    /**
     * Class constructor.
     *
     * @param array $file  A single $_FILE instance with: name, tmp_name, size, error.
     *
     * @since  __DEPLOY_VERSION__
     */
    public function __construct(array $file)
    {
        $this->name  = $file['name'] ?? '';
        $this->uri   = $file['tmp_name'] ?? '';
        $this->size  = $file['size'] ?? 0;
        $this->error = $file['error'] ?? 0;
    }

    /**
     * Reading the file data while accessing to object as string.
     * Made for backward compatibility only.
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     *
     * @deprecated  __DEPLOY_VERSION__ will be removed in 7.0 without replacement.
     */
    final public function __toString(): string
    {
        @trigger_error(
            'Stringification and direct accessing to the file content are deprecated, and will be removed in 7.0.',
            E_USER_DEPRECATED
        );

        return file_get_contents($this->getUri()) ?: '';
    }

    /**
     * Return the name.
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Return the path to the file.
     *
     * @return string
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Return file size.
     *
     * @return integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Return upload error code.
     *
     * @return integer
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getError(): int
    {
        return $this->error;
    }
}
