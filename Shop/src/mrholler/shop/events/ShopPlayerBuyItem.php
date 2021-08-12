<?php

namespace mrholler\shop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ShopPlayerBuyItem extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /** @var Player */
    public Player $player;

    /** @var int */
    private int $itemPrise;

    /** @var Item */
    private Item $item;

    public function __construct(Player $player, int $itemPrise, Item $item)
    {
        parent::__construct($player);

        $this->player = $player;
        $this->itemPrise = $itemPrise;
        $this->item = $item;
    }

    public function getItemPrise() :int
    {
        return $this->itemPrise;
    }

    public function getItem() :Item
    {
        return $this->item;
    }

}