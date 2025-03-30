<?php
/**
 * Plugin Name: NicheClassify Framework
 */

defined('ABSPATH') || exit;

// ✅ Composer Autoloader (preferred if available)
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
}

// PSR-4 Autoloader fallback
spl_autoload_register(function ($class) {
    if (strpos($class, 'NicheClassify\\') === 0) {
        $path = plugin_dir_path(__FILE__) . 'includes/' . str_replace('NicheClassify\\', '', $class);
        $path = str_replace('\\', '/', $path) . '.php';
        if (file_exists($path)) require_once $path;
    }
});

// Initialize Plugin
use NicheClassify\Core\Plugin;
Plugin::init();
