<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInit041073b86b42c2a828a11a90349a6e5e
{
    private static $loader;

    public static function loadClassLoader($class)
    {
        if ('Composer\Autoload\ClassLoader' === $class) {
            require __DIR__ . '/ClassLoader.php';
        }
    }

    /**
     * @return \Composer\Autoload\ClassLoader
     */
    public static function getLoader()
    {
        if (null !== self::$loader) {
            return self::$loader;
        }

        spl_autoload_register(array('ComposerAutoloaderInit041073b86b42c2a828a11a90349a6e5e', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInit041073b86b42c2a828a11a90349a6e5e', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInit041073b86b42c2a828a11a90349a6e5e::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
