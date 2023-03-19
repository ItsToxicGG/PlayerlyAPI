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

class StatsAPI {

    public mysqli $db;	
	
    public Stats $plugin;	
	
    public function __construct(Stats $plugin){
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
        if($this->db->connect_error){
            die("Connection Failed" . $this->db->connect_error);
        }
        // username & name is two different things unless the player sets the name in the
        // authh system if auth system on config is set to
        // true in config, but the name is selected by the player however the username is the players
        // name and cant be choosen but once join they username is set
        $querycontents = "CREATE TABLE IF NOT EXISTS stats (
            username VARCHAR(255) PRIMARY KEY,
            uuid VARCHAR(255), 
            xuid VARCHAR(50),
            name VARCHAR(50),
            breaks INT DEFAULT 0,
            places INT DEFAULT 0,
            deaths INT DEFAULT 0,
            kills INT DEFAULT 0,
            wins INT DEFAULT 0,
            time INT,
            playtime INT,
            joined DATETIME,
            password TEXT,
            coins INT DEFAULT 0
          );";
        $this->db->query($querycontents);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function accountExists(string $name){
		$result = $this->db->query("SELECT * FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		return $result->num_rows > 0 ? true:false;
	}

    /**
     * @param Player $player
     * @return bool
     */
    public function removeProfile(Player $player) :bool{
		if($this->db->query("DELETE FROM stats WHERE username='".$this->db->real_escape_string(strtolower($player->getName()))."'") === true) return true;
		return false;
	}

    /**
     * @param Player $player
     * @return null|int
     */
    public function getPlaces(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT places FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

    /**
     * @param Player $player
     * @return null|int
     */
    public function getKills(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT kills FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

        /**
     * @param Player $player
     * @return null|int
     */
    public function getDeaths(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT deaths FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

        /**
     * @param Player $player
     * @return null|int
     */
    public function getWins(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT wins FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

        /**
     * @param Player $player
     * @return null|int
     */
    public function getBreaks(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT breaks FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

    public function getCoins(Player $player){
      $name = trim(strtolower($player->getName()));
      $res = $this->db->query("SELECT coins FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
      $ret = $res->fetch_array()[0] ?? false;
      $res->free();
      return $ret;     
    }
    
    // adding

    public function addKills(Player $player, int $amount){
		$calculate = $this->getKills($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET kills = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function addWins(Player $player, int $amount){
		$calculate = $this->getWins($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET wins = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function addDeaths(Player $player, int $amount){
		$calculate = $this->getDeaths($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET deaths = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function addCoins(Player $player, int $amount){
      $calculate = $this->getCoins($player) + $amount;
      if($player instanceof Player){
        $player = strtolower($player->getDisplayName());
      }
      return $this->db->query("UPDATE stats SET coins = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }    

    // kinda useless via removing

    public function removeKills(Player $player, int $amount){
		$calculate = $this->getKills($player) - $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET kills = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function removeWins(Player $player, int $amount){
		$calculate = $this->getWins($player) - $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET wins = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function removeDeaths(Player $player, int $amount){
		$calculate = $this->getDeaths($player) - $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET deaths = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }
  
    public function removeCoins(Player $player, int $amount){
      $calculate = $this->getCoins($player) - $amount;
      if($player instanceof Player){
        $player = strtolower($player->getDisplayName());
      }
      return $this->db->query("UPDATE stats SET coins = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }   

    // pointless only if you wanna simp and give sum girl like 10000 kills or whatever or something
    
    public function setKills(Player $player, int $amount){
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET kills = $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function setWins(Player $player, int $amount){
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET wins = $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function setDeaths(Player $player, int $amount){
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET deaths = $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function setCoins(Player $player, int $amount){
      if($player instanceof Player){
        $player = strtolower($player->getDisplayName());
      }
      return $this->db->query("UPDATE stats SET coins = $amount WHERE username='".$this->db->real_escape_string($player)."'");
    }

    // resets

    public function resetCoins(Player $player){
        return $this->setCoins($player, 0);
    } 
}
