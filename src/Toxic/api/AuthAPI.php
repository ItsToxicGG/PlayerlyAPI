<?php

namespace Toxic\api;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerBlockPickEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerItemConsumeEvent;
use pocketmine\event\player\PlayerItemUseEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\network\mcpe\protocol\TransferPacket;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Vecnavium\FormsUI\CustomForm;

// so beta
// This is a completely independent plugin that I have already created and implemented on my server
// I didn't want to publish it because I'm sure it will be misused, but I have to publish some of my plugins for my resume.

// After loging in or registering, it will transfer you from the hub to the lobby (WatrdogPE proxy)

class AuthAPI extends PluginBase implements Listener {

    private $dtbs;
    // public $dtbs;


    public function onEnable(): void
    {
        $this->saveResource("database.yml");
        $this->dtbs = new Config($this->getDataFolder() . "database.yml", Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        switch ($command->getName()) {
            case "login":
                if ($sender instanceof Player) {
                    $name = strtolower($sender->getName());
                    if ($this->dtbs->getNested($name . ".account") == "true") {
                        if ($this->dtbs->getNested($name . ".freeze") == "on") {
                            $this->Login($sender);
                        } else {
                            $sender->sendMessage("§cshoma ghablan vared shodid.");
                        }
                        $this->Login($sender);
                    } else {
                        $sender->sendMessage("§ashoma sabtenam nakrdid lotfan /register konid.");
                    }
                } else {
                    $sender->sendMessage("pls use in game");
                }
                break;
            case "register":
                if ($sender instanceof Player) {
                    $name = strtolower($sender->getName());
                    if ($this->dtbs->getNested($name . ".account") == "true") {
                        $sender->sendMessage("§ein acc ghablan sabtenam karde ast.");
                    } else {
                        $this->Register($sender);
                    }
                } else {
                    $sender->sendMessage("pls use in game");
                }
                break;
            case "changepass":
                if ($sender instanceof Player) {
                    $name = strtolower($sender->getName());
                    if (!count($args) > 0 or !count($args) > 1) {
                        $sender->sendMessage("intori pass change bede: /changepass pass-ghadim pass-jadid");
                    }
                    if (!strlen($args[1]) < 4 or !strlen($args[1]) > 25) {
                        $sender->sendMessage("tedate karakter bayad 4 ta 25 bashad");
                    } else {

                        if ($this->dtbs->getNested($name . ".account") == "true") {
                            if ($this->dtbs->getNested($name . ".code") === $args[0]) {
                                $this->dtbs->setNested($name . ".code", $args[1]);
                                $this->dtbs->save();
                                $sender->sendMessage("pass jadid set shod, pass jadid shoma : " . $args[1]);
                            } else {
                                $sender->sendMessage("shoma pass khodeton ro eshtebah vared mikonid bayad intory vared konid: /changepass pass-ghadim pass-jadid");
                            }
                        } else {
                            $sender->sendMessage("shoma acc nadarid");
                        }
                    }
                }
                break;
            case "mypass":
                if ($sender instanceof Player) {
                    $name = strtolower($sender->getName());
                    if ($this->dtbs->getNested($name . ".account") == "true") {
                        $code = $this->dtbs->getNested($name . ".code");
                        $sender->sendMessage("§apass " . $sender->getName() . " ast:   §e" . $code);
                    } else {
                        $sender->sendMessage("shoma acc nadarid");
                    }
                }
                break;
            case "passreload":
                if ($sender instanceof Player) {
                    $this->dtbs->reload();
                    $this->dtbs->save();
                    $sender->sendMessage("§adatabase password ha reload shod!");
                } else {
                    $this->dtbs->save();
                    $this->getLogger()->info("§adatabase password ha reload shod!");
                }
                break;
        }
        return true;
    }
    public static function transferPlayer(Player $player, string $servername)
    {

//        if (!is_string($servername)) {
//            Server::getInstance()->getLogger()->alert("§4Please insert an valid Server name.");
//            return;
//        }

        $pk = new TransferPacket();
        $pk->address = $servername; //The server name you specified in the WaterDogPE config.
        $pk->port = 0;
        $player->getNetworkSession()->sendDataPacket($pk);
        Server::getInstance()->getLogger()->info($player->getName() . "§c will teleported to Server §f{$servername}§8.");
    }

    public function onJoin(PlayerJoinEvent $event)
    {
        $player = $event->getPlayer();
        $name = strtolower($player->getName());
        $this->dtbs->setNested($name . ".freeze", "on");
        $this->dtbs->save();
        if ($this->dtbs->getNested($name . ".account") == "true") {
            $this->Login($player);
        } else {
            $this->Register($player);
        }
    }

    public function Login(Player $player): CustomForm
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                $player->kick(TextFormat::GREEN . TextFormat::BOLD . "NaDa ghararemoon in nabood XD");
            } else {
                $name = strtolower($player->getName());
                $code = $this->dtbs->getNested($name . ".code");
                if ($code == $data[0]) {
                    $this->dtbs->setNested($name . ".freeze", "off");
                    $this->dtbs->save();
                    $this->transferPlayer($player, "lobby");
                    return true;
                } else {
                    $player->kick(TextFormat::GREEN . TextFormat::BOLD . "ramz ra eshtebah vared kardid!");
                }
            }
            return true;
        });
        $form->setTitle("§2§lVorood");
        $form->addInput("§eRamzi ke ghablan roye accante khod set kardid ra vared konid.", "§7Ramz ra inja vared konid!.");
        $form->sendToPlayer($player);
        return $form;
    }


    public function Register(Player $player): CustomForm
    {
        $form = new CustomForm(function (Player $player, $data) {
            if ($data === null) {
                $player->kick(TextFormat::GREEN . TextFormat::BOLD . "NaDa ghararemoon in nabood XD");
            } else {
                if (strlen($data[0]) == 4 || 5 || 6 || 7 || 8 || 9 || 10 || 11 || 12 || 13 || 14 || 15 || 16 || 17 || 18 || 19 || 20 || 21 || 22 || 23 || 24 || 25) {
                    $name = strtolower($player->getName());
                    $this->dtbs->setNested($name . ".account", "enable");
                    $this->dtbs->setNested($name . ".code", $this->dtbs->getNested($name . ".code") . $data[0]);
                    $this->dtbs->setNested($name . ".freeze", "off");
                    $this->dtbs->setNested($name . ".account", "true");
                    $this->dtbs->save();
                    $this->transferPlayer($player, "lobby");
                } else {
                    $player->kick(TextFormat::GREEN . TextFormat::BOLD . "deghat kon va dorost vared kon!");
                }

                if ($data[0] == "") {
                    $player->kick(TextFormat::GREEN . TextFormat::BOLD . "Oomadio nasazi XD");
                }
                if ($data[0] == " ") {
                    $player->kick(TextFormat::GREEN . TextFormat::BOLD . "Oomadio nasazi XD");
                }
                // if (strpos($data[0], " ") !== false) {
                //     $player->kick(TextFormat::GREEN . TextFormat::BOLD . "ramz ra bedoone fasele vared kon!");
                // }
            }
        });
        $form->setTitle("§2§lSabtenam");
        $form->addInput("§eLotfan yek ramz 4ta 25 raghami vared konid va bekhater besparid, zira badan baraye voroode badi bayad vared konid.", "§7mesal: ramz123");
        $form->sendToPlayer($player);
        return $form;
    }
    public function onChat(PlayerChatEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }

    public function onEat(PlayerItemConsumeEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }

    public function onBreak(BlockBreakEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }

    public function onPlace(BlockPlaceEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }
    // public function onTuche(BlockPlaceEvent $event){
    //     $player = $event->getPlayer();
    //     $name = strtolower($player->getName());
    //     if($this->dtbs->getNested($name.".freeze") == "on"){
    //         $event->cancel(true);
    //         $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
    //     }
    // }
    public function onMove(PlayerMoveEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }
    public function onDamage(EntityDamageEvent $event){
        // $player = $event->getPlayer();
        if(($event->getEntity()) instanceof Player) {
            $event->cancel();
        }

    }

    public function onBlockPick(PlayerBlockPickEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }
    // public function onInventoryTransaction(InventoryTransactionEvent $event){
    //     $player = $event->getPlayer();
    //     $name = strtolower($player->getName());
    //     if($this->dtbs->getNested($name.".freeze") == "on"){
    //         $event->cancel(true);
    //         $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
    //     }
    // }
    public function onPlayerCommandPreprocess(PlayerCommandPreprocessEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }
    public function onPlayerItemUse(PlayerItemUseEvent $event)
    {
        // $name = strtolower($player->getName());
        // if ($this->dtbs->getNested($name . ".freeze") == "on") {
        $event->cancel();
        // $player->sendTip("§4§abaraye bazi kardan bayad sabtenam konid ya varedshavid: / register ya / login.");
        // }
    }
}
