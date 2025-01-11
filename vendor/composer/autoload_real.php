<?php

// autoload_real.php @generated by Composer

class ComposerAutoloaderInitebe9e136daba7d28d0d5f2b9b3542f33
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

        require __DIR__ . '/platform_check.php';

        spl_autoload_register(array('ComposerAutoloaderInitebe9e136daba7d28d0d5f2b9b3542f33', 'loadClassLoader'), true, true);
        self::$loader = $loader = new \Composer\Autoload\ClassLoader(\dirname(__DIR__));
        spl_autoload_unregister(array('ComposerAutoloaderInitebe9e136daba7d28d0d5f2b9b3542f33', 'loadClassLoader'));

        require __DIR__ . '/autoload_static.php';
        call_user_func(\Composer\Autoload\ComposerStaticInitebe9e136daba7d28d0d5f2b9b3542f33::getInitializer($loader));

        $loader->register(true);

        return $loader;
    }
}
