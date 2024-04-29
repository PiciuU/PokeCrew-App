<?php

namespace Framework\View\Compilers;

/**
 * Class VisionCompiler
 *
 * The VisionCompiler class extends the base Compiler class and provides functionality to compile Vision template files.
 * It uses traits to handle specific compilation tasks for echoing, if statements, and foreach loops.
 *
 * @package Framework\View\Compilers
 */
class VisionCompiler extends Compiler
{
    use Traits\CompileResources,
        Traits\CompileComponents,
        Traits\CompileEchos,
        Traits\CompileIfs,
        Traits\CompileForeachs;

    /**
     * The path to the file being compiled.
     *
     * @var string
     */
    protected $path;

    /**
     * Compile the specified Vision template file.
     *
     * @param string|null $path The path to the Vision template file.
     * @return void
     */
    public function compile($path = null)
    {
        if ($path) {
            $this->setPath($path);
        }

        $contents = $this->compileContent($this->fs->get($this->getPath()));

        if (!empty($this->getPath())) {
            $contents = $this->appendFilePath($contents);
        }

        $compiledPath = $this->getCompiledPath($this->getPath());

        $this->fs->put($compiledPath, $contents);
    }

    /**
     * Append the file path and compilation timestamp to the compiled content.
     *
     * @param string $contents The compiled content.
     * @return string The content with appended file path and compilation timestamp.
     */
    protected function appendFilePath($contents)
    {
        $tokens = $this->getOpenAndClosingPhpTokens($contents);

        if ($tokens->isNotEmpty() && $tokens->last() !== T_CLOSE_TAG) {
            $contents .= ' ?>';
        }

        return $contents."<?php /**PATH {$this->getPath()} ENDPATH**/ /**COMPILED Compiled at: ".date('Y-m-d H:i:s')." ENDCOMPILED**/ ?>";
    }

    /**
     * Get open and closing PHP tokens from the given content.
     *
     * @param string $contents The content to extract tokens from.
     * @return \Framework\Support\Collections\Collection The collection of PHP tokens.
     */
    protected function getOpenAndClosingPhpTokens($contents)
    {
        return collect(token_get_all($contents))
            ->pluck(0)
            ->filter(function ($token) {
                return in_array($token, [T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG]);
            });
    }

    /**
     * Compile the content by applying echo, if, and foreach compilation.
     *
     * @param string $content The content to be compiled.
     * @return string The compiled content.
     */
    public function compileContent($content)
    {
        $content = $this->compileResources($content);
        $content = $this->compileComponents($content);
        $content = $this->compileRegularEchos($content);
        $content = $this->compileIfs($content);
        $content = $this->compileForeachs($content);

        return $content;
    }

    /**
     * Get the path currently being compiled.
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set the path currently being compiled.
     *
     * @param  string  $path
     * @return void
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

}