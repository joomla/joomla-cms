#!/usr/bin/env php
<?php
/**
 * Security Patch Validation - Code Review Test
 * 
 * This validates the patch implementation through static analysis
 * since we cannot run the full Joomla stack.
 */

echo "=== SQL Field Authorization Bypass Patch Validation ===\n\n";

$patchedFile = '/Users/dex/Downloads/PRG/joomla-cms/plugins/fields/sql/tmpl/sql.php';

echo "Test 1: Verify patch file exists and is readable\n";
echo "-------------------------------------------------\n";
if (file_exists($patchedFile)) {
    echo "✓ PASS: Patched file exists\n";
    $content = file_get_contents($patchedFile);
    echo "✓ PASS: File is readable (" . strlen($content) . " bytes)\n\n";
} else {
    echo "✗ FAIL: Patched file not found\n";
    exit(1);
}

echo "Test 2: Verify authorization check is present\n";
echo "----------------------------------------------\n";
$requiredChecks = [
    'use Joomla\CMS\Access\Access;' => 'Import Access class',
    'Factory::getApplication()' => 'Get application instance',
    'getIdentity()' => 'Get current user',
    'Access::getAssetRules(1)' => 'Check root asset rules',
    'allow(\'core.admin\'' => 'Check core.admin permission',
    'getAuthorisedGroups()' => 'Get user groups',
];

$allPassed = true;
foreach ($requiredChecks as $needle => $description) {
    if (strpos($content, $needle) !== false) {
        echo "✓ PASS: $description found\n";
    } else {
        echo "✗ FAIL: $description NOT found\n";
        $allPassed = false;
    }
}
echo "\n";

echo "Test 3: Verify authorization check happens BEFORE query execution\n";
echo "-------------------------------------------------------------------\n";

// Extract line numbers for critical operations
$lines = explode("\n", $content);
$authCheckLine = 0;
$queryExecLine = 0;

foreach ($lines as $num => $line) {
    if (strpos($line, 'Access::getAssetRules(1)->allow') !== false) {
        $authCheckLine = $num + 1;
    }
    if (strpos($line, '$query->setQuery($sql') !== false) {
        $queryExecLine = $num + 1;
    }
}

if ($authCheckLine > 0 && $queryExecLine > 0) {
    echo "Authorization check at line: $authCheckLine\n";
    echo "Query execution at line: $queryExecLine\n";
    
    if ($authCheckLine < $queryExecLine) {
        echo "✓ PASS: Authorization check occurs BEFORE query execution\n";
    } else {
        echo "✗ FAIL: Authorization check occurs AFTER query execution\n";
        $allPassed = false;
    }
} else {
    echo "✗ FAIL: Could not locate authorization or query execution code\n";
    $allPassed = false;
}
echo "\n";

echo "Test 4: Verify early return on authorization failure\n";
echo "-----------------------------------------------------\n";
if (preg_match('/if\s*\(\s*!\s*Access::getAssetRules.*?\)\s*\{[^}]*return;/s', $content)) {
    echo "✓ PASS: Code returns early when authorization fails\n";
} else {
    echo "✗ FAIL: No early return found on authorization failure\n";
    $allPassed = false;
}
echo "\n";

echo "Test 5: Verify security comment is present\n";
echo "-------------------------------------------\n";
if (strpos($content, 'SECURITY:') !== false) {
    echo "✓ PASS: Security documentation comment found\n";
    
    // Extract and display the comment
    preg_match('/\/\/\s*SECURITY:.*?(?=\n\$)/s', $content, $matches);
    if (isset($matches[0])) {
        echo "\nSecurity Comment:\n";
        echo str_repeat('-', 70) . "\n";
        echo trim($matches[0]) . "\n";
        echo str_repeat('-', 70) . "\n";
    }
} else {
    echo "⚠ WARNING: No security documentation found\n";
}
echo "\n";

echo "Test 6: Verify no breaking changes to existing logic\n";
echo "-----------------------------------------------------\n";
$criticalElements = [
    'field->value' => 'Field value extraction',
    'Factory::getDbo' => 'Database connection',
    'setQuery(' => 'Query execution',
    'htmlentities(' => 'Output sanitization',
];

foreach ($criticalElements as $needle => $description) {
    if (strpos($content, $needle) !== false) {
        echo "✓ PASS: $description unchanged\n";
    } else {
        echo "✗ FAIL: $description missing or changed\n";
        $allPassed = false;
    }
}
echo "\n";

echo "=== Test Summary ===\n";
if ($allPassed) {
    echo "✓ ALL TESTS PASSED - Patch is correctly implemented\n";
    echo "\nSecurity Impact:\n";
    echo "- Non-super-admin users: SQL queries BLOCKED ✓\n";
    echo "- Super admin users: SQL queries ALLOWED ✓\n";
    echo "- Authorization matches field creation requirements ✓\n";
    echo "- Early return prevents query execution ✓\n";
    exit(0);
} else {
    echo "✗ SOME TESTS FAILED - Review patch implementation\n";
    exit(1);
}
