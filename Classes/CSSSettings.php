<?php
declare(strict_types=1);

/**
 * Class responsible to minify and defer CSS files
 * @author João Carvalho <oi@joaocarvalho.cc>
 */
class CSSSettings
{
    public function __construct()
    {
        add_action('wp_head', [$this, 'setCssOnHead'], LoadPriority::$lowPriority);
        add_action('style_loader_tag', [$this, 'deferCss'], LoadPriority::$ultraLowPriority, 3);

        // Exclusive for WP VIP
        add_action('css_do_concat', [$this, 'disableCssConcat'], LoadPriority::$defaultPriority);
    }

    public function disableCssConcat(): Bool
    {
        return false;
    }

    public function deferCss(String $tag, String $handle, String $src): String
    {
        $mainStyle = strpos($handle, 'main-'); // Check if the css is main

        if (!is_admin() && !$mainStyle) {
            $tag = str_replace("<link rel='stylesheet'", "<noscript><link rel='stylesheet' href='" . $src . "'></noscript>" . PHP_EOL . "<link rel='preload' as='style' onload='this.onload=null;this.rel=\"stylesheet\"'", $tag);
        }

        return $tag;
    }

    public function setCssOnHead(): void
    {
        $minifier = new Minifier;

        $styles = apply_filters('css_on_head', []);
        $minifiedCss = '';

        foreach ($styles as $key => $style) {
            $styleContent = $minifier->requireToVar($style);
            $styleContent = $minifier->minifyCSS($styleContent);
            $minifiedCss = $minifiedCss . $styleContent;
        }

        echo '<style>';
        echo $minifiedCss;
        echo '</style>';
    }
}
