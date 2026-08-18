<?php
/**
 * Smoke checks for Cloudron masterdata rewrite behavior.
 */

require_once __DIR__ . '/../config/config.php';

$failures = [];
$htaccessPath = PUBLIC_PATH . '/.htaccess';
$masterdataPath = PUBLIC_PATH . '/masterdata';

if (!is_readable($htaccessPath)) {
    $failures[] = "Cannot read .htaccess at $htaccessPath";
    $htaccess = '';
} else {
    $htaccess = file_get_contents($htaccessPath);
}

/**
 * Assert that a string exists in .htaccess.
 */
$expectContains = static function (string $needle) use ($htaccess, &$failures): void {
    if (strpos($htaccess, $needle) === false) {
        $failures[] = "Missing .htaccess directive: $needle";
    }
};

/**
 * Assert that one string appears before another string in .htaccess.
 */
$expectBefore = static function (string $first, string $second) use ($htaccess, &$failures): void {
    $firstPos = strpos($htaccess, $first);
    $secondPos = strpos($htaccess, $second);

    if ($firstPos === false || $secondPos === false) {
        $failures[] = "Cannot compare missing .htaccess directives";
        return;
    }

    if ($firstPos > $secondPos) {
        $failures[] = "Expected '$first' before '$second'";
    }
};

/**
 * Assert that a legacy wrapper rewrite target was removed.
 */
$expectMissing = static function (string $needle) use ($htaccess, &$failures): void {
    if (strpos($htaccess, $needle) !== false) {
        $failures[] = "Legacy wrapper rewrite still present: $needle";
    }
};

$adapterRule = 'RewriteRule ^masterdata(/.*)?$ index.php [L,QSA]';
$frontControllerGuard = 'RewriteCond %{REQUEST_FILENAME} !-d';

$expectContains('RewriteCond %{REQUEST_URI} !/index\.php($|\?)');
$expectContains($adapterRule);
$expectBefore($adapterRule, $frontControllerGuard);
$expectMissing('masterdata/$1/$2/index.php');

$wrapperActions = [];
$wrapperDirectories = [];

if (!is_dir($masterdataPath)) {
    $failures[] = "Missing masterdata directory at $masterdataPath";
} else {
    $wrapperDirectories = glob($masterdataPath . '/*', GLOB_ONLYDIR);

    if ($wrapperDirectories === false) {
        $failures[] = "Failed to read masterdata wrapper directories at $masterdataPath";
        $wrapperDirectories = [];
    }
}

foreach ($wrapperDirectories as $directory) {
    $action = basename($directory);

    if ($action !== 'index.php') {
        $wrapperActions[] = $action;
    }
}

sort($wrapperActions);
$expectedActions = ['create', 'delete', 'edit', 'list', 'store', 'toggleActive', 'update'];

if ($wrapperActions !== $expectedActions) {
    $failures[] = 'Unexpected masterdata wrapper action directory set';
}

if (!empty($failures)) {
    fwrite(STDERR, implode(PHP_EOL, $failures) . PHP_EOL);
    exit(1);
}

echo "Cloudron masterdata rewrite smoke checks passed." . PHP_EOL;
