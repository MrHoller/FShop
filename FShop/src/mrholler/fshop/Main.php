<?php

namespace mrholler\fshop;

use mrholler\fshop\commands\ShopCommand;
use mrholler\fshop\events\ShopCloseEvent;
use mrholler\fshop\events\ShopOpenEvent;
use mrholler\fshop\events\ShopPlayerAddItem;
use mrholler\fshop\events\ShopPlayerBuyItem;
use mrholler\fshop\events\ShopPlayerRemoveItem;
use mrholler\fshop\libs\xenialdan\customui\windows\CustomForm;
use mrholler\fshop\libs\xenialdan\customui\windows\ModalForm;
use mrholler\fshop\libs\xenialdan\customui\windows\SimpleForm;

use pocketmine\item\ItemFactory;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;

use Exception;

class Main extends PluginBase {

    /** @var Config */
    public Config $shop;

    /** @var mixed */
    private mixed $economyAPI;

    /** @var Main */
    public static Main $instance;

    public const PERMISSION_ADMIN = "mrholler.shop.admin";

    protected function onEnable(): void
    {
        PermissionManager::getInstance()->addPermission(new Permission(self::PERMISSION_ADMIN));
        Server::getInstance()->getCommandMap()->register("shop", new ShopCommand());
    }

    protected function onLoad(): void
    {
        $this->shop = new Config($this->getDataFolder()."/shop.yml", Config::YAML);
        self::$instance = $this;
    }

    public static function getInstance() :Main
    {
        return self::$instance;
    }

    /**
     * @param Player $player
     * @return void
     * @throws Exception
     */
    public function showMainShop(Player $player) :void
    {
        if(count(API::listCategories()) == 0){
            if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN)){
                $form = new SimpleForm("Магазин");
                $form->addButtonEasy("Добавить категорию");
                $form->setCallable(function(Player $player, $data){
                    if($data == "Добавить категорию")
                        $this->showAddCategory($player);
                });
            } else {
                $form = new ModalForm("Магазин", "Сейчас магазин пуст\nВернитесь сюда позже","Перезагрузить магазин", "Закрыть магазин");
                $form->setCallable(function(Player $player, $data){
                    if($data)
                        $this->showMainShop($player);
                    else
                        (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
                });
            }
        } else {
            $form = new SimpleForm("Магазин");
            if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN))
                $form->addButtonEasy("Добавить категорию");
            $list = [];
            foreach(API::listCategories() as $name => $data) {
                if($data["hide"]){
                    if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN)) {
                        $form->addButtonEasy($name . " §c(скрыт)");
                        $list[] = $name;
                    }
                } else {
                    $list[] = $name;
                    $form->addButtonEasy($name);
                }
            }
            if(count($list) == 0){
                if(!Server::getInstance()->isOp($player->getName()) or !$player->hasPermission(self::PERMISSION_ADMIN)){
                    $form = new ModalForm("Магазин", "Сейчас магазин пуст", "Перезагрузить магазин", "Закрыть магазин");
                    $form->setCallable(function(Player $player, $data){
                        if($data)
                            $this->showMainShop($player);
                        else
                            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
                    });
                    $form->setCallableClose(function(Player $player){
                        (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
                    });
                    $player->sendForm($form);
                    return;
                }
            }
            if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN))
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
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
        });
        (new ShopOpenEvent($player, $form))->call();
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $err
     */
    private function showAddCategory(Player $player, int $err = 0) :void
    {
        $form = new CustomForm("Добавить категорию");
        if($err == 0)
            $form->addLabel("Введите название категории");
        if($err == 1)
            $form->addLabel("§cПроизошла ошибка при добавлении категории, попробуйте еще раз");
        if($err == 2)
            $form->addLabel("§cКороткое название категории.\nДолжно быть не меньше 3 символов");
        if($err == 3)
            $form->addLabel("§cТакая категория уже существует");
        if($err == 4)
            $form->addLabel("§cВы не можете добавить новую категорию в данный момент");
        $form->addInput("", "Название");
        $form->addToggle("Скрыть ее для обычных игроков из списка?", false);
        $form->setCallable(function(Player $player, $data){
            if($result = API::addCategory($player, $data[1], $data[2]) != 0){
                $this->showAddCategory($player, $result);
            } else {
                $player->sendMessage("§aКатегория с названием \"" . $data[1] . "\" успешно добавлена");
                (new ShopCloseEvent($player, ShopCloseEvent::SUCCESS_CLOSE))->call();
            }
        });
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param int $err
     */
    private function showRemoveCategory(Player $player, int $err = 0) :void
    {
        $form = new CustomForm("Удаление категории");
        if($err == 0)
            $form->addLabel("Выберите категорию которую нужно удалить");
        if($err == 1)
            $form->addLabel("§cПроизошла ошибка, этой категории уже не существует");
        if($err == 2)
            $form->addLabel("§cВы не можете удалить категорию в данный момент");
        $form->addDropdown("", array_keys($this->shop->getAll()));
        $form->setCallable(function(Player $player, $data){
            if($result = API::removeCategory($player, $data[1]) != 0){
                $this->showRemoveCategory($player, $result);
            } else {
                $player->sendMessage("§aКатегория с названием \"" . $data[1] . "\" удалена");
                (new ShopCloseEvent($player, ShopCloseEvent::SUCCESS_CLOSE));
            }
        });
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
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
            $form = new ModalForm($categoryName, "В этой категории ничего нет","Закрыть", "Вернуться назад");
            $form->setCallable(function(Player $player, $data){
                if($data)
                    (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
                else
                    $this->showMainShop($player);
            });
        } else {
            $items = $category["items"] ?? [];
            if(!is_array($items) or count($items) == 0){
                if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN)){
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
                        if($data)
                            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
                        else
                            $this->showMainShop($player);
                    });
                }
            } else {
                $form = new SimpleForm($categoryName);
                if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN))
                    $form->addButtonEasy("Добавить предмет");
                foreach($items as $name => $data){
                    if(!$data["hide"])
                        $form->addButtonEasy($name);
                    else
                        if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN))
                            $form->addButtonEasy($name." §c(скрыт)");
                }
                if(Server::getInstance()->isOp($player->getName()) or $player->hasPermission(self::PERMISSION_ADMIN))
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
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
        });
        $player->sendForm($form);
    }

    /**
     * @param Player $player
     * @param string $categoryName
     * @param int $err
     */
    private function showAddItemCategory(Player $player, string $categoryName, int $err = 0) :void
    {
        $form = new CustomForm("Добавить предмет");
        if($err == 0)
            $form->addLabel("Поля со §c*§r обязательны к заполнению");
        if($err == 1)
            $form->addLabel("§cНе все обязательные поля заполнены");
        if($err == 2)
            $form->addLabel("§cАйди предмета должно быть числом");
        if($err == 3)
            $form->addLabel("§cМета айди предмета должно быть числом");
        if($err == 4)
            $form->addLabel("§cЦена предмета должно быть числом");
        if($err == 5)
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
            $price = (int)$data[5];
            $stackable = (bool)$data[6];
            $custom = (bool)$data[7];
            $hide = (bool)$data[8];

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
                "price" => $price,
                "custom" => $custom,
                "hide" => $hide
            ];
            $ev = new ShopPlayerAddItem($player, $categoryName, $name, $id, $meta, $lore, $price, $stackable, $custom, $hide);
            $ev->call();
            if($ev->isCancelled())
                return false;
            $this->shop->setNested($categoryName.".items", $items);
            API::reloadShop();
            $player->sendMessage("§aПредмет с названием \"".$name."\" добавлен в категорию \"".$categoryName."\"");
            (new ShopCloseEvent($player, ShopCloseEvent::SUCCESS_CLOSE))->call();

            return true;
        });
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
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
        list("id" => $id, "meta" => $meta, "stackable" => $stackable, "lore" => $lore, "price" => $price, "custom" => $custom) = $this->shop->getNested($categoryName.".items.".$itemName);
        $this->economyAPI = Server::getInstance()->getPluginManager()->getPlugin("EconomyAPI");
        if($this->economyAPI){
            $form = new CustomForm("Покупка предмета \"".$itemName."\"");
            if($msg == 0)
                $form->addLabel("Стоимость предмета ".$price."$");
            else if($msg == 1)
                $form->addLabel("§cУ вас недостаточно средств");
            else if($msg == 2)
                $form->addLabel("§cНедостаточно места в инвентаре");
            $form->addSlider("Выберите колличество", 1, 64);
            $form->setCallable(function(Player $player, $data) use($id, $price, $meta, $stackable, $lore, $custom, $categoryName, $itemName){
                if($this->economyAPI->myMoney($player) >= $price*$data[1]){
                    $item = ItemFactory::getInstance()->get($id, $meta);
                    $item->setCount($stackable == true ? 64 * $data[1] : $data[1]);
                    if($custom)
                        $item->setCustomName($itemName);
                    if($lore != false)
                        $item->setLore(explode("\n", $lore));
                    $ev = new ShopPlayerBuyItem($player, $price, $item);
                    $ev->call();
                    if($ev->isCancelled())
                        return;
                    if($player->getInventory()->canAddItem($item)){
                        $this->economyAPI->reduceMoney($player, $price);
                        $player->getInventory()->addItem($item);
                        $player->sendMessage("§aПредмет успешно приобретен!");
                        (new ShopCloseEvent($player, ShopCloseEvent::SUCCESS_CLOSE))->call();
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
                if($data)
                    $this->showCategoryShop($player, $categoryName);
                else
                    (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
            });
        }
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
        });
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
            list("id" => $id, "meta" => $meta, "stackable" => $stackable, "lore" => $lore, "price" => $price, "custom" => $custom, "hide" => $hide) = $this->shop->getNested($categoryName.".items.".$data[0]);
            $ev = new ShopPlayerRemoveItem($player, $categoryName, $data[0], $id, $meta, $lore, $price, $stackable, $custom, $hide);
            $ev->call();
            if($ev->isCancelled())
                return;
            $this->shop->removeNested($categoryName.".items.".$data[0]);
            API::reloadShop();
            $player->sendMessage("§aПредмет успешно удален");
            (new ShopCloseEvent($player, ShopCloseEvent::SUCCESS_CLOSE))->call();
        });
        $form->setCallableClose(function(Player $player){
            (new ShopCloseEvent($player, ShopCloseEvent::BUTTON_CLOSE))->call();
        });
        $player->sendForm($form);
    }

}
