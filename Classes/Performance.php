<?php

declare(strict_types=1);

/**
 * Class responsible to load the other classes
 * @author João Carvalho <oi@joaocarvalho.cc>
 */
class Performance
{
    public function __construct()
    {
        spl_autoload_extensions('.php');
        spl_autoload_register(array($this, 'autoLoader'));
        $this->loadClasses();
    }

    private function autoLoader($className)
    {
        $extension = spl_autoload_extensions();
        $file = __DIR__ . '//' . $className . $extension;

        $file = str_replace('\\', '/', $file);

        if (file_exists($file)) {
            require_once($file);
        }
    }

    private function loadClasses(): void
    {
        if (class_exists('CSSSettings')) {
            new CSSSettings();
        }
        if (class_exists('DeferJS')) {
            new DeferJS();
        }
        if (class_exists('LoadPriority')) {
            new LoadPriority();
        }
        if (class_exists('MinifyHTML')) {
            new MinifyHTML();
        }
    }
}
