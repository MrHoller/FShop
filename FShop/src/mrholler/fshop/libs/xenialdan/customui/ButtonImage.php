<?php

declare(strict_types=1);

namespace mrholler\fshop\libs\xenialdan\customui;

use JsonSerializable;

class ButtonImage implements JsonSerializable
{
    public const IMAGE_TYPE_PATH = 'path';
    public const IMAGE_TYPE_URL = 'url';


    /**
     * @param string $data Path or URL depending on $type
     * @param string $type Type of the icon, either ButtonImage::IMAGE_TYPE_PATH or ButtonImage::IMAGE_TYPE_URL
     */
    public function __construct(private string $data, private string $type = self::IMAGE_TYPE_URL){}

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getData(): string
    {
        return $this->data;
    }

    public function jsonSerialize()
    {
        return [
            'type' => $this->type,
            'data' => $this->data
        ];
    }
}
