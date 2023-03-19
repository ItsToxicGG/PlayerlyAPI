<?php

namespace Toxic\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Toxic\Stats;

class UnBanCommand extends Command {

    private $plugin;
    
    public function __construct(Stats $plugin) {
        parent::__construct("unban", "Unban a player", null, ["unban"]);
        $this->setPermission("toxic.unban.command");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $label, array $args) {
        if (count($args) < 1) {
            $sender->sendMessage("Usage: /unban <player>");
            return false;
        }
        
        $playerName = $args[0];
        
        if ($this->plugin->getBanAPI()->unbanPlayer($playerName)) {
            $sender->sendMessage("$playerName has been unbanned");
        } else {
            $sender->sendMessage("$playerName is not banned");
        }
        
        return true;
    }
}
