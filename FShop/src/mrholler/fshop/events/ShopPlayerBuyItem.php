<?php

namespace mrholler\fshop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\item\Item;
use pocketmine\player\Player;

class ShopPlayerBuyItem extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @param ?Player $player
     * @param int $itemPrice
     * @param Item $item
     */
    public function __construct(public ?Player $player, private int $itemPrice, private Item $item)
    {
        parent::__construct($this->player);
    }

    public function getItemPrice() :int
    {
        return $this->itemPrice;
    }

    public function getItem() :Item
    {
        return $this->item;
    }

}
