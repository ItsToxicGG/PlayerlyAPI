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
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
        $username = strtolower($player->getName());
        $sessionTime = $this->getSessionTime($username);
        $this->db->query("UPDATE stats SET time = $sessionTime WHERE username = '$username'");
    }
}

public function getSessionTime($username) {
    $result = $this->db->query("SELECT time FROM stats WHERE username = '$username'");
    $sessionTime = 0;
    if ($result instanceof \mysqli_result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sessionTime = (int) $row["time"] + 1;
    }
    return $sessionTime;
}
}
