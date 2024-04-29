<?php

namespace Framework\Http;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * Class Response
 *
 * The Response class extends Symfony's Response class to provide a simple constructor.
 * It allows creating HTTP responses with custom content, status code, and headers.
 *
 * @package Framework\Http
 */
class Response extends SymfonyResponse
{
    /**
     * Create a new Response instance.
     *
     * @param string $content The response content.
     * @param int $status The HTTP status code.
     * @param array $headers The response headers.
     */
    public function __construct($content = '', $status = 200, array $headers = [])
    {
        parent::__construct($content, $status, $headers);
    }

}