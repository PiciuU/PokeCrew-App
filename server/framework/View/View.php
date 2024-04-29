<?php

namespace Framework\View;

/**
 * Class View
 *
 * The View class represents a view instance, encapsulating the rendering of a view file.
 *
 * @package Framework\View
 */
class View
{
    /**
     * The view factory instance.
     *
     * @var \Framework\View\Factory
     */
    protected $factory;

    /**
     * The view engine instance.
     *
     * @var mixed
     */
    protected $engine;

    /**
     * The name of the view.
     *
     * @var string
     */
    protected $view;

    /**
     * The path to the view file.
     *
     * @var string
     */
    protected $path;

    /**
     * The view data.
     *
     * @var array
     */
    protected $data;

    /**
     * Create a new view instance.
     *
     * @param  \Framework\View\Factory  $factory The view factory instance.
     * @param  mixed  $engine The view engine instance.
     * @param  string  $view The name of the view.
     * @param  string  $path The path to the view file.
     * @param  array  $data The view data.
     */
    public function __construct(Factory $factory, $engine, $view, $path, $data = [])
    {
        $this->factory = $factory;
        $this->engine = $engine;
        $this->view = $view;
        $this->path = $path;

        $this->data = is_array($data) ? $data : (array) $data;
    }

    /**
     * Render the view to a string.
     *
     * @return string The rendered view content.
     *
     * @throws \Throwable If an error occurs during rendering.
     */
    public function render()
    {
        try {
            return $this->getContents();
        }
        catch (Throwable $e) {
            throw $e;
        }
    }

    /**
     * Get the contents of the view file.
     *
     * @return string The view contents.
     */
    protected function getContents()
    {
        return $this->engine->get($this->path, $this->data);
    }

    /**
     * Get the string contents of the view.
     *
     * @return string The rendered view content.
     */
    public function toHtml()
    {
        return $this->render();
    }

    /**
     * Get the string contents of the view when casting to a string.
     *
     * @return string The rendered view content.
     */
    public function __toString()
    {
        return $this->render();
    }
}