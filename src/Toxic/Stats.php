<?php

namespace Toxic;

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

use mysqli;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerKickEvent, PlayerChatEvent};
use Toxic\api\{StatsAPI, MuteAPI, BanAPI};
use Toxic\task\SessionTimeTask;
use Toxic\command\{MuteCmd, UnMuteCmd};
use pocketmine\player\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use lib\FormsUI\forms\Vecnavium\FormsUI\CustomForm;

class Stats extends PluginBase implements Listener {

    /** @var StatsAPI $s */ 
    private StatsAPI $s;

    /** @var BanAPI $b */
    private BanAPI $b;

    /** @var MuteAPI $m */
    private MuteAPI $m;

    /** @var mysqli $db */
    private mysqli $db;

    private $loggedIn = [];

    public function onLoad(): void{
        $config = $this->getConfig()->get("mysql-settings");
        $this->db = new mysqli($config['host'], $config['user'], $config['password'], $config['database'], $config['port']);
    }

    public function onEnable(): void{
        $this->getServer()->getPluginManager()->registerEvents($this, $this); 
        $this->getServer()->getCommandMap()->register("mute", new MuteCmd($this));
        $this->getServer()->getCommandMap()->register("unmute", new UnMuteCmd($this));
        $this->getScheduler()->scheduleRepeatingTask(new SessionTimeTask($this->db), 1200);
        $this->s = new StatsAPI($this);
        $this->m = new MuteAPI($this);
        $this->b = new BanAPI($this);
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool {
        if(strtolower($command->getName()) === "stats") {
            if(!isset($args[0])) {
                if(!$sender instanceof Player) {
                    $sender->sendMessage("Please specify a player.");
                    return true;
                }
                $playerName = strtolower($sender->getName());
            } else {
                $playerName = strtolower($args[0]);
            }
            $query = "SELECT * FROM stats WHERE username = '?'";
            $stmt = $this->getStatsAPI()->db->prepare($query);
            $stmt->bind_param("s", $playerName);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows === 0) {
                $sender->sendMessage("Stats not found for $playerName.");
                return true;
            }
            $row = $result->fetch_assoc();
            $kills = $row['kills']; $wins = $row['wins'];
            $deaths = $row['deaths'];
            $txt = 
            " Name: $playerName\n".
            " Level: Soon\n".
            " Xp: Soon\n".
            " Rank: Soon\n\n".
            " Coins: Soon\n".
            " Points: Soon\n\n".
            " Kills: $kills\n".
            " Wins: $wins\n".
            " Deaths: $deaths\n".
            " KDR: Soon\n\n".
            " Banned: Soon\n".
            " Kicked: Soon\n".
            " Muted: Soon\n"
            ;
            $sender->sendMessage($txt);
        }
        return false;
    }

    public function getStatsAPI(): StatsAPI
    {
        return $this->s;
    }

    public function getMuteAPI(): MuteAPI
    {
        return $this->m;
    }

    public function getBanAPI(): BanAPI
    {
        return $this->b;
    }

    public function loginForm($player){
        $form = new CustomForm(function(Player $player, $data) {
            if ($data === null) {
                // Player closed the form
                return;
            }

            // Check if username and password are correct
            $username = $data[0];
            $password = $data[1];
            $stmt = $this->db->prepare("SELECT * FROM stats WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            if ($row && password_verify($password, $row["password"])) {
                // Login successful
                $this->setLoggedIn($player, true);
                $player->sendMessage("Welcome back, " . $row["username"] . "!");
            } else {
                // Login failed
                $player->sendMessage("Incorrect username or password");
            }
        });

        $form->setTitle("Login");
        $form->addInput("Username", "Enter your Username here");
        $form->addInput("Password", "Enter your password here");
        $form->sendToPlayer($player);
    }

    public function registerForm($player){
        $form = new CustomForm(function(Player $player, $data) {
            if ($data === null) {
                // Player closed the form
                return;
            }

            // Insert new user into database
            $username = $data[0];
            $password = password_hash($data[1], PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO stats (name, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                // Registration successful
                $this->setLoggedIn($player, true);
                $player->sendMessage("Account created successfully");
            } else {
                // Registration failed
                $player->sendMessage("Failed to create account");
            }
        });

        $form->setTitle("Register");
        $form->addInput("Username", "Enter your username here");
        $form->addInput("Password", "Enter your password here");
        $form->sendToPlayer($player);
    }

    private function setLoggedIn(Player $player, $loggedIn){
        $username = $player->getName();
        if($loggedIn) {
            $this->loggedIn[$username] = true;
        } else {
            unset($this->loggedIn[$username]);
        }
    }

    public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $date = date("Y-m-d H:i:s");
    if(!$this->getStatsAPI()->accountExists(strtolower($player->getName()))){
        if(!$player->hasPlayedBefore()){
        $this->getStatsAPI()->db->query("INSERT INTO `stats`
            (`username`, `uuid`, `xuid`, `breaks`, `places`, `deaths`,`kills`,`wins`,`time`, `playtime`, `joined`)
            VALUES
            ('".$this->getStatsAPI()->db->escape_string(strtolower($player->getDisplayName()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getUniqueId()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getXuid()))."', '0','0','0','0','0','1','0', '$date')
        ");
        $player->sendMessage("Welcome to the server for the first time!");
        }
    } 
    if($this->getConfig()->get("auth-system") === true){
    $username = $player->getName();
    if (isset($this->loggedIn[$username])) {
        return;
    }
    $stmt = $this->db->prepare("SELECT * FROM stats WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row) {
        // Player is registered, show login form
        $this->loginForm($player);
    } else {
        // Player is not registered, show registration form
        $this->registerForm($player);
    }
    }  
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $username = strtolower($player->getName());
        $sessionTime = $this->getSessionTime($username);
        $playtime = $this->getPlaytime($player);
        $this->getStatsAPI()->db->query("UPDATE stats SET playtime = '$playtime' WHERE username = '$username'");
        $this->getStatsAPI()->db->query("UPDATE stats SET time = $sessionTime WHERE username = '$username'");
    }

    public function onKick(PlayerKickEvent $event){
        $player = $event->getPlayer();
        $username = strtolower($player->getName());
        $playtime = $this->getPlaytime($player);
        $this->getStatsAPI()->db->query("UPDATE stats SET playtime = '$playtime' WHERE username = '$username'");
    }

    public function getSessionTime($username) {
        $player = $this->getServer()->getPlayerExact($username);
        if ($player instanceof Player && $player->isOnline()) {
            $sessionTime = intval($this->getStatsAPI()->db->query("SELECT time FROM stats WHERE username = '$username'")->fetch_assoc()["time"]) + 1;
        } else {
            $sessionTime = intval($this->getStatsAPI()->db->query("SELECT time FROM stats WHERE username = '$username'")->fetch_assoc()["time"]);
        }
        return $sessionTime;
    }

    public function getPlaytime(Player $player){
        $username = strtolower($player->getName());
        $result = $this->getStatsAPI()->db->query("SELECT * FROM stats WHERE username = '$username'");
        $playtime = 0; // bug?
        if($result->num_rows === 1){
            $row = $result->fetch_assoc();
            $playtime += (int) $row["playtime"];
        }
        return $playtime;
    }

    public function formatTime($time) {
        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        $seconds = $time % 60;
        return "$hours:$minutes:$seconds";
    }

    public function onPlayerChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $username = $player->getName();
        $result = $this->getMuteAPI()->db->query("SELECT * FROM mute WHERE username = '" . $username . "' AND mutetime > " . time());
        if($result->num_rows > 0){
            $event->cancel();
            $row = $result->fetch_assoc();
            $remainingTime = intval($row['mutetime']) - time();
            $reason = $row['reason'];
            $player->sendMessage(TextFormat::RED . "You are muted for " . $remainingTime . " seconds. Reason: " . $reason);
        }        
    }
}

