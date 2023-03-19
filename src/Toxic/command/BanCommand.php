<?php

namespace Toxic\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use Toxic\Stats;

class BanCommand extends Command {

    private $plugin;
    
    public function __construct(Stats $plugin) {
        parent::__construct("ban", "Ban a player", null, ["ban"]);
        $this->setPermission("toxic.ban.command");
        $this->plugin = $plugin;
    }
    
    public function execute(CommandSender $sender, string $label, array $args) {
        if (count($args) < 2) {
            $sender->sendMessage("Usage: /ban <player> <reason> [duration]");
            return false;
        }
        
        $playerName = $args[0];
        $reason = $args[1];
        $duration = isset($args[2]) ? intval($args[2]) : 0;
        
        if ($this->plugin->getBanAPI()->banPlayer($playerName, $reason, $duration)) {
            $sender->sendMessage("$playerName has been banned");
        } else {
            $sender->sendMessage("Failed to ban $playerName");
        }
        
        return true;
    }
}
