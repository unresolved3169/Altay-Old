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

use pocketmine\block\Block;
use pocketmine\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\ProjectileHitEvent;
use pocketmine\event\player\PlayerTeleportEvent;
use pocketmine\level\sound\EndermanTeleportSound;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\RayTraceResult;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\Player;
use pocketmine\Server;

class EnderPearl extends Throwable{

    public const NETWORK_ID = EntityIds::ENDER_PEARL;

    public $width = 0.25;
    public $height = 0.25;

    protected $gravity = 0.03;
    protected $drag = 0.01;

    protected function calculateInterceptWithBlock(Block $block, Vector3 $start, Vector3 $end) : ?RayTraceResult{
        if($block->getId() !== Block::AIR and empty($block->getCollisionBoxes())){
            //TODO: remove this once block collision boxes are fixed properly
            $bb = new AxisAlignedBB(
                $block->x,
                $block->y,
                $block->z,
                $block->x + 1,
                $block->y + 1,
                $block->z + 1
            );

            return $bb->calculateIntercept($start, $end);
        }

        return parent::calculateInterceptWithBlock($block, $start, $end);
    }

    protected function onHit(ProjectileHitEvent $event) : void{
        $owner = $this->getOwningEntity();
        if($owner !== null){
            //TODO: check end gateways (when they are added)
            //TODO: spawn endermites at origin
            if($owner instanceof Player){
                Server::getInstance()->getPluginManager()->callEvent($ev = new PlayerTeleportEvent($owner, PlayerTeleportEvent::CAUSE_ENDER_PEARL));
                if(!$ev->isCancelled()){
                    $this->level->broadcastLevelEvent($owner, LevelEventPacket::EVENT_PARTICLE_ENDERMAN_TELEPORT);
                    $this->level->addSound(new EndermanTeleportSound($owner));
                    $owner->teleport($event->getRayTraceResult()->getHitVector());
                    $this->level->addSound(new EndermanTeleportSound($owner));

                    $owner->attack(new EntityDamageEvent($owner, EntityDamageEvent::CAUSE_FALL, 5));
                    }
            }
        }

        $this->flagForDespawn();
    }
}