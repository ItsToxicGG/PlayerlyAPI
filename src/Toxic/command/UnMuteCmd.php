<?php

declare(strict_types=1);

namespace Toxic\command;

/**
 *    Copyright 2023 @ ItsToxicGG
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use pocketmine\utils\TextFormat;

use pocketmine\form\forms\SimpleForm;

use Toxic\Stats;

class UnMuteCmd extends Command implements PluginOwned {
    
    private Stats $plugin;
    
    public function __construct(Stats $plugin){
        $this->plugin = $plugin; 
        
        parent::__construct("unmute", 'UnMute an player!', null, ["uncancelchat"]);
        $this->setAliases(["uncancelchat"]);
        $this->setPermission("toxic.unmute.command");
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {       
        if($sender instanceof Player){
            if(!$sender->hasPermission("toxic.unmute.command")){
                $sender->sendMessage(TextFormat::RED . "You don't have permission to use this command.");
                return true;
            }
            if(count($args) !== 1){
                $sender->sendMessage(TextFormat::RED . "Usage: /unmute <player>");
                return true;
            }
            $player = $this->plugin->getServer()->getPlayerExact($args[0]);
            if($player === null){
                $sender->sendMessage(TextFormat::RED . "The player $args[0] was not found.");
                return true;
            }
            $this->plugin->getMuteAPI()->query("DELETE FROM mute WHERE username = '" . $player->getName() . "'");
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