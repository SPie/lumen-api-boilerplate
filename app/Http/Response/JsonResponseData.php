<?php

namespace App\Http\Response;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

/**
 * Class JsonResponseData
 *
 * @package App\Http\Response
 */
class JsonResponseData implements Jsonable
{

    /**
     * @var array
     */
    private $data;

    /**
     * JsonResponseData constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    protected function getData(): array
    {
        return $this->data;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param  int $options
     *
     * @return string
     */
    public function toJson($options = 0): string
    {
        return \json_encode($this->prepareForJson($this->getData()), $options);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function prepareForJson(array $data): array
    {
        $responseArray = [];

        foreach ($data as $key => $value) {
            if ($value instanceof Arrayable) {
                $value = $value->toArray();
            }

            if (\is_array($value)) {
                $value = $this->prepareForJson($value);
            }

            $responseArray[$key] = $value;
        }

        return $responseArray;
    }
}