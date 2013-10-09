<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @package Piwik
 */

define('PIWIK_DOCUMENT_ROOT', __DIR__ . '/piwik');
define('PIWIK_USER_PATH', PIWIK_DOCUMENT_ROOT);
define('PIWIK_INCLUDE_PATH', PIWIK_DOCUMENT_ROOT);

require 'vendor/autoload.php';
require_once PIWIK_INCLUDE_PATH . '/core/Loader.php';
require 'vendor/nikic/php-parser/lib/bootstrap.php';
require 'hooks/Hooks.php';
ini_set('xdebug.max_nesting_level', 2000);

$target   = __DIR__ . '/docs/Hooks.md';

$files    = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(PIWIK_DOCUMENT_ROOT));
$phpFiles = new RegexIterator($files, '/piwik\/(core|plugins)(.*)\.php$/');

try {

    $hooks = new Hooks();
    $view  = array('hooks' => array());

    foreach ($phpFiles as $phpFile) {
        $relativeFileName = str_replace(PIWIK_DOCUMENT_ROOT, '', $phpFile);
        $foundHooks = $hooks->searchForHooksInFile($relativeFileName, $phpFile);

        if (!empty($foundHooks)) {
            foreach ($foundHooks as $hook) {
                $view['hooks'][] = $hook;
            }
        }
    }

    $view['hooks'] = $hooks->sortHooksByName($view['hooks']);
    $view['hooks'] = $hooks->addUsages($view['hooks']);

    $hooks->generateDocumentation($view, $target);

} catch (Exception $e) {
    echo 'Parse Error: ', $e->getMessage();
}