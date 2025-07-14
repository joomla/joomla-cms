<?php

/**
 * Script used to build Joomla distribution archive packages
 * Builds packages in tmp/packages folder (for example, 'build/tmp/packages')
 *
 * Note: the new package must be tagged in your git repository BEFORE doing this
 * It uses the git tag for the new version, not trunk.
 *
 * This script is designed to be run in CLI on Linux, Mac OS X and WSL.
 * Make sure your default umask is 022 to create archives with correct permissions.
 *
 * For WSL based setups make sure there is a /etc/wsl.conf with the following content:
 * [automount]
 * enabled=true
 * options=metadata,uid=1000,gid=1000,umask=022
 *
 * Steps:
 * 1. Tag new release in the local git repository (for example, "git tag 2.5.1")
 * 2. Set the $version and $release variables for the new version.
 * 3. Run from CLI as: 'php build.php" from build directory.
 * 4. Check the archives in the tmp directory.
 *
 * @package    Joomla.Build
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Version;

const PHP_TAB = "\t";

function usage(string $command)
{
    echo PHP_EOL;
    echo 'Usage: php ' . $command . ' [options]' . PHP_EOL;
    echo PHP_TAB . '[options]:' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--remote=<remote>:' . PHP_TAB . PHP_TAB . 'The git remote reference to build from (ex: `tags/3.8.6`, `4.0-dev`), defaults to the most recent tag for the repository' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--exclude-zip:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Exclude the generation of .zip packages' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--exclude-gzip:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Exclude the generation of .tar.gz packages' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--include-bzip2:' . PHP_TAB . PHP_TAB . 'Exclude the generation of .tar.bz2 packages' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--exclude-zstd:' . PHP_TAB . PHP_TAB . PHP_TAB . 'Include the generation of .tar.zst packages' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--disable-patch-packages:' . PHP_TAB . 'Disable the generation of patch packages' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--debug-build:' . PHP_TAB . 'Include development packages and build folder' . PHP_EOL;
    echo PHP_TAB . PHP_TAB . '--help:' . PHP_TAB . PHP_TAB . PHP_TAB . PHP_TAB . 'Show this help output' . PHP_EOL;
    echo PHP_EOL;
}

function clean_checkout(string $dir)
{
    // Save the current working directory to restore when complete
    $cwd = getcwd();
    chdir($dir);

    echo "Cleaning checkout in $dir.\n";

    // Removes .DS_Store; .git sources; testing, CI, and IDE configuration files; Changelogs; GitHub Meta; and README files
    run_and_check('find . -name .appveyor.yml | xargs rm -rf -');
    run_and_check('find . -name .coveralls.yml | xargs rm -rf -');
    run_and_check('find . -name .DS_Store | xargs rm -rf -');
    run_and_check('find . -name .editorconfig | xargs rm -rf -');
    run_and_check('find . -name .gitattributes | xargs rm -rf -');
    run_and_check('find . -name .github | xargs rm -rf -');
    run_and_check('find . -name .gitignore | xargs rm -rf -');
    run_and_check('find . -name .gitmodules | xargs rm -rf -');
    run_and_check('find . -name .phan | xargs rm -rf -');
    run_and_check('find . -name .php-cs-fixer.dist.php | xargs rm -rf -');
    run_and_check('find . -name .scrutinizer.yml | xargs rm -rf -');
    run_and_check('find . -name .travis.yml | xargs rm -rf -');
    run_and_check('find . -name appveyor.yml | xargs rm -rf -');
    run_and_check('find . -name CHANGELOG.md | xargs rm -rf -');
    run_and_check('find . -name CONTRIBUTING.md | xargs rm -rf -');
    run_and_check('find . -name psalm.xml | xargs rm -rf -');
    run_and_check('find . -name psalm.xml.dist | xargs rm -rf -');
    run_and_check('find . -name phpcs.xml | xargs rm -rf -');
    run_and_check('find . -name phpcs.xml.dist | xargs rm -rf -');
    run_and_check('find . -name phpstan.neon | xargs rm -rf -');
    run_and_check('find . -name phpunit.xml | xargs rm -rf -');
    run_and_check('find . -name phpunit.*.xml | xargs rm -rf -');
    run_and_check('find . -name phpunit.xml.dist | xargs rm -rf -');
    run_and_check('find . -name README.md | xargs rm -rf -');
    run_and_check('find . -name SECURITY.md | xargs rm -rf -');

    echo "Cleaning vendors.\n";

    run_and_check('find libraries/vendor -name CODE_OF_CONDUCT.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name CONDUCT.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name docker-compose.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpunit.xml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name README.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name readme.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name UPGRADING.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name SUMMARY.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .travis.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .git | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .gitignore | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .gitmodules | xargs rm -rf -');
    run_and_check('find libraries/vendor -name ISSUE_TEMPLATE | xargs rm -rf -');
    run_and_check('find libraries/vendor -name CONTRIBUTING.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name CHANGES.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name CHANGELOG.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name SECURITY.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name psalm.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name psalm-baseline.md | xargs rm -rf -');
    run_and_check('find libraries/vendor -name psalm-baseline.xml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .yamllint | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .remarkrc | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .editorconfig | xargs rm -rf -');
    run_and_check('find libraries/vendor -name appveyor.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpunit.xml.dist | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .php_cs | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .php_cs.dist | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpcs.xsd | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpcs.xml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name build.xml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name infection.json.dist | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpbench.json | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpstan.neon.dist | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .doctrine-project.json | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .pullapprove.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name phpstan.neon | xargs rm -rf -');
    run_and_check('find libraries/vendor -name _config.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .bowerrc | xargs rm -rf -');
    run_and_check('find libraries/vendor -name bower.json | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .drone.yml | xargs rm -rf -');
    run_and_check('find libraries/vendor -name .drone.jsonnet | xargs rm -rf -');
    run_and_check('rm -rf libraries/vendor/bin');

    // aldo26-matthias/idna-convert
    run_and_check('rm -rf libraries/vendor/algo26-matthias/idna-convert/tests');

    // defuse/php-encryption
    run_and_check('rm -rf libraries/vendor/defuse/php-encryption/docs');

    // doctrine/inflector
    run_and_check('rm -rf libraries/vendor/doctrine/inflector/docs');

    // fig/link-util
    run_and_check('rm -rf libraries/vendor/fig/link-util/test');

    // google/recaptcha
    run_and_check('rm -rf libraries/vendor/google/recaptcha/examples');
    run_and_check('rm -rf libraries/vendor/google/recaptcha/tests');

    // jakeasmith/http_build_url
    run_and_check('rm -rf libraries/vendor/jakeasmith/http_build_url/tests');

    // joomla/*
    run_and_check('rm -rf libraries/vendor/joomla/*/docs');
    run_and_check('rm -rf libraries/vendor/joomla/*/tests');
    run_and_check('rm -rf libraries/vendor/joomla/*/Tests');
    run_and_check('rm -rf libraries/vendor/joomla/*/ruleset.xml');

    // testing sampledata
    run_and_check('rm -rf plugins/sampledata/testing');
    run_and_check('rm -rf images/sampledata/parks');
    run_and_check('rm -rf images/sampledata/fruitshop');

    // paragonie/sodium_compat
    run_and_check('rm -rf libraries/vendor/paragonie/sodium_compat/build-phar.sh');

    // phpmailer/phpmailer
    run_and_check('rm -rf libraries/vendor/phpmailer/phpmailer/language');
    run_and_check('rm -rf libraries/vendor/phpmailer/phpmailer/get_oauth_token.php');

    // psr/log
    run_and_check('rm -rf libraries/vendor/psr/log/Psr/Log/Test');

    // symfony/*
    run_and_check('rm -rf libraries/vendor/symfony/*/Resources/doc');
    run_and_check('rm -rf libraries/vendor/symfony/*/Tests');
    run_and_check('rm -rf libraries/vendor/symfony/console/Resources');
    run_and_check('rm -rf libraries/vendor/symfony/string/Resources/bin');

    // tobscure/json-api
    run_and_check('rm -rf libraries/vendor/tobscure/json-api/tests');

    // wamania/php-stemmer
    run_and_check('rm -rf libraries/vendor/wamania/php-stemmer/test');

    // willdurand/negotiation
    run_and_check('rm -rf libraries/vendor/willdurand/negotiation/tests');

    // jfcherng
    run_and_check('rm -rf libraries/vendor/jfcherng/php-color-output/demo.php');
    run_and_check('rm -rf libraries/vendor/jfcherng/php-color-output/UPGRADING_v2.md');
    run_and_check('rm -rf libraries/vendor/jfcherng/php-diff/CHANGELOG');
    run_and_check('rm -rf libraries/vendor/jfcherng/php-diff/example');
    run_and_check('rm -rf libraries/vendor/jfcherng/php-diff/UPGRADING');
    run_and_check('rm -rf libraries/vendor/jfcherng/php-mb-string/CHANGELOG');

    echo "Cleanup complete.\n";

    chdir($cwd);
}

function clean_composer(string $dir)
{
    // Save the current working directory to restore when complete
    $cwd = getcwd();
    chdir($dir);

    echo "Cleaning Composer manifests in $dir.\n";

    // Removes Composer manifests
    run_and_check('find . -name composer.json | xargs rm -rf -');
    run_and_check('find . -name composer.lock | xargs rm -rf -');

    echo "Cleanup complete.\n";

    chdir($cwd);
}

/**
 * Executes a system command to build a package file.
 * Shows a start and a finish message and terminates if an error occurs.
 *
 * @param string $packageName Name of the package file to be created.
 * @param string $command The full system command to execute.
 *
 * @return void
 */
function build_and_check(string $packageName, string $command): void
{
    echo "Building {$packageName} ... ";
    run_and_check($command);
    echo "done.\n";
}

/**
 * Executes a system command and exits the script if the command fails.
 *
 * @param string $command The full system command to execute.
 *
 * @return void
 */
function run_and_check(string $command): void
{
    $exitCode    = 0;

    // Let stdout/stderr pass through to the terminal
    passthru($command, $exitCode);

    if ($exitCode !== 0) {
        echo "failed.\n";
        fwrite(STDERR, "ERROR: Command failed (exit code: $exitCode): $command\n");
        exit($exitCode);
    }
}

/**
 * Executes a command, returns output, and exits the script if the command fails.
 *
 * @param string $command
 * @return string Output from the command
 */
function capture_or_fail(string $command): string
{
    $output   = [];
    $exitCode = 0;

    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        fwrite(STDERR, "ERROR: Command failed (exit code $exitCode): $command\n");
        exit($exitCode);
    }

    return implode("\n", $output);
}

$time = time();

// Set path to git binary (e.g., /usr/local/git/bin/git or /usr/bin/git)
$systemGit = trim(
    capture_or_fail('which git')
);

// Make sure file and folder permissions are set correctly
umask(022);

// Shortcut the paths to the repository root and build folder
$repo = \dirname(__DIR__);
$here = __DIR__;

// Set paths for the build packages
$tmp      = $here . '/tmp';
$fullpath = $tmp . '/' . $time;

// Parse input options
$options = getopt('', ['help', 'remote::', 'exclude-zip', 'exclude-gzip', 'include-bzip2', 'exclude-zstd', 'debug-build', 'disable-patch-packages']);

$remote             = $options['remote'] ?? false;
$debugBuild         = isset($options['debug-build']);
$excludeZip         = isset($options['exclude-zip']);
$excludeGzip        = isset($options['exclude-gzip']);
$excludeBzip2       = !isset($options['include-bzip2']);
$excludeZstd        = isset($options['exclude-zstd']);
$buildPatchPackages = false && !isset($options['disable-patch-packages']);
$showHelp           = isset($options['help']);

// Disable the generation of extra text files
$includeExtraTextfiles = false;

if ($showHelp) {
    usage($argv[0]);
    exit;
}

// If not given a remote, assume we are looking for the latest local tag
if (!$remote) {
    chdir($repo);
    $tagVersion = capture_or_fail($systemGit . ' describe --tags `' . $systemGit . ' rev-list --tags --max-count=1`');
    $remote     = 'tags/' . $tagVersion;
    chdir($here);

    // We are in release mode so we need the extra text files
    $includeExtraTextfiles = true;
}

$composerOptions = ' ';
if (!$debugBuild) {
    $composerOptions .= '--no-dev';
}

echo "Start build for remote $remote.\n";
echo "Delete old release folder.\n";
run_and_check('rm -rf ' . $tmp);
mkdir($tmp);
mkdir($fullpath);

echo "Copy the files from the git repository.\n";
chdir($repo);
run_and_check($systemGit . ' archive ' . $remote . ' | tar -x -C ' . $fullpath);
// Install PHP and NPM dependencies and compile required media assets, skip Composer autoloader until post-cleanup
chdir($fullpath);
run_and_check('composer install --no-autoloader --ignore-platform-reqs' . $composerOptions);

// Try to update the fido.jwt file
if (!file_exists(rtrim($fullpath, '\\/') . '/plugins/system/webauthn/fido.jwt')) {
    echo "The file plugins/system/webauthn/fido.jwt was not created. Build failed.\n";

    exit(1);
}

run_and_check('npm install --unsafe-perm');

// Create version entries of the urls inside the static css files
run_and_check('npm run cssversioning');

// Create gzipped version of the static assets
run_and_check('npm run gzip');

// Create version entries of the static assets in their respective joomla.asset.json
run_and_check('npm run versioning');

// Clean the checkout of extra resources
if (!$debugBuild) {
    clean_checkout($fullpath);
}

// Regenerate the Composer autoloader without deleted files
run_and_check('composer dump-autoload --optimize --no-scripts' . $composerOptions);

// Clean the Composer manifests now
if (!$debugBuild) {
    clean_composer($fullpath);
}

// And cleanup the Node installation
if (!$debugBuild) {
    run_and_check('rm -rf node_modules');
}

echo "Workspace built.\n";

// Import the version class to set the version information
\define('_JEXEC', 1);
require_once $fullpath . '/libraries/src/Version.php';

// Set version information for the build
$majorVersion = Version::MAJOR_VERSION;
$version      = Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION;
$release      = Version::PATCH_VERSION;
$fullVersion  = (new Version())->getShortVersion();

$previousRelease = Version::PATCH_VERSION - 1;

if ($previousRelease < 0) {
    $previousRelease = false;
}

chdir($tmp);
run_and_check('mkdir diffdocs');
run_and_check('mkdir diffconvert');
run_and_check('mkdir packages');

echo "Create list of changed files from git repository for version $fullVersion.\n";

/*
 * Here we force add every top-level directory and file in our diff archive, even if they haven't changed.
 * This allows us to install these files from the Extension Manager.
 * So we add the index file for each top-level directory.
 * Note: If we add new top-level directories or files, be sure to include them here.
 */
$filesArray = [
    "administrator/index.php\n" => true,
    "api/index.php\n"           => true,
    "cache/index.html\n"        => true,
    "cli/index.html\n"          => true,
    "components/index.html\n"   => true,
    "images/index.html\n"       => true,
    "includes/index.html\n"     => true,
    "language/index.html\n"     => true,
    "layouts/index.html\n"      => true,
    "libraries/index.html\n"    => true,
    "media/index.html\n"        => true,
    "modules/index.html\n"      => true,
    "plugins/index.html\n"      => true,
    "templates/index.html\n"    => true,
    "tmp/index.html\n"          => true,
    "htaccess.txt\n"            => true,
    "index.php\n"               => true,
    "LICENSE.txt\n"             => true,
    "README.txt\n"              => true,
    "robots.txt.dist\n"         => true,
    "web.config.txt\n"          => true,
];

/*
 * Here we set the files/folders which should not be packaged at any time
 * These paths are from the repository root without the leading slash
 * Because this is a fresh copy from a git tag, local environment files may be ignored
 */
$doNotPackage = [
    '.appveyor.yml',
    '.drone.yml',
    '.editorconfig',
    '.github',
    '.gitignore',
    '.phan',
    '.php-cs-fixer.dist.php',
    'acceptance.suite.yml',
    // Media Manager Node Assets
    'administrator/components/com_media/resources',
    'appveyor-phpunit.xml',
    'build',
    'build.xml',
    'CODE_OF_CONDUCT.md',
    'composer.json',
    'composer.lock',
    'crowdin.yml',
    'cypress.config.dist.mjs',
    'package-lock.json',
    'package.json',
    'phpunit-pgsql.xml.dist',
    'phpstan.neon',
    'phpunit.xml.dist',
    'plugins/sampledata/testing/language/en-GB/en-GB.plg_sampledata_testing.ini',
    'plugins/sampledata/testing/language/en-GB/en-GB.plg_sampledata_testing.sys.ini',
    'plugins/sampledata/testing/testing.php',
    'plugins/sampledata/testing/testing.xml',
    'README.md',
    'renovate.json',
    'ruleset.xml',
    'tests',
];

/*
 * Here we set the files/folders which should not be packaged with patch packages only
 * These paths are from the repository root without the leading slash
 */
$doNotPatch = [
    'administrator/cache',
    'administrator/logs',
    'images',
    'installation',
];

/*
 * This array will contain the checksums for all files which are created by this script.
 * This is an associative array with the following structure:
 * array(
 *   'filename' => array(
 *     'type1' => 'hash',
 *     'type2' => 'hash',
 *   ),
 * )
 */
$checksums = [];

// For the packages, replace spaces in stability (RC) with underscores
$packageStability = str_replace(' ', '_', Version::DEV_STATUS);

if ($debugBuild) {
    $packageStability .= '-Debug';
}

// Delete the files and folders we exclude from the packages (tests, docs, build, etc.).
echo "Delete folders not included in packages.\n";

if (!$debugBuild) {
    foreach ($doNotPackage as $removeFile) {
        run_and_check('rm -rf ' . $time . '/' . $removeFile);
    }
}

// Count down starting with the latest release and add diff files to this array
for ($num = $release - 1; $num >= 0; $num--) {
    if (!$buildPatchPackages) {
        echo "Disabled creating patch package for $num per flag.\n";
        continue;
    }

    echo "Create version $num update packages.\n";

    // Here we get a list of all files that have changed between the two references ($previousTag and $remote) and save in diffdocs
    $previousTag = $version . '.' . $num;
    $command     = $systemGit . ' diff tags/' . $previousTag . ' ' . $remote . ' --name-status > diffdocs/' . $version . '.' . $num;

    run_and_check($command);

    // $filesArray will hold the array of files to include in diff package
    $deletedFiles = [];
    $files        = file('diffdocs/' . $version . '.' . $num);

    // Loop through and add all files except: tests, installation, build, .git, .travis, travis, phpunit, .md, or images
    foreach ($files as $file) {
        if (substr($file, 0, 1) === 'R') {
            $fileName = substr($file, strrpos($file, "\t") + 1);
        } else {
            $fileName = substr($file, 2);
        }

        $folderPath             = explode('/', $fileName);
        $baseFolderName         = $folderPath[0];
        $doNotPackageFile       = \in_array(trim($fileName), $doNotPackage);
        $doNotPatchFile         = \in_array(trim($fileName), $doNotPatch);
        $doNotPackageBaseFolder = \in_array($baseFolderName, $doNotPackage);
        $doNotPatchBaseFolder   = \in_array($baseFolderName, $doNotPatch);
        $dirtyHackForMediaCheck = false;

        // The raw files for the vue files are not packaged but are not a top level directory so aren't handled by the
        // above checks. This is dirty but a fairly performant fix for now until we can come up with something better.
        if (\count($folderPath) >= 4) {
            $fullPath               = [$folderPath[0] . '/' . $folderPath[1] . '/' . $folderPath[2] . '/' . $folderPath[3]];
            $dirtyHackForMediaCheck = \in_array('administrator/components/com_media/resources', $fullPath);
        }


        if (!$debugBuild && ($dirtyHackForMediaCheck || $doNotPackageFile || $doNotPatchFile || $doNotPackageBaseFolder || $doNotPatchBaseFolder)) {
            continue;
        }

        // Act on the file based on the action
        switch (substr($file, 0, 1)) {
            // This is a new case with git 2.9 to handle renamed files
            case 'R':
                // Explode the file on the tab character; key 0 is the action (rename), key 1 is the old filename, and key 2 is the new filename
                $renamedFileData = explode("\t", $file);

                // Add the new file for packaging
                $filesArray[$renamedFileData[2]] = true;

                // And flag the old file as deleted
                $deletedFiles[] = $renamedFileData[1];

                break;

            case 'D':
                // Deleted files
                $deletedFiles[] = $fileName;

                break;

            default:
                // Regular additions and modifications
                $filesArray[$fileName] = true;

                break;
        }
    }

    // Write the file list to a text file.
    $filePut = array_keys($filesArray);
    sort($filePut);
    file_put_contents('diffconvert/' . $version . '.' . $num, implode('', $filePut));
    file_put_contents('diffconvert/' . $version . '.' . $num . '-deleted', $deletedFiles);

    // Only create archives for 0 and most recent versions. Skip other update versions.
    if ($num != 0 && ($num != $release - 1)) {
        echo "Skipping patch archive for version $version.$num\n";

        continue;
    }

    $fromName = $num == 0 ? 'x' : $num;

    // Create the diff archive packages using the file name list.
    if (!$excludeZip) {
        $packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.zip';
        echo "Building " . $packageName . "... ";
        $command = "cd {$time} && zip -q ../packages/{$packageName} -@ < ../diffconvert/{$version}.{$num}";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeGzip) {
        $packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.tar.gz';
        $command     = "tar --create --gzip --no-recursion --directory {$time} --file packages/{$packageName} --files-from diffconvert/{$version}.{$num}";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeBzip2) {
        $packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.tar.bz2';
        $command     = "tar --create --bzip2 --no-recursion --directory {$time} --file packages/{$packageName} --files-from diffconvert/{$version}.{$num}";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeZstd) {
        $packageName = 'Joomla_' . $version . '.' . $fromName . '_to_' . $fullVersion . '-' . $packageStability . '-Patch_Package.tar.zst';
        $command     = "tar --create --no-recursion --directory {$time} --files-from diffconvert/{$version}.{$num} | zstd --ultra -22 -o packages/{$packageName}";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }
}

echo "Build full package files.\n";
chdir($time);

// Create full archive packages.
if (!$excludeZip) {
    $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.zip';
    $command     = "zip -qr ../packages/{$packageName} *";
    build_and_check($packageName, $command);
    $checksums[$packageName] = [];
}

if (!$excludeGzip) {
    $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.tar.gz';
    $command     = "tar --create --gzip --file ../packages/{$packageName} *";
    build_and_check($packageName, $command);
    $checksums[$packageName] = [];
}

if (!$excludeBzip2) {
    $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.tar.bz2';
    $command     = "tar --create --bzip2 --file ../packages/{$packageName} *";
    build_and_check($packageName, $command);
    $checksums[$packageName] = [];
}

if (!$excludeZstd) {
    $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Full_Package.tar.zst';
    $command     = "tar --create * | zstd --ultra -22 -o ../packages/{$packageName}";
    build_and_check($packageName, $command);
    $checksums[$packageName] = [];
}

// Create full update file without the default logs directory, installation folder, or sample images.
if (!$debugBuild) {
    echo "Build full update package.\n";
    run_and_check('rm -r administrator/logs');
    run_and_check('rm -r installation');
    run_and_check('rm -r images/banners');
    run_and_check('rm -r images/headers');
    run_and_check('rm -r images/sampledata');
    run_and_check('rm images/joomla_black.png');
    run_and_check('rm images/powered_by.png');

    if (!$excludeZip) {
        $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.zip';
        $command     = "zip -qr ../packages/{$packageName} *";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeGzip) {
        $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.tar.gz';
        $command     = "tar --create --gzip --file ../packages/{$packageName} *";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeBzip2) {
        $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.tar.bz2';
        $command     = "tar --create --bzip2 --file ../packages/{$packageName} *";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }

    if (!$excludeZstd) {
        $packageName = 'Joomla_' . $fullVersion . '-' . $packageStability . '-Update_Package.tar.zst';
        $command     = "tar --create * | zstd --ultra -22 -o ../packages/{$packageName}";
        build_and_check($packageName, $command);
        $checksums[$packageName] = [];
    }
}

chdir('..');

// This is only needed when we release a version
if ($includeExtraTextfiles) {
    foreach (array_keys($checksums) as $packageName) {
        echo "Generating checksums for $packageName\n";

        foreach (['sha256', 'sha384', 'sha512'] as $hash) {
            if (file_exists('packages/' . $packageName)) {
                $checksums[$packageName][$hash] = hash_file($hash, 'packages/' . $packageName);
            } else {
                echo "Package $packageName not found in build directories\n";
            }
        }
    }

    echo "Generating checksums files\n";

    $checksumsContent       = '';
    $checksumsContentUpdate = '';

    foreach ($checksums as $packageName => $packageHashes) {
        $checksumsContent .= "Filename: $packageName\n";

        foreach ($packageHashes as $hashType => $hash) {
            $checksumsContent .= "$hashType: $hash\n";
            if (strpos($packageName, 'Update_Package.zip') !== false) {
                $checksumsContentUpdate .= "<$hashType>$hash</$hashType>\n";
            }
        }

        $checksumsContent .= "\n";
    }

    file_put_contents('checksums.txt', $checksumsContent);
    file_put_contents('checksums_update.txt', $checksumsContentUpdate);

    echo "Generating github_release.txt file\n";

    $githubContent = [];
    $releaseText   = [
        'FULL'    => 'New Joomla! Installations ',
        'POINT'   => 'Update from Joomla! ' . $version . '.' . $previousRelease . ' ',
        'MINOR'   => 'Update from Joomla! ' . $version . '.x ',
        'UPGRADE' => 'Update from Joomla! 3.10 ',
    ];

    $githubLink = 'https://github.com/joomla/joomla-cms/releases/download/' . $tagVersion . '/';

    foreach ($checksums as $packageName => $packageHashes) {
        $type = '';

        if (strpos($packageName, 'Full_Package') !== false) {
            $type = 'FULL';
        } elseif (strpos($packageName, 'Patch_Package') !== false) {
            if (strpos($packageName, '.x_to') !== false) {
                $type = 'MINOR';
            } else {
                $type = 'POINT';
            }
        } elseif (strpos($packageName, 'Update_Package') !== false) {
            $type = 'UPGRADE';
        }

        $githubContent[$type][$packageName] = $packageHashes;
    }

    ob_start();
    require __DIR__ . '/layouts/github.php';
    $githubText = ob_get_clean();

    file_put_contents('github_release.txt', $githubText);
}

echo "Build of version $fullVersion complete!\n";
