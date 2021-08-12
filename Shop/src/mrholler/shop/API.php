<?php

namespace mrholler\shop;

use mrholler\shop\events\ShopPlayerAddCategory;
use mrholler\shop\events\ShopPlayerRemoveCategory;
use pocketmine\player\Player;

class API
{

    /**
     * @param Player $player
     * @param string $categoryName
     * @param bool $hide
     * @return int
     */
    public static function addCategory(Player $player, string $categoryName, bool $hide) :int
    {
        $shop = Main::getInstance()->shop->getAll();
        if(empty($categoryName) or !filter_var($categoryName, FILTER_VALIDATE_INT) === false)
            return 1;
        if(strlen($categoryName) < 3)
            return 2;
        if(Main::getInstance()->shop->exists($categoryName))
            return 3;
        $ev = new ShopPlayerAddCategory($player, $categoryName);
        $ev->call();
        if($ev->isCancelled())
            return 4;
        $shop[$categoryName] = ["hide" => $hide];
        Main::getInstance()->shop->setAll($shop);
        self::reloadShop();
        return 0;
    }

    /**
     * @param Player $player
     * @param string $categoryName
     * @return int
     */
    public static function removeCategory(Player $player, string $categoryName) :int
    {
        if(!Main::getInstance()->shop->exists($categoryName))
            return 1;
        $ev = new ShopPlayerRemoveCategory($player, $categoryName);
        $ev->call();
        if($ev->isCancelled())
            return 2;
        Main::getInstance()->shop->remove($categoryName);
        self::reloadShop();
        return 0;
    }

    public static function listCategories() :array{
        return Main::getInstance()->shop->getAll();
    }

    public static function reloadShop() :void
    {
        Main::getInstance()->shop->save();
        Main::getInstance()->shop->reload();
    }

}