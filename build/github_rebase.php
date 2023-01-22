<?php

/**
 * This script rebases Joomla Github Pull Requests to the target branch
 *
 * @package            Joomla.Build
 * @copyright          (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license            GNU General Public License version 2 or later; see LICENSE.txt
 */

// Set defaults
$scriptRoot   = __DIR__;
$prNumber     = false;
$php          = 'php';
$git          = 'git';
$gh           = 'gh';
$checkPath    = false;
$ghRepo       = 'joomla/joomla-cms';
$baseBranches = '4.1-dev';
$targetBranch = '4.2-dev';

$script = array_shift($argv);

if (empty($argv)) {
        echo <<<TEXT
        Joomla! Github Rebase script
        ============================
        Usage:
            php {$script} --base=4.1-dev[,...] --target=4.2-dev [--pr=<number>]

        Description:
            Rebase all open pull requests on github to the target branch.

            --base:
              The base branch of the pull request. Multiple branches can be separated by comma.

            --target:
              The target branch the pull request gets rebased to.

            --pr:
              Rebase only the given PR.

        TEXT;
        die(1);
}

foreach ($argv as $arg) {
    if (substr($arg, 0, 2) === '--') {
            $argi = explode('=', $arg, 2);
        switch ($argi[0]) {
            case '--base':
                $baseBranches = $argi[1];
                break;
            case '--target':
                    $targetBranch = $argi[1];
                break;
            case '--pr':
                    $prNumber = $argi[1];
                break;
        }
    } else {
            $checkPath = $arg;
            break;
    }
}

$cmd        = $git . ' -C "' . $scriptRoot . '" rev-parse --show-toplevel';
$output     = [];
$repoScript = '';
exec($cmd, $output, $result);
if ($result !== 0) {
        $repoScript = $output[0];
        die($script . ' must be located inside of the git repository');
}

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
        $cmd = $gh . ' pr list --limit 1000 --json ' . implode(',', $fieldList) . ' --search "is:pr is:open ' . $branches . '"';
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
        echo "Rebase #" . $pr['number'] . "\n";

        $cmd    = $gh . ' pr edit ' . $pr['url'] . ' --base ' . $targetBranch;
        $output = [];
        exec($cmd, $output, $result);
    if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to set target branch for pr #' . $pr['number']);
    }

        $cmd    = $gh . ' pr comment ' . $pr['url'] . ' --body "This pull request has been automatically rebased to ' . $targetBranch . '."';
        $output = [];
        exec($cmd, $output, $result);
    if ($result !== 0) {
            var_dump([$cmd, $output, $result]);
            die('Unable to create a comment for pr #' . $pr['number']);
    }
}
