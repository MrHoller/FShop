<?php

namespace mrholler\shop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class ShopPlayerRemoveCategory extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /** @var Player */
    public Player $player;

    /** @var string */
    private string $categoryName;

    public function __construct(Player $player, string $categoryName)
    {
        parent::__construct($player);

        $this->player = $player;
        $this->categoryName = $categoryName;
    }

    public function getCategoryName() :string
    {
        return $this->categoryName;
    }


}