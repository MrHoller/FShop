<?php

namespace mrholler\fshop\events;

use pocketmine\event\Event;
use pocketmine\form\Form;
use pocketmine\player\Player;

class ShopOpenEvent extends Event
{

    /** @var Player */
    public Player $player;

    /** @var Form */
    public Form $form;

    public function __construct(Player $player, Form $form)
    {
        $this->player = $player;
        $this->form = $form;
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