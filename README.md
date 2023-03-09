# PlayerlyAPI
A API for https://github.com/ItsToxicGG/PlayerDataWebsitePMMP
# Config
enter the mysql data in config.yml
# How to interact with the api
Get & Set
```php
// to get or set something thats already in the api easily by

// player is usally in the function, example:
public function example(Player $player){ } // you see in the ( ) it defines Player to $player

// $stats how to get
use Toxic\Statics\Stats;

public Stats $stats;

public function onEnable(): void{
    $stats = $this->getServer()->getPluginManager()->getPlugin("PlayerlyAPI");
}

$this->stats->getKills($player); // $player
$this->stats->setWins($player, 1); // $player & an number/int/integar

```
Add & Remove
```php
$this->stats->addKill($player); // $player, only adds 1 kill
$this->stats->addKills($player, 5); // $player, add any amount of kills
$this->stats->removeWins($player, 1); // $player & an number/int/integar
```

Make
```php
soon!
```

# Tests
The tests for the website are at https://github.com/ItsToxicGG/PlayerDataWebsitePMMP

The tests in-game is below:

Stats UI:
