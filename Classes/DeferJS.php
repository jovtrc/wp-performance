<?php

declare(strict_types=1);

/**
 * Class responsible to defer all JS that is not prioritary to load
 * @author João Carvalho <oi@joaocarvalho.cc>
 */
class DeferJS
{
    public function __construct()
    {
        add_action('style_loader_tag', [$this, 'deferJs'], LoadPriority::$defaultPriority, 3);
    }

    public function deferJs(String $tag, String $handle, String $src): String
    {
        $mainScript = strpos($handle, 'main-'); // Check if the script is main

        if (!is_admin() && !$mainScript) {
            $tag = str_replace("<script type='text/javascript' src", "<script defer type='text/javascript' src", $tag);
        }

        return $tag;
    }
}
