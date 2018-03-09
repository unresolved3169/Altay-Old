<?php

/*
 *               _ _
 *         /\   | | |
 *        /  \  | | |_ __ _ _   _
 *       / /\ \ | | __/ _` | | | |
 *      / ____ \| | || (_| | |_| |
 *     /_/    \_|_|\__\__,_|\__, |
 *                           __/ |
 *                          |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://github.com/TuranicTeam/Altay
 *
 */

declare(strict_types=1);

namespace pocketmine\entity\projectile;

use pocketmine\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;

class EnderPearl extends Throwable{

    public const NETWORK_ID = EntityIds::ENDER_PEARL;

    public $width = 0.25;
    public $height = 0.25;

    protected $gravity = 0.03;
    protected $drag = 0.01;

    protected function onHit(ProjectileHitEvent $event) : void{
        if(($player = $this->getOwningEntity()) instanceof Player && $player->isAlive() && $this->y > 0){
            // TODO : %5 spawn endermites (when added endermites on Altay)
            $player->attack(new EntityDamageEvent($player, EntityDamageEvent::CAUSE_FALL, 5));
            $player->getLevel()->broadcastLevelEvent(new Vector3($this->x + (mt_rand()/mt_getrandmax()) * 2 - 0.5, $this->y + + (mt_rand()/mt_getrandmax()) * 0.5 + 0.5, $this->z + (mt_rand()/mt_getrandmax()) * 2 - 0.5), LevelEventPacket::EVENT_PARTICLE_PORTAL);
            $player->getLevel()->broadcastLevelEvent($this, LevelEventPacket::EVENT_SOUND_ENDERMAN_TELEPORT);
            $player->teleport($this);
        }
    }
}