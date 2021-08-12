<?php

namespace mrholler\fshop\commands;

use mrholler\fshop\API;
use mrholler\fshop\Main;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;

use Exception;

class ShopCommand extends Command
{

    private Main $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin)
    {
        parent::__construct("shop");
        $this->setDescription("Открыть магазин");
        $this->setUsage("/shop - открыть интерфейс магазина");
        $this->setPermission(DefaultPermissions::ROOT_USER.";".Main::PERMISSION_ADMIN);
        $this->plugin = $plugin;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @throws Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if($sender instanceof Player)
            API::openShop($sender);
        else
            $sender->sendMessage("Используйте команду в игре");
    }

}