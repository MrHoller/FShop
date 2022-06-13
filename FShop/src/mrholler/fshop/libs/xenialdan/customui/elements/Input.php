<?php

namespace mrholler\fshop\libs\xenialdan\customui\elements;

use pocketmine\player\Player;

class Input extends UIElement
{

    /**
     *
     * @param string $text
     * @param string $placeholder
     * @param string $defaultText
     */
    public function __construct(protected string $text = "", protected string $placeholder = "", protected string $defaultText = ""){}

    final public function jsonSerialize(): array
    {
        return [
            'type' => 'input',
            'text' => $this->text,
            'placeholder' => $this->placeholder,
            'default' => $this->defaultText
        ];
    }

    /**
     * @param string $value
     * @param Player $player
     * @return string
     */
    public function handle(string $value, Player $player)
    {
        return $value;
    }

}
