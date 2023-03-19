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

class BanAPI {

    public mysqli $db;	
	
    public Stats $plugin;	
	
    public function __construct(Stats $plugin){
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
        if($this->db->connect_error){
            die("Connection Failed" . $this->db->connect_error);
        }
        $querycontents = "CREATE TABLE IF NOT EXISTS bans (
            username VARCHAR(255) PRIMARY KEY,
            uuid VARCHAR(255), 
            xuid VARCHAR(50),
            reason TEXT,
            expiry_date INT
          );";
        $this->db->query($querycontents);
  }

  public function isBanned(string $playerName) : bool {
    $query = $this->db->prepare("SELECT * FROM bans WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    $result = $query->get_result();
    
    return $result->num_rows > 0;
}

public function banPlayer(string $playerName, string $reason, int $duration) : bool {
    $query = $this->db->prepare("INSERT INTO bans (`username`, `reason`, `expiry_date`) VALUES (?, ?, NOW() + INTERVAL ? SECOND)");
    $query->bind_param("ssi", $playerName, $reason, $duration);
    $query->execute();
    
    return $this->db->affected_rows > 0;
}

public function unbanPlayer(string $playerName) : bool {
    $query = $this->db->prepare("DELETE FROM bans WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    
    return $this->db->affected_rows > 0;
}

public function getBanReason(string $playerName) : ?string {
    $query = $this->db->prepare("SELECT `reason` FROM bans WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    $result = $query->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc()["reason"] : null;
}

public function getBanExpiryDate(string $playerName) : ?string {
    $query = $this->db->prepare("SELECT `expiry_date` FROM bans WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    $result = $query->get_result();
    
    return $result->num_rows > 0 ? $result->fetch_assoc()["expiry_date"] : null;
}
}