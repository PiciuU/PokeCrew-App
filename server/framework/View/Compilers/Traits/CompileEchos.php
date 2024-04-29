<?php

namespace Framework\View\Compilers\Traits;

/**
 * Trait CompileEchos
 *
 * This trait provides methods for compiling echo statements in templates.
 *
 * @package Framework\View\Compilers\Traits
 */
trait CompileEchos
{
    /**
     * The tags used to denote content in the template.
     *
     * @var array
     */
    protected $contentTags = ['{{', '}}'];

    /**
     * The format used for echoing content in the compiled template.
     *
     * @var string
     */
    protected $echoFormat = 'e(%s)';

    /**
     * Compile regular echo statements in the given content.
     *
     * @param string $content The content to be compiled.
     * @return string The compiled content.
     */
    protected function compileRegularEchos($content)
    {
        $pattern = sprintf('/(@)?%s\s*(.+?)\s*%s(\r?\n)?/s', $this->contentTags[0], $this->contentTags[1]);

        $callback = function ($matches) {
            $whitespace = empty($matches[3]) ? '' : $matches[3];
            $wrapped = sprintf($this->echoFormat, $matches[2]);
            return $matches[1] ? substr($matches[0], 1) : "<?php echo {$wrapped}; ?>".$whitespace;
        };

        return preg_replace_callback($pattern, $callback, $content);
    }
}