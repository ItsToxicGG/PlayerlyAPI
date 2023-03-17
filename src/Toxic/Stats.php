<?php

namespace Statics;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerKickEvent};
use Toxic\api\{StatsAPI, MuteAPI, BanAPI};
use Toxic\task\SessionTimeTask;
use Toxic\command\{MuteCmd, UnMuteCmd};
use Vecnavium\FormsUI\SimpleForm;
use pocketmine\player\Player;

class Stats extends PluginBase implements Listener {

    /** @var StatsAPI $s */
    private $s;

    public function onEnable(): void{
        $this->getLogger()->info("PlayerlyAPI");
        $this->getLogger()->info("Warning: Earlier Beta");
        $this->getLogger()->info("Early Beta, pls beware of bugs.");
        $this->getServer()->getPluginManager()->registerEvents($this, $this); 
        $this->getServer()->getCommandMap()->register("mute", new MuteCommand($this));
        $this->getServer()->getCommandMap()->register("unmute", new UnMuteCommand($this));
        $this->getScheduler()->scheduleRepeatingTask(new SessionTimeTask($this->getStatsAPI()->db), 1200);
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
                $player = $sender;
            } else {
                $player = $this->getServer()->getPlayer($args[0]);
                if(!$player instanceof Player) {
                    $sender->sendMessage("The player by the name of {$args[0]} was not found.");
                    return true;
                }
            }
            $playerName = $player->getName();
            $kills = $this->getStatsAPI()->getKills($player); $wins = $this->getStatsAPI()->getWins($player);
            $deaths = $this->getStatsAPI()->getDeaths($player);
            $form = new SimpleForm(function (Player $player, $data) {
                if($data === null) {
                    return;
                }
            });
            $form->setTitle("$playerName Stats");
            $txt = 
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
            $form->setContent($txt);
            $form->setButton("Exit");
            $form->sendToPlayer($sender);
            return true;
        }
        return false;
    }

    public function getStatsAPI(){
        return $this->s;
    }

    public function getMuteAPI(){
        return $this->m;
    }

    public function getBanAPI(){
        return $this->b;
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
            ('".$this->getStatsAPI()->db->escape_string(strtolower($player->getDisplayName()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getUniqueId()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getXuid()))."', '0','0','0','0','0','0','0','1','0', '$date')
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
