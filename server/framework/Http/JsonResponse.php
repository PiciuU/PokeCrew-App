<?php

namespace Framework\Http;

use Symfony\Component\HttpFoundation\JsonResponse as SymfonyJsonResponse;

use InvalidArgumentException;

/**
 * Class JsonResponse
 *
 * The JsonResponse class extends Symfony's JsonResponse to customize the constructor.
 * It allows setting encoding options for JSON responses.
 *
 * @package Framework\Http
 */
class JsonResponse extends SymfonyJsonResponse
{
    /**
     * Create a new JsonResponse instance.
     *
     * @param mixed $data The JSON response data.
     * @param int $status The HTTP status code.
     * @param array $headers The response headers.
     * @param int $options The JSON encoding options.
     * @param bool $json Set to true to indicate that the data is already in JSON format.
     */
    public function __construct($data = null, $status = 200, $headers = [], $options = 0, $json = false)
    {
        $this->encodingOptions = $options;

        parent::__construct($data, $status, $headers, $json);
    }

    /**
     * {@inheritdoc}
     *
     * @return static
     */
    public function setData($data = []): static
    {
        $this->original = $data;

        // Ensure json_last_error() is cleared...
        json_decode('[]');

        $this->data = match (true) {
            $data instanceof Jsonable => $data->toJson($this->encodingOptions),
            $data instanceof JsonSerializable => json_encode($data->jsonSerialize(), $this->encodingOptions),
            $data instanceof Arrayable => json_encode($data->toArray(), $this->encodingOptions),
            default => json_encode($data, $this->encodingOptions),
        };

        if (! $this->hasValidJson(json_last_error())) {
            throw new InvalidArgumentException(json_last_error_msg());
        }

        return $this->update();
    }

        /**
     * Determine if an error occurred during JSON encoding.
     *
     * @param  int  $jsonError
     * @return bool
     */
    protected function hasValidJson($jsonError)
    {
        if ($jsonError === JSON_ERROR_NONE) {
            return true;
        }

        return $this->hasEncodingOption(JSON_PARTIAL_OUTPUT_ON_ERROR) &&
                    in_array($jsonError, [
                        JSON_ERROR_RECURSION,
                        JSON_ERROR_INF_OR_NAN,
                        JSON_ERROR_UNSUPPORTED_TYPE,
                    ]);
    }

    /**
     * Determine if a JSON encoding option is set.
     *
     * @param  int  $option
     * @return bool
     */
    public function hasEncodingOption($option)
    {
        return (bool) ($this->encodingOptions & $option);
    }
}