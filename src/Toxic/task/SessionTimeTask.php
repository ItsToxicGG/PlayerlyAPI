<?php

namespace Toxic\task;

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

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\Server;

class SessionTimeTask extends Task {

/** @var \mysqli */
private $db;

public function __construct(\mysqli $db) {
    $this->db = $db;
}
    public function onRun() : void {
        $players = Server::getInstance()->getOnlinePlayers();
        $numPlayers = count($players);

        // Batch the queries and updates
        $batchedQueries = [];
        for ($i = 0; $i < $numPlayers; $i += $this->batchSize) {
            $batch = array_slice($players, $i, $this->batchSize);
            $usernames = array_map(function($player) {
                return strtolower($player->getName());
            }, $batch);
            $usernameList = "'" . implode("','", $usernames) . "'";
            $batchedQueries[] = "UPDATE stats SET time = time + 1 WHERE username IN ($usernameList)";
        }

        // Execute the batched queries
        foreach ($batchedQueries as $query) {
            $this->db->query($query);
        }
    }
}
