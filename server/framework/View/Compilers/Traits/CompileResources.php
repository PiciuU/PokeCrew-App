<?php

namespace Framework\View\Compilers\Traits;

use Exception;

/**
 * Trait CompileResources
 *
 * This trait provides methods for compiling resources in views.
 *
 * @package Framework\View\Compilers\Traits
 */
trait CompileResources
{
    /**
     * Compile resources in the given content.
     *
     * @param string $content The content to be compiled.
     * @return string The compiled content.
     */
    protected function CompileResources($content)
    {
        $patterns = [
            '/@resource\s?\(\s*(.+?)\s*\);?/' => '%s',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $callback = function ($matches) use ($replacement) {
                $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];
                $file = $matches[1];

                try {
                    $wrapped = $this->minify($this->fs->get(resource_path()."/".$file));
                } catch(Exception $e) {
                    $wrapped = "/* ".$e->getMessage()." */";
                }
                return sprintf($replacement, $wrapped) . $whitespace;
            };

            $content = preg_replace_callback($pattern, $callback, $content);
        }

        return $content;
    }

    /**
     * Minify the given content by removing comments and whitespaces resulting in a minified version.
     *
     * @param string $content The original content to minify.
     * @return string The minified content.
     */
    private function minify($content)
    {
        $content = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $content);
        $content = preg_replace('/\s*([{}|:;,])\s+/', '$1', $content);
        $content = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '',$content);

        return $content;
    }
}