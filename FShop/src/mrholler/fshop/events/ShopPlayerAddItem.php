<?php

namespace mrholler\fshop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class ShopPlayerAddItem extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @param Player $player
     * @param string $categoryName
     * @param string $itemName
     * @param int $itemId
     * @param int $itemMeta
     * @param mixed $itemLore
     * @param int $itemPrice
     * @param bool $itemStackable
     * @param bool $itemCustom
     * @param bool $itemHide
     */
    public function __construct(public Player $player, private string $categoryName, private string $itemName, private int $itemId, private int $itemMeta, private mixed $itemLore, private int $itemPrice, private bool $itemStackable, private bool $itemCustom, private bool $itemHide)
    {
        parent::__construct($this->player);
    }

    public function getCategoryName() :string
    {
        return $this->categoryName;
    }

    public function getItemName() :string
    {
        return $this->itemName;
    }

    public function getItemId() :int
    {
        return $this->itemId;
    }

    public function getItemMeta() :int
    {
        return $this->itemMeta;
    }

    public function getItemLore() :mixed
    {
        return $this->itemLore;
    }

    public function getItemPrice() :int
    {
        return $this->itemPrice;
    }

    public function getItemStackable() :bool
    {
        return $this->itemStackable;
    }

    public function getItemCustom() :bool
    {
        return $this->itemCustom;
    }

    public function getItemHide() :bool
    {
        return $this->itemHide;
    }

}
