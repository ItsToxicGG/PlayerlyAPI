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

class UnMuteCmd extends Command implements PluginOwned {
    
    private $plugin;
    
    public function __construct(Stats $plugin){
        $this->plugin = $plugin; 
        
        parent::__construct("unmute", 'UnMute an player!', null, ["uncancelchat"]);
        $this->setAliases(["uncancelchat"]);
        $this->setPermission("unmute.command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {       
        if($sender instanceof Player){
            if(!$sender->hasPermission("unmute.command")){
                $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command.");
                return true;
            }
            if(count($args) !== 1){
                $sender->sendMessage(TextFormat::RED . "Usage: /unmute <player>");
                return true;
            }
            $player = $this->plugin->getServer()->getPlayerExact($args[0]);
            if($player === null){
                $sender->sendMessage(TextFormat::RED . "The player {$args[0]} was not found.");
                return true;
            }
            $this->plugin->getMuteAPI()->query("DELETE FROM mutes WHERE username = '" . $player->getName() . "'");
            $sender->sendMessage(TextFormat::GREEN . "Player " . $player->getName() . " has been unmuted.");
            $player->sendMessage(TextFormat::GREEN . "You have been unmuted.");
            return true;
        }
        return false;
    }

    public function getOwningPlugin(): Stats{
        return $this->plugin;
    }
}