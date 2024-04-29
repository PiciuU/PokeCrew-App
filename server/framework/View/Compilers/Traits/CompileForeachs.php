<?php

namespace Framework\View\Compilers\Traits;

/**
 * Trait CompileForeachs
 *
 * This trait provides methods for compiling foreach statements in templates.
 *
 * @package Framework\View\Compilers\Traits
 */
trait CompileForeachs
{
    /**
     * Compile foreach statements in the given content.
     *
     * @param string $content The content to be compiled.
     * @return string The compiled content.
     */
    protected function compileForeachs($content)
    {
        $patterns = [
            '/@foreach\s?\(\s*(.+?)\s*\)(\r\n?|\n)/' => '<?php foreach(%s): ?>',
            '/@endforeach/' => '<?php endforeach; ?>',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $callback = function ($matches) use ($replacement) {
                $wrapped = $matches[1] ?? '';
                $whitespace = empty($matches[2]) ? '' : $matches[2];
                return sprintf($replacement, $wrapped) . $whitespace;
            };

            $content = preg_replace_callback($pattern, $callback, $content);
        }

        return $content;
    }
}