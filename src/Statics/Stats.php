<?php

namespace Statics;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerKickEvent};
use Statics\api\StatsAPI;
use Statics\task\SessionTimeTask;
use Statics\command\StatsCommand;
use pocketmine\player\Player;

class Stats extends PluginBase implements Listener {

    /** @var StatsAPI $s */
    private $s;

    public function onEnable(): void{
        $this->getLogger()->info("PlayerlyAPI");
        $this->getLogger()->info("Warning: Earlier Beta");
        $this->getLogger()->info("Early Beta, pls beware of bugs.");
        $this->getServer()->getPluginManager()->registerEvents($this, $this); 
        $this->getServer()->getCommandMap()->register("stats", new StatsCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new SessionTimeTask($this->getStatsAPI()->db), 1200);
        $this->s = new StatsAPI($this);
    }

    public function getStatsAPI(){
        return $this->s;
    }

    public function onLoad(): void{
        $this->getLogger()->notice("This Project is in earlier beta, pls beware of problems!");
    }

    public function onJoin(PlayerJoinEvent $event){
    $player = $event->getPlayer();
    $date = date("Y-m-d H:i:s");
    if(!$this->getStatsAPI()->accountExists(strtolower($player->getName()))){
        if(!$player->hasPlayedBefore()){
        $this->getStatsAPI()->db->query("INSERT INTO `stats`
            (`username`, `uuid`, `xuid`, `breaks`, `places`, `deaths`, `kicked`, `banned`,`kills`,`wins`,`time`, `playtime`, `joined`)
            VALUES
            ('".$this->getStatsAPI()->db->escape_string(strtolower($player->getDisplayName()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getUnquieId()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getXuid()))."', '0','0','0','0','0','0','0','1','0', '$date')
        ");
        $player->sendMessage("Welcome to the {$this->getConfig()->get("servername")} for the first time!");
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
            $sessionTime = $this->getStatsAPI()->db->query("SELECT time FROM stats WHERE username = '$username'")->fetch_assoc()["time"] + 1;
        } else {
            $sessionTime = $this->getStatsAPI()->db->query("SELECT time FROM stats WHERE username = '$username'")->fetch_assoc()["time"];
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
}
