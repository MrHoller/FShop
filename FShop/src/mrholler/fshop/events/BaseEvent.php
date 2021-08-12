<?php

namespace mrholler\fshop\events;

use pocketmine\event\Event;
use pocketmine\player\Player;

abstract class BaseEvent extends Event
{

    /** @var Player */
    public Player $player;

    /**
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer() :Player
    {
        return $this->player;
    }

}