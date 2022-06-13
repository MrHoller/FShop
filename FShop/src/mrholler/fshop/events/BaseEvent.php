<?php

namespace mrholler\fshop\events;

use pocketmine\event\Event;
use pocketmine\player\Player;

abstract class BaseEvent extends Event
{

    /**
     * @param ?Player $player
     */
    public function __construct(public ?Player $player){}

    public function getPlayer() :?Player
    {
        return $this->player;
    }

}
