<?php

declare(strict_types=1);

/**
 * Class responsible to minify the HTML content
 * @author João Carvalho <oi@joaocarvalho.cc>
 */
class MinifyHTML
{
    public function __construct()
    {
        add_action('get_header', [$this, 'compressHTML'], LoadPriority::$defaultPriority);
    }

    public function compressHTML(): void
    {
        ob_start([$this, 'startMinifyHTML']);
    }

    public function startMinifyHTML(String $html): Minifier
    {
        return new Minifier($html);
    }
}
