<?php

namespace mrholler\fshop\events;

use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class ShopRemoveCategory extends BaseEvent implements Cancellable
{
    use CancellableTrait;

    /**
     * @param string $categoryName
     * @param ?Player $player
     */
    public function __construct(private string $categoryName, public ?Player $player)
    {
        parent::__construct($this->player);
    }

    public function getCategoryName() :string
    {
        return $this->categoryName;
    }


}
