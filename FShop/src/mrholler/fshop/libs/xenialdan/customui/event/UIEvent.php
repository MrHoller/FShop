<?php

namespace mrholler\fshop\libs\xenialdan\customui\event;

use pocketmine\event\plugin\PluginEvent;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;

abstract class UIEvent extends PluginEvent
{

    public static $handlerList;

    public function __construct(Plugin $plugin, protected DataPacket $packet, protected Player $player)
    {
        parent::__construct($plugin);
    }

    public function getPacket(): DataPacket
    {
        return $this->packet;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }

    public function getID(): int
    {
        return $this->packet->formId;
    }

}
