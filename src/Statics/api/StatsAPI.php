<?php

namespace Statics\api;

use pocketmine\player\Player;
use pocketmine\utils\Config;
use Statics\Stats;
use mysqli;

class StatsAPI {

    public mysqli $db;	
	
    public Stats $plugin;	
	
    public function __construct(Stats $plugin){
        $this->plugin = $plugin;
        $config = $this->plugin->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database']);
        if($this->db->connect_error){
            die("Connection Failed" . $this->db->connect_error);
        }
        $querycontents = "CREATE TABLE IF NOT EXISTS stats (
            username VARCHAR(255) PRIMARY KEY, 
            xuid VARCHAR(50),
            breaks INT DEFAULT 0,
            places INT DEFAULT 0,
            deaths INT DEFAULT 0,
            kicked INT DEFAULT 0,
            banned INT DEFAULT 0,
            kills INT DEFAULT 0,
            wins INT DEFAULT 0,
            time INT,
            playtime INT
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

        /**
     * @param Player $player
     * @return null|int
     */
    public function getKicks(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT kicked FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
		$ret = $res->fetch_array()[0] ?? false;
		$res->free();
		return $ret;
    }

       /**
     * @param Player $player
     * @return null|int
     */
    public function getBans(Player $player){
		$name = trim(strtolower($player->getName()));
		$res = $this->db->query("SELECT banned FROM stats WHERE username='".$this->db->real_escape_string($name)."'");
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

    // kinda useless via removing

    public function removeKills(Player $player, int $amount){
		$calculate = $this->getKills($player) - $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET kills = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function removeWins(Player $player, int $amount){
		$calculate = $this->getWins($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET wins = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
    }

    public function removeDeaths(Player $player, int $amount){
		$calculate = $this->getDeaths($player) + $amount;
		if($player instanceof Player){
			$player = strtolower($player->getDisplayName());
		}
		return $this->db->query("UPDATE stats SET deaths = $calculate WHERE username='".$this->db->real_escape_string($player)."'");
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
}
