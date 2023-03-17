<?php

declare(strict_types=1);

namespace Toxic\command;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use pocketmine\utils\TextFormat;

use pocketmine\form\forms\SimpleForm;

use Toxic\Stats;

class MuteCmd extends Command implements PluginOwned {
    
    private $plugin;
    
    public function __construct(Stats $plugin){
        $this->plugin = $plugin; 
        
        parent::__construct("mute", 'Mute an player!', null, ["cancelchat"]);
        $this->setAliases(["cancelchat"]);
        $this->setPermission("mute.command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {       
        if($sender instanceof Player){
            if(!$sender->hasPermission("mute.command")){
                $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command.");
                return true;
            }
            if(count($args) < 2){
                $sender->sendMessage(TextFormat::RED . "Usage: /mute <player> <time> [reason]");
                return true;
            }
            $player = $this->getServer()->getPlayer($args[0]);
            if($player === null){
                $sender->sendMessage(TextFormat::RED . "Player not found.");
                return true;
            }
            $time = strtotime($args[1]);
            if($time === false){
                $sender->sendMessage(TextFormat::RED . "Invalid time format. Use something like 1d4h2m for 1 day, 4 hours, and 2 minutes.");
                return true;
            }
            if($time < time()){
                $sender->sendMessage(TextFormat::RED . "You can't mute someone in the past.");
                return true;
            }
            $reason = isset($args[2]) ? implode(" ", array_slice($args, 2)) : "";
            $this->plugin->getMuteAPI()->query("INSERT INTO mutes (player, mutetime, reason) VALUES ('" . $player->getName() . "', " . $time . ", '" . $this->mysqli->real_escape_string($reason) . "')");
            $sender->sendMessage(TextFormat::GREEN . "Player " . $player->getName() . " has been muted until " . date("Y-m-d H:i:s", $time) . ".");
            $player->sendMessage(TextFormat::RED . "You have been muted until " . date("Y-m-d H:i:s", $time) . ". Reason: " . $reason);
            return true;
        }
        return false;
    }

    public function getOwningPlugin(): Stats{
        return $this->plugin;
    }
}