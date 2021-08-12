<?php

namespace mrholler\fshop\events;

use pocketmine\player\Player;

class ShopCloseEvent extends BaseEvent
{

    public const BUTTON_CLOSE = 0;
    public const SUCCESS_CLOSE = 1;

    /** @var Player  */
    public Player $player;
    /** @var int */
    public int $type;

    /**
     * @param Player $player
     * @param int $type
     */
    public function __construct(Player $player, int $type)
    {
        parent::__construct($player);
        $this->player = $player;
        $this->type = $type;
    }

    public function getType() :int
    {
        return $this->type;
    }

}