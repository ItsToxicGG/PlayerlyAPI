<?php

declare(strict_types=1);

namespace Statics\command;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use Statics\Stats;

class StatsCommand extends Command implements PluginOwned {
    
    private $plugin;
    
    public function __construct(Stats $plugin){
        $this->plugin = $plugin; 
        
        parent::__construct("stats", 'Display your stats!', null, ["profile", "userinfo"]);
        $this->setAliases(["profile", "user"]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if ($sender instanceof Player) {
            $name = $sender->getName();
            $kills = $this->plugin->getStatsAPI()->getKills($sender); 
            $wins = $this->plugin->getStatsAPI()->getWins($sender);
            $deaths = $this->plugin->getStatsAPI()->getDeaths($sender);
            $sender->sendMessage("Name: " . $name);
            $sender->sendMessage("Kills: " . $kills);
            $sender->sendMessage("Wins: " . $wins);
            $sender->sendMessage("Deaths: " . $deaths);
            } else {
                $sender->sendMessage("Use this command in-game");
            }
        }

    public function getOwningPlugin(): Stats{
        return $this->plugin;
    }
}