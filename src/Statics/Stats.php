<?php

namespace Statics;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use Statics\api\StatsAPI;
use Statics\command\StatsCommand;

class Stats extends PluginBase implements Listener {

    /** @var StatsAPI $s */
    private $s;

    public function onEnable(): void{
        $this->getLogger()->info("PlayerlyAPI");
        $this->getLogger()->info("Warning: Earlier Beta");
        $this->getLogger()->info("Early Beta, pls beware of bugs.");
        $this->getServer()->getPluginManager()->registerEvents($this, $this); 
        $this->getServer()->getCommandMap()->register("stats", new StatsCommand($this));
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
    if(!$this->getStatsAPI()->accountExists(strtolower($player->getName()))){
        $this->getStatsAPI()->db->query("INSERT INTO `stats`
            (`username`, `xuid`, `breaks`, `places`, `deaths`, `kicked`, `banned`,`kills`,`wins`)
            VALUES
            ('".$this->getStatsAPI()->db->escape_string(strtolower($player->getDisplayName()))."', '".$this->getStatsAPI()->db->real_escape_string(strtolower($player->getXuid()))."', '0','0','0','0','0','0','0')
        ");
    } 
    }
}
