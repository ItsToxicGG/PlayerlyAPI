<?php

namespace Statics\task;

use pocketmine\scheduler\Task;
use pocketmine\player\Player;

class SessionTimeTask extends Task {

/** @var \mysqli */
private $db;

public function __construct(\mysqli $db) {
    $this->db = $db;
}

public function onRun(int $currentTick) {
    foreach (Server::getInstance()->getOnlinePlayers() as $player) {
        $username = strtolower($player->getName());
        $sessionTime = $this->getSessionTime($username);
        $this->database->query("UPDATE stats SET time = $sessionTime WHERE username = '$username'");
    }
}

public function getSessionTime($username) {
    $username = $player->getDisplayName();
    $result = $this->database->query("SELECT time FROM stats WHERE username = '$username'");
    if ($result instanceof \mysqli_result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $sessionTime = $row["time"] + 1;
    }
    return $sessionTime;
}
}