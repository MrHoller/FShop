<?php

namespace mrholler\fshop\events;

use pocketmine\player\Player;

class ShopCloseEvent extends BaseEvent
{

    public const BUTTON_CLOSE = 0;
    public const SUCCESS_CLOSE = 1;

    /**
     * @param Player $player
     * @param int $type
     */
    public function __construct(public Player $player, private int $type)
    {
        parent::__construct($this->player);
    }

    public function getType() :int
    {
        return $this->type;
    }

}
