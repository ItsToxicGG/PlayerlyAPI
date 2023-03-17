# PlayerlyAPI
request anything that should be implemented in PlayerlyAPI
# Todo-List
- [ ] Ban-System
- [X] Mute-System
- [X] StatsAPI
- [ ] Level-System
- [ ] Rank-System
- [ ] Coins-System
- [ ] Points-System
- [X] Mysql
# Config
enter the mysql data in config.yml
# How to Use the api
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
$this->stats->getDataBase()->addKill($player); // $player, only adds 1 kill
$this->stats->getDataBase()->addKills($player, 5); // $player, add any amount of kills
$this->stats->getDataBase()->removeWins($player, 1); // $player & an number/int/integar
```

Website
```php
include "config.php"; // where the database connection is (seperated from main)
// NOTE: that some things in this code you will have to handle your self (such as the $username)
$result = mysqli_query($conn, "SELECT * FROM stats WHERE username='$username'"); // $username must be handled by you & $conn is in the config.php
// check for query execution errors
if (!$result) {
     echo "Error executing query: " . mysqli_error($conn);
     exit();
}

while($row = mysqli_fetch_assoc($result)){
echo " - Name: " . $row["username"]. "<br>" ." - Kills: " . $row["kills"]. "<br>" . "- Wins:" . $row["wins"]. "<br>" . "- Deaths:" . $row["deaths"]. "<br>";
}
mysqli_close($conn);

?>
```
# Tests
**SOON

**SOON
