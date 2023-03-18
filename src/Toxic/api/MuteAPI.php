<?php

namespace Toxic\api;

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

use pocketmine\player\Player;
use pocketmine\utils\Config;
use Toxic\Stats;
use mysqli;

class MuteAPI {

    public mysqli $db;	
	
    public Stats $plugin;	
	
    public function __construct(Stats $plugin){
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
        if($this->db->connect_error){
            die("Connection Failed" . $this->db->connect_error);
        }
        $querycontents = "CREATE TABLE IF NOT EXISTS mute (
            username VARCHAR(255) PRIMARY KEY,
            uuid VARCHAR(255), 
            xuid VARCHAR(50),
            mutetime INT(11),
            mutereason TEXT
          );";
        $this->db->query($querycontents);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function MuteExistsFromPlayer(string $name): bool
    {
		$result = $this->db->query("SELECT * FROM mute WHERE username='".$this->db->real_escape_string($name)."'");
		return $result->num_rows > 0;
	}

    /**
     * @param Player $player
     * @param string $reason
     * @param int $t
     * @return bool
     */
  public function addMuteToPlayer(Player $player, string $reason, int $t): bool{
    $time = strtotime($t);
    $this->db->query("INSERT INTO mutes (player, mutetime, reason) VALUES ('" . $player->getName() . "', " . $time . ", '" . $this->db->real_escape_string($reason) . "')");
    return false;
  }

    /**
     * @param Player $player
     * @return bool
     */
  public function removeMuteFromPlayer(Player $player) :bool{
		if($this->db->query("DELETE FROM mute WHERE username='".$this->db->real_escape_string(strtolower($player->getName()))."'") === true) return true;
		return false;
	}

    public function query(string $string)
    {
    }
}