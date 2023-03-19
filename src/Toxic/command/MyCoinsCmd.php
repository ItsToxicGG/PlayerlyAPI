<?php

declare(strict_types=1);

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

namespace Toxic\command;

use pocketmine\Server;
use pocketmine\player\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\plugin\PluginOwned;

use pocketmine\utils\TextFormat;

//use pocketmine\form\forms\SimpleForm;

use Toxic\Stats;

class MyCoinsCmd extends Command implements PluginOwned {
    
    private $plugin;
    
    public function __construct(Stats $plugin){
        $this->plugin = $plugin; 
        
        parent::__construct("mycoins", 'See your coins!', null, ["mycoin"]);
        $this->setAliases(["mycoin"]);
    }
    
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {       
        if($sender instanceof Player){ 
            $coins = $this->plugin->getStatsAPI()->getCoins($sender);
            $player->sendMessage("You have exactly " . $coins . " coins");
        }
        return false;
    }

    public function getOwningPlugin(): Stats{
        return $this->plugin;
    }
}