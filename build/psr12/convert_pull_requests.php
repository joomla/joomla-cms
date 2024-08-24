<?php

/**
 * This script converts Joomla Github Pull Requests to psr-12 coding standard
 *
 * @package        Joomla.Build
 * @copyright      (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set defaults
$scriptRoot        = __DIR__;
$repoRoot          = false;
$prNumber          = false;
$createPullRequest = false;
$php               = 'php';
$git               = 'git';
$gh                = 'gh';
$checkPath         = false;
$ghRepo            = 'joomla/joomla-cms';
$baseBranches      = '4.2-dev'; // '4.1-dev,4.2-dev,4.3-dev'; We only check for 4.2-dev

$script = array_shift($argv);

if (empty($argv)) {
    echo <<<TEXT
        Joomla! PSR-12 Converter
        =======================
        Usage:
            IMPORTANT THIS SCRIPT HAVE TO BE OUTSIDE OF THE REPOSITORY
            Best way to run this script is in a physical copy of the original
            The psr12_converter.php is expected to be in the same directory.

            php {$script} [--pr=<number>] --repo=<repository rootpath>

        Description:
            The converter converts all open pull requests on github
            to the PSR12 standard and pushes the changes back to github.

            Flow:
            * Load all pull requests from Github for the repository
              $ghRepo
            * Checkout each open pull request with the base branch matching
              $baseBranches
            * Merge into the checked out branch up to the psr12anchor tag
              which includes the conversion script.
            * Run the psr12_converter.php with the task "BRANCH"
            * Merge up to the psr12final tag with strategy "OURS"
            * Push the changes back to the PR or create a new PR if we
              don't have commit rights to the repository

            --pr:
              Only convert the given github pull request id.

            --repo:
              The path to the repository root.

        TEXT;
    die(1);
}

foreach ($argv as $arg) {
    if (substr($arg, 0, 2) === '--') {
        $argi = explode('=', $arg, 2);
        switch ($argi[0]) {
            case '--pr':
                $prNumber = $argi[1];
                break;
            case '--repo':
                $repoRoot = $argi[1];
                break;
        }
    } else {
        $checkPath = $arg;
        break;
    }
}

if (!$repoRoot) {
    die('You have to set the repository root! (--repo)');
}

$cmd          = $git . ' -C "' . $scriptRoot . '" rev-parse --show-toplevel';
$output       = [];
$scriptInRepo = false;
$repoScript   = '';
exec($cmd, $output, $result);
if ($result === 0) {
    $scriptInRepo = true;
    $repoScript   = $output[0];
}

$cmd    = $git . ' -C "' . $repoRoot . '" rev-parse --show-toplevel';
$output = [];
exec($cmd, $output, $result);
if ($result !== 0) {
    die($repoRoot . ' is not a git repository.');
}

$repoRoot = $output[0];

if ($scriptInRepo && $repoRoot === $repoScript) {
    die($script . ' must be located outside of the git repository');
}

echo "Changing to working directory: " . $repoRoot . "\n";
chdir($repoRoot);

echo "Validate gh client...\n";
$cmd    = $gh;
$output = [];
exec($cmd, $output, $result);
if ($result !== 0) {
    die('Github cli client not found. Please install the client first (https://cli.github.com)');
}

echo "Validate gh authentication...\n";
$cmd = $gh . ' auth status';
passthru($cmd, $result);
if ($result !== 0) {
    die('Please login with the github cli client first. (gh auth login)');
}

$fieldList = [
    "number",
    "author",
    "baseRefName",
    "headRefName",
    "headRepository",
    "headRepositoryOwner",
    "isCrossRepository",
    "maintainerCanModify",
    "mergeStateStatus",
    "mergeable",
    "state",
    "title",
    "url",
    "labels",
];

$branches = 'base:' . implode(' base:', explode(',', $baseBranches));


if (!empty($prNumber)) {
    echo "Retrieving Pull Request " . $prNumber . "...\n";
    $cmd = $gh . ' pr view ' . $prNumber . ' --json ' . implode(',', $fieldList);
} else {
    echo "Retrieving Pull Request list...\n";
    $cmd = $gh . ' pr list --limit 1000 --json ' . implode(',', $fieldList) . ' --search "is:pr is:open -label:psr12 ' . $branches . '"';
}

$output = [];
exec($cmd, $output, $result);
if ($result !== 0) {
    var_dump([$cmd, $output, $result]);
    die('Unable to retrieve PR list.');
}

$json = $output[0];

if (!empty($prNumber)) {
    $json = '[' . $json . ']';
}

$list = json_decode($json, true);

echo "\nFound " . count($list) . " pull request(s).\n";

foreach ($list as $pr) {
    echo "Checkout #" . $pr['number'] . "\n";

    $cmd    = $gh . ' pr checkout ' . $pr['url'] . ' --force -b psr12/merge/' . $pr['number'];
    $output = [];
    exec($cmd, $output, $result);
    if ($result !== 0) {
        var_dump([$cmd, $output, $result]);
        die('Unable to checkout pr #' . $pr['number']);
    }

    echo "Upmerge to psr12anchor\n";

    $cmd    = $git . ' merge psr12anchor';
    $output = [];
    exec($cmd, $output, $result);
    if ($result !== 0) {
        var_dump([$cmd, $output, $result]);
        echo 'Unable to upmerge to psr12anchor pr #' . $pr['number'] . "\n";
        echo "Abort merge...\n";
        $cmd    = $git . ' merge --abort';
        $output = [];
        exec($cmd, $output, $result);
        continue;
    }

    echo "Run PSR-12 converter script\n";

    $cmd = $git . ' diff --name-only psr12anchor..HEAD';
    $output = [];
    exec($cmd, $output, $result);
    if (count($output) > 500) {
        var_dump([$cmd, $output, $result]);
        echo 'Too many files changed between psr12anchor and HEAD pr #' . $pr['number'] ."\n";
        continue;
    }

    echo "Run PSR-12 converter script\n";

    $cmd = $php . ' ' . $scriptRoot . '/psr12_converter.php --task=branch --repo="' . $repoRoot . '"';

    passthru($cmd, $result);
    if ($result !== 0) {
        var_dump([$cmd, $result]);
        die('Unable to convert to psr-12 pr #' . $pr['number']);
    }

    echo "Upmerge to psr12final\n";

    $cmd    = $git . ' merge --strategy=ort --strategy-option=ours psr12final';
    $output = [];
    exec($cmd, $output, $result);
    if ($result !== 0) {
        echo "Upmerge with strategy ort failed using fallback ours\n";
        var_dump([$cmd, $result]);
        $cmd    = $git . ' merge --abort';
        $output = [];
        exec($cmd, $output, $result);

        $cmd    = $git . ' merge --strategy=ours psr12final';
        $output = [];
        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $result]);
            die('Unable to upmerge to psr-12 pr #' . $pr['number']);
        }
    }

    if (!$createPullRequest && $pr['maintainerCanModify'] === true) {
        echo "Push directly to PR branch\n";

        $cmd    = $git . ' push git@github.com:' . $pr['headRepositoryOwner']['login'] . '/' . $pr['headRepository']['name'] . '.git '
            . 'psr12/merge/' . $pr['number'] . ':' . str_replace('"', '\"', $pr['headRefName']);
        $output = [];

        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to directly push for pr #' . $pr['number']);
        }

        $cmd    = $gh . ' pr comment ' . $pr['url'] . ' --body "This pull requests has been automatically converted to the PSR-12 coding standard."';
        $output = [];
        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to create a comment for pr #' . $pr['number']);
        }
    } else {
        echo "Create pull request\n";

        $cmd    = $git . ' push --force -u github HEAD';
        $output = [];

        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to push to github for pr #' . $pr['number']);
        }

        $cmd    = $gh . ' pr create --title "PSR-12 conversion" --body "This pull requests converts the branch to the PSR-12 coding standard." '
            . '-R ' . $pr['headRepositoryOwner']['login'] . '/' . $pr['headRepository']['name'] . ' -B ' . str_replace('"', '\"', $pr['headRefName']);
        $output = [];
        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to create pull request for pr #' . $pr['number']);
        }

        $cmd    = $gh . ' pr comment ' . $pr['url']
            . ' --body "A new pull request has been created automatically to convert this PR to the PSR-12 coding standard.'
            . ' The pr can be found at ' . $output[0] . '"';
        $output = [];
        exec($cmd, $output, $result);
        if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to create a comment for pr #' . $pr['number']);
        }
    }

    // Set label
    echo "Set psr12 label\n";

    $cmd    = $gh . ' pr edit ' . $pr['url'] . ' --add-label psr12';
    $output = [];
    exec($cmd, $output, $result);
    if ($result !== 0) {
        var_dump([$cmd, $output, $result]);
        die('Unable to set psr12 label for pr #' . $pr['number']);
    }
}
