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

namespace pocketmine\entity\hostile;

use pocketmine\entity\WaterAnimal;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\math\Vector3;

class Guardian extends WaterAnimal{
    public const NETWORK_ID = self::GUARDIAN;

    /** @var Vector3 */
    public $swimDirection = null;
    public $swimSpeed = 0.5;

    private $switchDirectionTicker = 0;

    public function getName() : string{
        return "Guardian";
    }

    public function attack(EntityDamageEvent $source){
        parent::attack($source);
        if($source->isCancelled()){
            return;
        }

        if($source instanceof EntityDamageByEntityEvent){
            $this->swimSpeed = mt_rand(150 * 5, 350 * 5) / 2000;
            $e = $source->getDamager();
            if($e !== null){
                $this->swimDirection = (new Vector3($this->x - $e->x, $this->y - $e->y, $this->z - $e->z))->normalize();
            }
        }
    }

    public function entityBaseTick(int $tickDiff = 1) : bool{
        if($this->closed !== false){
            return false;
        }

        if(++$this->switchDirectionTicker === 100 or $this->isCollided){
            $this->switchDirectionTicker = 0;
            if(mt_rand(0, 100) < 50){
                $this->swimDirection = null;
            }
        }

        $hasUpdate = parent::entityBaseTick($tickDiff);

        if($this->isAlive()){

            if($this->y > 62 and $this->swimDirection !== null){
                $this->swimDirection->y = -0.5;
            }

            $inWater = $this->isInsideOfWater();
            if(!$inWater){
                $this->swimDirection = null;
            }elseif($this->swimDirection !== null){
                if($this->motionX ** 2 + $this->motionY ** 2 + $this->motionZ ** 2 <= $this->swimDirection->lengthSquared()){
                    $this->motionX = $this->swimDirection->x * $this->swimSpeed;
                    $this->motionY = $this->swimDirection->y * $this->swimSpeed;
                    $this->motionZ = $this->swimDirection->z * $this->swimSpeed;
                }
            }else{
                $this->swimDirection = $this->generateRandomDirection();
                $this->swimSpeed = mt_rand(50, 100) / 2000;
            }

            $f = sqrt(($this->motionX ** 2) + ($this->motionZ ** 2));
            $this->yaw = (-atan2($this->motionX, $this->motionZ) * 180 / M_PI);
            $this->pitch = (-atan2($f, $this->motionY) * 180 / M_PI);
        }

        return $hasUpdate;
    }

    protected function applyGravity(){
        if(!$this->isInsideOfWater()){
            parent::applyGravity();
        }
    }

}