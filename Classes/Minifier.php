<?php

declare(strict_types=1);

/**
 * Main class responsible to parse HTML and CSS and return them minified
 * @author João Carvalho <oi@joaocarvalho.cc>
 */
class Minifier
{
    protected $compress_css = true;
    protected $compress_js = true;
    protected $info_comment = true;
    protected $remove_comments = true;
    protected $html;

    public function __construct($html = null)
    {
        if (!empty($html)) {
            $this->parseHtml($html);
        }
    }

    public function __toString()
    {
        return $this->html;
    }

    public function parseHTML(String $html): void
    {
        $this->html = $this->minifyHTML($html);

        if ($this->info_comment) {
            $this->html .= "\n" . $this->bottomComment($html, $this->html);
        }
    }

    protected function minifyHTML(String $html): String
    {
        $pattern = '/<(?<script>script).*?<\/script\s*>|<(?<style>style).*?<\/style\s*>|<!(?<comment>--).*?-->|<(?<tag>[\/\w.:-]*)(?:".*?"|\'.*?\'|[^\'">]+)*>|(?<text>((<[^!\/\w.:-])?[^<]*)+)|/si';
        preg_match_all($pattern, $html, $matches, PREG_SET_ORDER);
        $overriding = false;
        $raw_tag = false;
        $html = '';

        if (is_array($matches)) {
            foreach ($matches as $token) {
                $tag = (isset($token['tag'])) ? strtolower($token['tag']) : null;
                $content = $token[0];
                if (is_null($tag)) {
                    if (!empty($token['script'])) {
                        $strip = $this->compress_js;
                    } else if (!empty($token['style'])) {
                        $strip = $this->compress_css;
                    } else if ($content == '<!--wp-html-compression no compression-->') {
                        $overriding = !$overriding;
                        continue;
                    } else if ($this->remove_comments) {
                        if (!$overriding && $raw_tag != 'textarea') {
                            $content = preg_replace('/<!--(?!\s*(?:\[if [^\]]+]|<!|>))(?:(?!-->).)*-->/s', '', $content);
                        }
                    }
                } else {
                    if ($tag == 'pre' || $tag == 'textarea') {
                        $raw_tag = $tag;
                    } else if ($tag == '/pre' || $tag == '/textarea') {
                        $raw_tag = false;
                    } else {
                        if ($raw_tag || $overriding) {
                            $strip = false;
                        } else {
                            $strip = true;
                            $content = preg_replace('/(\s+)(\w++(?<!\baction|\balt|\bcontent|\bsrc)="")/', '$1', $content);
                            $content = str_replace(' />', '/>', $content);
                        }
                    }
                }
                if ($strip) {
                    $content = $this->removeWhiteSpace($content);
                }
                $html .= $content;
            }
        }
        return $html;
    }

    public function minifyCSS(String $input): String|null
    {
        if (trim($input) === "") return $input;
        return preg_replace(
            array(
                // Remove comment(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
                // Remove unused white-space(s)
                '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
                // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
                '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
                // Replace `:0 0 0 0` with `:0`
                '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
                // Replace `background-position:0` with `background-position:0 0`
                '#(background-position):0(?=[;\}])#si',
                // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
                '#(?<=[\s:,\-])0+\.(\d+)#s',
                // Minify string value
                '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
                '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
                // Minify HEX color code
                '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
                // Replace `(border|outline):none` with `(border|outline):0`
                '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
                // Remove empty selector(s)
                '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
            ),
            array(
                '$1',
                '$1$2$3$4$5$6$7',
                '$1',
                ':0',
                '$1:0 0',
                '.$1',
                '$1$3',
                '$1$2$4$5',
                '$1$2$3',
                '$1:0',
                '$1$2'
            ),
            $input
        );
    }

    public function requireToVar(String $file): String|false
    {
        ob_start();
        require($file);
        return ob_get_clean();
    }

    protected function bottomComment(String $rawHtml, String $compressedHtml): String
    {
        $rawHtml = strlen($rawHtml);
        $compressedHtml = strlen($compressedHtml);
        $savings = ($rawHtml - $compressedHtml) / $rawHtml * 100;
        $savings = round($savings, 2);
        return '<!-- HTML minificado, economizamos ' . $savings . '%. De ' . $rawHtml . ' bytes, para ' . $compressedHtml . ' bytes-->';
    }

    protected function removeWhiteSpace(String $str): String
    {
        $str = str_replace("\t", ' ', $str);
        $str = str_replace("\n",  '', $str);
        $str = str_replace("\r",  '', $str);
        while (stristr($str, '  ')) {
            $str = str_replace('  ', ' ', $str);
        }
        return $str;
    }
}
