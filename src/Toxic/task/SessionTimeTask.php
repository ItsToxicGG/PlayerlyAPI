<?php

namespace Toxic\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\Server;

class SessionTimeTask extends Task {

/** @var \mysqli */
private $db;

public function __construct(\mysqli $db) {
    $this->db = $db;
}

public function onRun() : void {
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
        $username = strtolower($player->getName());
        $sessionTime = $this->getSessionTime($username);
        $this->db->query("UPDATE stats SET time = $sessionTime WHERE username = '$username'");
    }
}

public function getSessionTime($username) {
    $result = $this->db->query("SELECT time FROM stats WHERE username = '$username'");
    $sessionTime = 0;
    if ($result instanceof \mysqli_result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sessionTime = (int) $row["time"] + 1;
    }
    return $sessionTime;
}
}
