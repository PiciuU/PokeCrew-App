<?php

namespace Framework\View\Compilers\Traits;

use Framework\View\Factory;

/**
 * Trait CompileComponents
 *
 * This trait provides methods to compile Vision components in view templates.
 *
 * @package Framework\View\Compilers\Traits
 */
trait CompileComponents
{
    /**
     * Compile Vision components in the given content.
     *
     * @param string $content The content to be compiled.
     * @return string The compiled content.
     */
    protected function compileComponents($content)
    {
        $patterns = [
            '/@component\s?\(\s*(.+?)\s*\);?/' => '%s',
        ];

        foreach ($patterns as $pattern => $replacement) {
            $callback = function ($matches) use ($replacement) {
                $whitespace = empty($matches[3]) ? '' : $matches[3] . $matches[3];

                return sprintf($replacement, "<?php echo view('$matches[1]'); ?>") . $whitespace;
            };

            $content = preg_replace_callback($pattern, $callback, $content);
        }

        return $content;
    }
}