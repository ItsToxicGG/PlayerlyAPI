<?php

namespace Statics;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use Statics\api\StatsAPI;
use Statics\api\OtherStats;

class Stats extends PluginBase implements Listener {

    /** @var StatsAPI $s */
    private $s;

    /** @var OtherAPI $o */
    private $o;

    public function onEnable(): void{
        $this->getLogger()->info("PlayerlyAPI");
        $this->getLogger()->info("Warning: Earlier Beta");
        $this->getLogger()->info("Early Beta, pls beware of bugs.");
        $this->o = new OtherStats();
        $this->s = new StatsAPI($this);
    }

    public function getStatsAPI(){
        return $this->s;
    }

    public function getOtherStatsAPI(){
        return $this->o;
    }

    public function onLoad(): void{
        $this->getLogger()->notice("This Project is in earlier beta, pls beware of problems!");
    }

    public function onJoin(PlayerJoinEvent $event){
        $player = $event->getPlayer();
        /** 
         * No need for check because
         * in the function addPlayer
         * it already checks if the account
         * exists or not
        */
        $this->getStatsAPI()->addPlayer($player);
    }
}