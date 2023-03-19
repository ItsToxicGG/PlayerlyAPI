<?php

namespace Toxic\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Toxic\Stats;
use pocketmine\player\Player;

class OPCoinComamnd extends Command {

private $plugin;
    
public function __construct(Stats $plugin){
    $this->plugin = $plugin; 
        
    parent::__construct("opcoins", 'Add,Remove or Set amount of coins to a player!', null, ["opcoin"]);
    $this->setAliases(["opcoin"]);
    $this->setPermission("toxic.opcoin.command");
}

public function execute(CommandSender $sender, string $commandLabel, array $args) {
    if(count($args) < 1) {
        $sender->sendMessage($this->getUsage());
        return;
    }
    
    $action = strtolower($args[0]);
    $playerName = strtolower($args[1] ?? $sender->getName()); // use sender's name if player name not specified
    $amount = intval($args[2] ?? 0);
    
    $player = $this->getServer()->getPlayerExact($playerName);
    if(!$player) {
        $sender->sendMessage("Player was not found.");
        return;
    }
    
    switch($action) {
        case "add":
            if($sender->hasPermission("toxic.opcoin.command")){
            $this->plugin->getStatsAPI()->addCoins($player->getName(), $amount);
            $sender->sendMessage("Added " . $amount . " coins to " . $player->getName());
            }
            break;
        case "remove":
            if($sender->hasPermission("toxic.opcoin.command")){
            $this->plugin->getStatsAPI()->removeCoins($player->getName(), $amount);
            $sender->sendMessage("Removed " . $amount . " coins from " . $player->getName());
            }
            break;
        case "set":
            if($sender->hasPermission("toxic.opcoin.command")){
            $this->plugin->getStatsAPI()->setCoins($player->getName(), $amount);
            $sender->sendMessage("Set " . $player->getName() . "'s coins to " . $amount);
            }
            break;
        case "reset":
            if($sender->hasPermission("toxic.opcoin.command")){
            $this->plugin->getStatsAPI()->resetCoins($player->getName());
            $sender->sendMessage("Resetted " . $player->getName() . "coins");
            }
            break;   
        default:
            $sender->sendMessage($this->getUsage());
            break;
    }
}

}