<?php

namespace mrholler\fshop\libs\xenialdan\customui\elements;

use JsonSerializable;
use pocketmine\player\Player;

abstract class UIElement implements JsonSerializable
{

    /** @var string */
    protected $text = "";

    public function jsonSerialize(): array
    {
        return [];
    }

    /**
     * @param string $value
     * @param Player $player
     * @return mixed
     */
    abstract public function handle(string $value, Player $player);

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

}
