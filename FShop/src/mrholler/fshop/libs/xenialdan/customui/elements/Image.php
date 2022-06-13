<?php

namespace mrholler\fshop\libs\xenialdan\customui\elements;

use pocketmine\player\Player;

/**
 * @internal
 * @deprecated
 */
class Image extends UIElement
{
//TODO! Blame Mojang, doesn't work yet

    public function __construct(public $texture, public int $width = 0, public int $height = 0){}

    /**
     *
     * @return array
     */
    final public function jsonSerialize(): array
    {
        return [
            'text' => 'sign',
            'type' => 'image',
            'texture' => $this->texture,
            'size' => [$this->width, $this->height]
        ];
    }

    /**
     * TODO
     *
     * @param null $value
     * @param Player $player
     * @return mixed
     */
    public function handle($value, Player $player)
    {
        return null;
    }

}
