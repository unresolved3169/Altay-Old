<?php

declare(strict_types=1);

namespace pocketmine\event\player;

use pocketmine\event\Cancellable;
use pocketmine\Player;

class PlayerTeleportEvent extends PlayerEvent implements Cancellable{

    const CAUSE_COMMAND = 1;
    const CAUSE_END_PORTAL = 2; // TODO: Implement the cause when portals work
    const CAUSE_ENDER_PEARL = 3;
    const CAUSE_NETHER_PORTAL = 4; // TODO: Implement the cause when portals work

    /** @var int */
    private $cause;

    public function __construct(Player $player, int $cause){
        $this->player = $player;
        $this->cause = $cause;
    }

    /**
     * @return int
     */
    public function getCause(){
        return $this->cause;
    }
}