<?php

namespace mrholler\fshop;

use mrholler\fshop\events\ShopAddCategory;
use mrholler\fshop\events\ShopRemoveCategory;
use pocketmine\player\Player;

use Exception;

class API
{

    /**
     * @param Player $player
     * @return bool
     * @throws Exception
     */
    public static function openShop(Player $player) :bool
    {
        Main::getInstance()->showMainShop($player);
        return true;
    }

    /**
     * @param string $categoryName
     * @param bool $hide
     * @param ?Player $player
     * @return int
     */
    public static function addCategory(string $categoryName, bool $hide, ?Player $player = null) :int
    {
        $shop = Main::getInstance()->shop->getAll();
        if(empty($categoryName) or !filter_var($categoryName, FILTER_VALIDATE_INT) === false)
            return 1;
        if(strlen($categoryName) < 3)
            return 2;
        if(Main::getInstance()->shop->exists($categoryName))
            return 3;
        $ev = new ShopAddCategory($categoryName, $player);
        $ev->call();
        if($ev->isCancelled())
            return 4;
        $shop[$categoryName] = ["hide" => $hide];
        Main::getInstance()->shop->setAll($shop);
        self::reloadShop();
        return 0;
    }

    /**
     * @param string $categoryName
     * @param ?Player $player
     * @return int
     */
    public static function removeCategory(string $categoryName, ?Player $player = null) :int
    {
        if(!Main::getInstance()->shop->exists($categoryName))
            return 1;
        $ev = new ShopRemoveCategory($categoryName, $player);
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
