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

class NickAPI {

    public mysqli $db;	
	
    public Stats $plugin;	
	
    public function __construct(Stats $plugin){
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
        if($this->db->connect_error){
            die("Connection Failed" . $this->db->connect_error);
        }
        $querycontents = "CREATE TABLE IF NOT EXISTS nicknames (
            username VARCHAR(255) PRIMARY KEY,
            uuid VARCHAR(255), 
            xuid VARCHAR(50),
            nickname VARCHAR(50),
          );";
        $this->db->query($querycontents);
  }

  public function hasNickname(string $playerName) : bool {
    $query = $this->db->prepare("SELECT * FROM nicknames WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    $result = $query->get_result();
    
    return $result->num_rows > 0;
}

public function setNickname(string $playerName, string $nickName){
    $stmt = $mysqli->prepare("INSERT INTO nicknames (username, nickname) VALUES (?, ?) ON DUPLICATE KEY UPDATE nickname = ?");
    $stmt->bind_param("sss", $playerName, $nickName, $nickName);
    $stmt->execute();
    $stmt->close();
    $mysqli->close();
}

public function removeNickname(string $playerName) : bool {
    $query = $this->db->prepare("DELETE FROM nicknames WHERE `username` = ?");
    $query->bind_param("s", $playerName);
    $query->execute();
    
    return $this->db->affected_rows > 0;
}

private function getNickname($playerName) {
    $result = "?";
    if($this->hasNickname($playerName)){
    $stmt = $this->db->prepare("SELECT nickname FROM nicknames WHERE username = ?");
    $stmt->bind_param("s", $playerName);
    $stmt->execute();
    $result = $stmt->get_result();
    $nickname = "";
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $nickname = $row["nickname"];
    }
    $stmt->close();
    $mysqli->close();
    $result = $nickname;
    } else if(!$this->hasNickname($playerName)) {
    $result = "No Nickname";
    }
    return $result;
}
}