<?php

namespace mrholler\fshop\libs\xenialdan\customui\elements;

use pocketmine\player\Player;

class Toggle extends UIElement
{

    public function __construct(protected string $text = "", protected bool $value = false){}

    public function setValue(bool $value): void
    {
        $this->value = $value;
    }

    public function jsonSerialize(): array
    {
        return [
            'type' => 'toggle',
            'text' => $this->text,
            'default' => $this->value
        ];
    }

    /**
     * @param string $value
     * @param Player $player
     * @return bool
     */
    public function handle(string $value, Player $player)
    {
        return $value;
    }

}
