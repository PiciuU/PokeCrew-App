<?php

namespace Framework\Http\Traits;

use Framework\Support\Str;

/**
 * Trait HandleContentTypes
 *
 * The HandleContentTypes trait provides methods for handling content types in HTTP requests.
 * It includes functionality to determine if the request content type is JSON, if the request expects JSON,
 * if the request is an AJAX request, if it is a PJAX request, if it accepts any content type, and if it wants JSON.
 *
 * @package Framework\Http\Traits
 */
trait HandleContentTypes
{
    /**
     * Determine if the request content type is JSON.
     *
     * @return bool True if the content type is JSON, otherwise false.
     */
    public function isJson()
    {
        return Str::contains($this->headers->get('CONTENT_TYPE') ?? '', ['/json', '+json']);
    }

    /**
     * Determine if the request expects a JSON response.
     *
     * @return bool True if the request expects JSON, otherwise false.
     */
    public function expectsJson()
    {
        return ($this->ajax() && ! $this->pjax() && $this->acceptsAnyContentType()) || $this->wantsJson();
    }

    /**
     * Determine if the request is an AJAX request.
     *
     * @return bool True if the request is an AJAX request, otherwise false.
     */
    public function ajax()
    {
        return $this->isXmlHttpRequest();
    }

    /**
     * Determine if the request is a PJAX request.
     *
     * @return bool True if the request is a PJAX request, otherwise false.
     */
    public function pjax()
    {
        return $this->headers->get('X-PJAX') == true;
    }

    /**
     * Determine if the request accepts any content type.
     *
     * @return bool True if the request accepts any content type, otherwise false.
     */
    public function acceptsAnyContentType()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return count($acceptable) === 0 || (
            isset($acceptable[0]) && ($acceptable[0] === '*/*' || $acceptable[0] === '*')
        );
    }

    /**
     * Determine if the request wants JSON.
     *
     * @return bool True if the request wants JSON, otherwise false.
     */
    public function wantsJson()
    {
        $acceptable = $this->getAcceptableContentTypes();

        return isset($acceptable[0]) && Str::contains(strtolower($acceptable[0]), ['/json', '+json']);
    }
}