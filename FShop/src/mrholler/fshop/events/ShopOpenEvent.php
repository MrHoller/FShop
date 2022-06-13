<?php

namespace mrholler\fshop\events;

use pocketmine\event\Event;
use pocketmine\form\Form;
use pocketmine\player\Player;

class ShopOpenEvent extends Event
{

    /**
     * @param Player $player
     * @param Form $form
     */
    public function __construct(public Player $player, private Form $form)
    {
        parent::__construct($this->player);
    }

    public function getPlayer() :Player
    {
        return $this->player;
    }

    public function getForm() :Form
    {
        return $this->form;
    }

}
