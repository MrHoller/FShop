<?php

namespace mrholler\shop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class ShopPlayerAddItem extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /** @var Player */
    public Player $player;

    /** @var string */
    private string $categoryName;

    /** @var string */
    private string $itemName;

    /** @var int */
    private int $itemId;

    /** @var int */
    private int $itemMeta;

    /** @var array */
    private array $itemLore;

    /** @var int */
    private int $itemPrise;

    /** @var bool */
    private bool $itemStackable;

    /** @var bool */
    private bool $itemCustom;

    /** @var bool */
    private bool $itemHide;

    public function __construct(Player $player, string $categoryName, string $itemName, int $itemId, int $itemMeta, array $itemLore, int $itemPrise, bool $itemStackable, bool $itemCustom, bool $itemHide)
    {
        parent::__construct($player);

        $this->player = $player;
        $this->categoryName = $categoryName;
        $this->itemName = $itemName;
        $this->itemId = $itemId;
        $this->itemMeta = $itemMeta;
        $this->itemLore = $itemLore;
        $this->itemPrise = $itemPrise;
        $this->itemStackable = $itemStackable;
        $this->itemCustom = $itemCustom;
        $this->itemHide = $itemHide;
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

    public function getItemLore() :array
    {
        return $this->itemLore;
    }

    public function getItemPrise() :int
    {
        return $this->itemPrise;
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