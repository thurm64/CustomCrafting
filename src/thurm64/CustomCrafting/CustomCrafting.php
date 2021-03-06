<?php

declare(strict_types=1);
namespace thurm64\CustomCrafting;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\Player\PlayerJoinEvent;
use pocketmine\nbt\tag\ListTag;
use pocketmine\event\Player\PlayerCommandPreprocessEvent;
use pocketmine\item\Item;
use pocketmine\block\BlockIds;
use pocketmine\item\Armor;
use pocketmine\utils\Color;
use pocketmine\utils\Config;
use pocketmine\Player;
use pocketmine\inventory\ShapedRecipe;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
class CustomCrafting extends PluginBase implements Listener {
    public function log($str) {
        $this->getLogger()->info("[CustomCrafts]" . $str);
    }

	public function onEnable() {
       
    	$this->saveDefaultConfig();
        if(!InvMenuHandler::isRegistered()){
            InvMenuHandler::register($this);
        }
	$this->myConfig = new Config($this->getDataFolder() . "config.yml", Config::YAML);
        foreach($this->myConfig->getAll() as $recipe){
            $this->getServer()->getCraftingManager()->registerRecipe(unserialize($recipe));
        }
    }
	
    public function onCommand(\pocketmine\command\CommandSender $player, \pocketmine\command\Command $c, string $l, array $a) : bool {
        if($player instanceof Player && $player->hasPermission("customcrafting.command")) {

            if($c->getName() == "ccrename" && count($a) == 1) {
                $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setCustomName($a[0]));
                return true;
            } else if($c->getName() == "ccrelore" && count($a) == 1) {
                $player->getInventory()->setItemInHand($player->getInventory()->getItemInHand()->setLore([str_replace("{line}","\n", $a[0])]));
                return true;
            } else if($c->getName() == "ccglowify" && count($a) == 0) {
                $item = $player->getInventory()->getItemInHand();
                $item->setNamedTagEntry(new ListTag("ench"));
                $player->getInventory()->setItemInHand($item);
                return true;
            } else if($c->getName() == "ccrecolor" && count($a) == 3) {
                $item = $player->getInventory()->getItemInHand();
                if($item instanceof Armor) {
                $item->setCustomColor(new Color(intval($a[0]),intval($a[1]),intval($a[2])));
                $player->getInventory()->setItemInHand($item);
                return true;
                } else {

                }
            } else if($c->getName() == "customcrafts") {
                $menu = InvMenu::create(InvMenu::TYPE_CHEST);
                $jason = Item::get(BlockIds::INVISIBLE_BEDROCK);
                $a = Item::get(Item::AIR);
                //1,2,3
                //10, 11, 12
                //19, 20, 21
                $inv = [
                    $jason,$a,$a,$a,$jason,$jason,$jason,$jason,$jason,
                    $jason,$a,$a,$a,$jason,$jason,$jason,$a,$jason,
                    $jason,$a,$a,$a,$jason,$jason,$jason,$jason,$jason
                ];
                for($i = 0; $i < 27; $i++) {
                    $item = $inv[$i]; 
                    $menu->getInventory()->setItem($i, $inv[$i]);
                }
                $menu->send($player);
                $menu->setListener(function(InvMenuTransaction $transaction) : InvMenuTransactionResult{
                    if($transaction->getItemClicked()->getId() == BlockIds::INVISIBLEBEDROCK){
                        return $transaction->discard();
                    }

                    return $transaction->continue();
                });
                $menu->setInventoryCloseListener(function($player, $inventory) : void{
                    $recipe = [];
                    for($r = 0; $r < 3; $r++) {
                        for($c = 0; $c < 3; $c++) {
                            $slot = $c + 1;
                            $slot += 9 * $r;
                            $recipe[($r * 3) + $c]= $inventory->getItem($slot);
                        }
                    }
                
                    $yield = $inventory->getItem(16);
                    if($yield->getID() != 0) {
                    $recipe = new ShapedRecipe([
                        "abc",
                        "def",
                        "ghi"
                    ],
                    [
                        "a" => $recipe[0],
                        "b" => $recipe[1],
                        "c" => $recipe[2],
                        "d" => $recipe[3],
                        "e" => $recipe[4],
                        "f" => $recipe[5],
                        "g" => $recipe[6],
                        "h" => $recipe[7],
                        "i" => $recipe[8]
                        
                    ],[$yield]);
                
                $this->myConfig->set("cc" . uniqid("Recipe-"), serialize($recipe));
                $this->myConfig->save();
                $this->getServer()->getCraftingManager()->registerRecipe($recipe);
                }
            });
            return true;
            }
            }
        return false;
    }
}
//commit
