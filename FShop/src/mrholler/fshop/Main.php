<?php

namespace mrholler\fshop;

use mrholler\fshop\events\ShopOpenEvent;
use mrholler\fshop\events\ShopPlayerAddItem;
use mrholler\fshop\events\ShopPlayerBuyItem;
use mrholler\fshop\events\ShopPlayerRemoveItem;
use mrholler\fshop\libs\xenialdan\customui\windows\CustomForm;
use mrholler\fshop\libs\xenialdan\customui\windows\ModalForm;
use mrholler\fshop\libs\xenialdan\customui\windows\SimpleForm;

use onebone\economyapi\EconomyAPI;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\item\ItemFactory;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

use Exception;

class Main extends PluginBase {

    /** @var Config */
    public Config $shop;

    /** @var EconomyAPI|null */
    private ?EconomyAPI $economyAPI;

    /** @var Main */
    public static Main $instance;

    protected function onLoad(): void
    {
        $this->shop = new Config($this->getDataFolder()."/fshop.yml", Config::YAML);
        Server::getInstance()->getLogger()->info("Плагин включен");
        self::$instance = $this;
    }

    public static function getInstance() :Main
    {
        return self::$instance;
    }

    /**
     * @param CommandSender $sender
     * @param Command $command
     * @param string $label
     * @param array $args
     * @return bool
     * @throws Exception
     */
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if(strtolower($command->getName()) == "fshop"){
            if($sender instanceof Player)
                $this->showMainShop($sender);
            else
                $sender->sendMessage("Используйте команду в игре");
            return true;
        }
        return false;
    }

    /**
     * @param Player $player
     * @return void
     * @throws Exception
     */
    private function showMainShop(Player $player) :void
    {
        if(count(API::listCategories()) == 0){
            if(Server::getInstance()->isOp($player->getName())){
                $form = new SimpleForm("Магазин");
                $form->addButtonEasy("Добавить категорию");
                $form->setCallable(function(Player $player, $data){
                    if($data == "Добавить категорию")
                        $this->showAddCategory($player);
                });
            } else {
                $form = new ModalForm("Магазин", "Сейчас магазин пуст\nВернитесь сюда позже","Закрыть", "Вернуться назад");
            }
        } else {
            $form = new SimpleForm("Магазин");
            if(Server::getInstance()->isOp($player->getName()))
                $form->addButtonEasy("Добавить категорию");
            foreach(API::listCategories() as $name => $data){
                if(!$data["hide"])
                    $form->addButtonEasy($name);
                elseif(count(API::listCategories()) == 1 and !Server::getInstance()->isOp($player->getName())){
                    $form = new ModalForm("Магазин", "Сейчас магазин пуст\nВернитесь сюда позже","Перезагрузить магазин", "Закрыть магазин");
                    $form->setCallable(function(Player $player, $data){
                        if($data)
                            $this->showMainShop($player);
                    });
                    $player->sendForm($form);
                    return;
                } else
                    if(Server::getInstance()->isOp($player->getName()))
                        $form->addButtonEasy($name." §c(скрыт)");
            }
            if(Server::getInstance()->isOp($player->getName()))
                $form->addButtonEasy("Удалить категорию");
            $form->setCallable(function(Player $player, $data){
                if($data == "Добавить категорию")
                    $this->showAddCategory($player);
                elseif($data == "Удалить категорию")
                    $this->showRemoveCategory($player);
                else
                    $this->showCategoryShop($player, $data);
            });
        }
        (new ShopOpenEvent($player, $form))->call();
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $msg
     */
    private function showAddCategory(Player $player, int $msg = 0) :void
    {
        $form = new CustomForm("Добавить категорию");
        if($msg == 1)
            $form->addLabel("§cПроизошла ошибка при добавлении категории, попробуйте еще раз");
        if($msg == 2)
            $form->addLabel("§cКороткое название категории.\nДолжно быть не меньше 3 символов");
        if($msg == 3)
            $form->addLabel("§cТакая категория уже существует");
        if($msg ==4)
            return;
        $form->addInput("Введите название категории", "Название");
        $form->addToggle("Скрыть ее для обычных игроков из списка?", false);
        $form->setCallable(function(Player $player, $data) use($msg){
            /*if($msg == 0) {
                if (isset($data[0]) and !empty($data[0]) and filter_var($data[0], FILTER_VALIDATE_INT) === false) {
                    if(strlen($data[0]) < 3){
                        $this->showAddCategory($player, 2);
                        return false;
                    }
                    if($this->fshop->exists($data[0])){
                        $this->showAddCategory($player, 3);
                        return false;
                    }
                    $ev = new ShopPlayerAddCategory($player, $data[0]);
                    $ev->call();
                    if($ev->isCancelled())
                        return false;
                    $fshop[(string) $data[0]] = ["hide" => $data[1] ?? false];
                    $this->fshop->setAll($fshop);
                    $this->reloadShop();
                    $player->sendMessage("§aКатегория с названием \"" . $data[0] . "\" успешно добавлена");
                } else {
                    $this->showAddCategory($player, 1);
                }
            } else {
                if (isset($data[1]) and !empty($data[1])) {
                    if(strlen($data[1]) < 3){
                        $this->showAddCategory($player, 2);
                        return false;
                    }
                    if($this->fshop->exists($data[1])){
                        $this->showAddCategory($player, 3);
                        return false;
                    }
                    $fshop[(string) $data[1]] = ["hide" => $data[2] ?? false];
                    $this->fshop->setAll($fshop);
                    $this->reloadShop();
                    $player->sendMessage("§aКатегория с названием \"" . $data[1] . "\" успешно добавлена");
                } else {
                    $this->showAddCategory($player, 1);
                }
            }
            return true;*/

            if($result = API::addCategory($player, $msg == 0 ? $data[0] : $data[1], $msg == 0 ? $data[1] : $data[2]) != 0){
                $this->showAddCategory($player, $result);
            } else
                $player->sendMessage("§aКатегория с названием \"" . $msg == 0 ? $data[0] : $data[1] . "\" успешно добавлена");
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $msg
     */
    private function showRemoveCategory(Player $player, int $msg = 0) :void
    {
        $form = new CustomForm("Удаление категории");
        if($msg == 1)
            $form->addLabel("§cПроизошла ошибка, этой категории уже не существует");
        if($msg == 2)
            return;
        $form->addDropdown("Выберите категорию которую нужно удалить", array_keys($this->shop->getAll()));
        $form->setCallable(function(Player $player, $data) use($msg){
            /*if($msg == 0){
                if(isset($data[0])){
                    if(!$this->fshop->exists($data[0]))
                        $this->showRemoveCategory($player, 1);
                    else {
                        $ev = new ShopPlayerRemoveCategory($player, $data[0]);
                        $ev->call();
                        if($ev->isCancelled())
                            return;
                        $this->fshop->remove($data[0]);
                        $this->reloadShop();
                        $player->sendMessage("§aКатегория с названием \"".$data[0]."\" удалена");
                    }
                }
            }*/
            if($result = API::removeCategory($player, $msg == 0 ? $data[0] : $data[1]) != 0){
                $this->showRemoveCategory($player, $result);
            } else
                $player->sendMessage("§aКатегория с названием \"".$msg == 0 ? $data[0] : $data[1]."\" удалена");
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $categoryName
     * @throws Exception
     */
    private function showCategoryShop(Player $player, string $categoryName) :void
    {
        $categoryName = str_replace(" §c(скрыт)", "", $categoryName);
        $category = $this->shop->exists($categoryName) ? $this->shop->get($categoryName) : null;
        if($category == null){
            $form = new ModalForm($categoryName, "Сейчас категория пуста\nВернитесь сюда позже","Закрыть", "Вернуться назад");
            $form->setCallable(function(Player $player, $data){
                if(!$data){
                    $this->showMainShop($player);
                }
            });
        } else {
            $items = $category["items"] ?? [];
            if(!is_array($items) or count($items) == 0){
                if(Server::getInstance()->isOp($player->getName())){
                    $form = new SimpleForm($categoryName);
                    $form->addButtonEasy("Добавить предмет");
                    $form->setCallable(function(Player $player, $data) use($categoryName){
                        if($data == "Добавить предмет"){
                            $this->showAddItemCategory($player, $categoryName);
                        }
                    });
                } else {
                    $form = new ModalForm($categoryName, "Сейчас категория пуста\nВернитесь сюда позже","Закрыть", "Вернуться назад");
                    $form->setCallable(function(Player $player, $data){
                        if(!$data){
                            $this->showMainShop($player);
                        }
                    });
                }
            } else {
                $form = new SimpleForm($categoryName);
                if(Server::getInstance()->isOp($player->getName()))
                    $form->addButtonEasy("Добавить предмет");
                foreach($items as $name => $data){
                    if(!$data["hide"])
                        $form->addButtonEasy($name);
                    else
                        if(Server::getInstance()->isOp($player->getName()))
                            $form->addButtonEasy($name." §c(скрыт)");
                }
                if(Server::getInstance()->isOp($player->getName()))
                    $form->addButtonEasy("Удалить предмет");
                $form->setCallable(function(Player $player, $data) use($categoryName){
                    if($data == "Добавить предмет")
                        $this->showAddItemCategory($player, $categoryName);
                    elseif($data == "Удалить предмет")
                        $this->showRemoveItem($player, $categoryName);
                    else
                        $this->showBuyItem($player, $categoryName, $data);
                });
            }
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $categoryName
     * @param int $msg
     */
    private function showAddItemCategory(Player $player, string $categoryName, int $msg = 0) :void
    {
        $form = new CustomForm("Добавить предмет");
        if($msg == 0)
            $form->addLabel("Поля со §c*§r обязательны к заполнению");
        if($msg == 1)
            $form->addLabel("§cНе все обязательные поля заполнены");
        if($msg == 2)
            $form->addLabel("§cАйди предмета должно быть числом");
        if($msg == 3)
            $form->addLabel("§cМета айди предмета должно быть числом");
        if($msg == 4)
            $form->addLabel("§cЦена предмета должно быть числом");
        if($msg == 5)
            $form->addLabel("§cПредмет с таким именем уже существует");
        $form->addInput("Введите название предмета §c*", "Название");
        $form->addInput("Введите айди предмета §c*", "Айди предмета");
        $form->addInput("Введите мета айди предмета §c*", "Мета айди предмета");
        $form->addInput("Введите описание предмета", "Используйте \\n для переноса");
        $form->addInput("Введите цену предмета §c*", "Цена");
        $form->addToggle("Поштучно/Стак", false);
        $form->addToggle("Выдавать его с кастомным именем?", false);
        $form->addToggle("Скрыть его для обычных игроков из списка?", false);
        $form->setCallable(function(Player $player, $data) use($categoryName){
            if(empty($data[1])){
                $this->showAddItemCategory($player, $categoryName, 1);
                return false;
            }
            if(filter_var($data[2], FILTER_VALIDATE_INT) === false){
                $this->showAddItemCategory($player, $categoryName, 2);
                return false;
            }
            if(filter_var($data[3], FILTER_VALIDATE_INT) === false){
                $this->showAddItemCategory($player, $categoryName, 3);
                return false;
            }
            if(filter_var($data[5], FILTER_VALIDATE_INT) === false){
                $this->showAddItemCategory($player, $categoryName, 4);
                return false;
            }


            $id = (int)$data[2];
            $meta = (int)$data[3];
            $name = $data[1];
            $lore = $data[4];
            $prise = (int)$data[5];
            $stackable = (bool)$data[6] ?? false;
            $custom = (bool)$data[7] ?? false;
            $hide = (bool)$data[8] ?? false;

            $category = $this->shop->get($categoryName);
            $items = $category["items"] ?? [];
            if(array_key_exists($name, $items)){
                $this->showAddItemCategory($player, $categoryName, 5);
                return false;
            }
            $items[$name] = [
                "id" => $id,
                "meta" => $meta,
                "stackable" => $stackable,
                "lore" => $lore ?? false,
                "prise" => $prise,
                "custom" => $custom,
                "hide" => $hide
            ];
            $ev = new ShopPlayerAddItem($player, $categoryName, $name, $id, $meta, $lore, $prise, $stackable, $custom, $hide);
            $ev->call();
            if($ev->isCancelled())
                return false;
            $this->shop->setNested($categoryName.".items", $items);
            $this->reloadShop();
            $player->sendMessage("§aПредмет с названием \"".$name."\" добавлен в категорию \"".$categoryName."\"");

            return true;
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $categoryName
     * @param string $itemName
     * @param int $msg
     */
    private function showBuyItem(Player $player, string $categoryName, string $itemName, int $msg = 0) :void
    {
        list("id" => $id, "meta" => $meta, "stackable" => $stackable, "lore" => $lore, "prise" => $prise, "custom" => $custom) = $this->shop->getNested($categoryName.".items.".$itemName);
        $this->economyAPI = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
        if($this->economyAPI){
            $form = new CustomForm("Покупка предмета \"".$itemName."\"");
            if($msg == 0)
                $form->addLabel("Стоимость предмета ".$prise."$");
            else if($msg == 1)
                $form->addLabel("§cУ вас недостаточно средств");
            else if($msg == 2)
                $form->addLabel("§cНедостаточно места в инвентаре");
            $form->addSlider("Выберите колличество", 1, 64);
            $form->setCallable(function(Player $player, $data) use($id, $prise, $meta, $stackable, $lore, $custom, $categoryName, $itemName){
                if($this->economyAPI->myMoney($player) >= $prise*$data[1]){
                    $item = ItemFactory::getInstance()->get($id, $meta);
                    $item->setCount($stackable == true ? 64 * $data[1] : $data[1]);
                    if($custom)
                        $item->setCustomName($itemName);
                    if($lore != false)
                        $item->setLore(explode("\n", $lore));
                    $ev = new ShopPlayerBuyItem($player, $prise, $item);
                    $ev->call();
                    if($ev->isCancelled())
                        return;
                    if($player->getInventory()->canAddItem($item)){
                        $this->economyAPI->reduceMoney($player, $prise);
                        $player->getInventory()->addItem($item);
                        $player->sendMessage("§aПредмет успешно приобретен!");
                    } else {
                        $this->showBuyItem($player, $categoryName, $itemName, 2);
                    }
                } else {
                    $this->showBuyItem($player, $categoryName, $itemName, 1);
                }
            });
        } else {
            $form = new ModalForm("Ошибка", "Произошла неизвестная ошибка", "Вернуться назад", "Выход");
            $form->setCallable(function(Player $player, $data) use($categoryName){
                if($data){
                    $this->showCategoryShop($player, $categoryName);
                }
            });
        }
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $categoryName
     */
    private function showRemoveItem(Player $player, string $categoryName) :void
    {

        $form = new CustomForm("Удаление предмета");
        $form->addDropdown("Выберите предмет который нужно удалить", array_keys($this->shop->getNested($categoryName.".items")));
        $form->setCallable(function(Player $player, $data) use($categoryName){
            list("id" => $id, "meta" => $meta, "stackable" => $stackable, "lore" => $lore, "prise" => $prise, "custom" => $custom, "hide" => $hide) = $this->shop->getNested($categoryName.".items.".$data[0]);
            $ev = new ShopPlayerRemoveItem($player, $categoryName, $data[0], $id, $meta, $lore, $prise, $stackable, $custom, $hide);
            $ev->call();
            if($ev->isCancelled())
                return;
            $this->shop->removeNested($categoryName.".items.".$data[0]);
            $this->reloadShop();
            $player->sendMessage("§aПредмет успешно удален");
        });
        $player->sendForm($form);
    }

    private function reloadShop() :void
    {
        $this->shop->save();
        $this->shop->reload();
    }

}