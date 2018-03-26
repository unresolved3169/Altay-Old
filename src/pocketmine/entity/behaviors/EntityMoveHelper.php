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

namespace pocketmine\entity\behaviors;

use pocketmine\entity\Mob;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

class EntityMoveHelper{

    /** @var Mob */
    protected $entity;
    /** @var Vector3 */
    protected $pos;

    /** @var float The speed at which the entity should move */
    protected $speed;
    /** @var bool */
    protected $update;

    public function __construct(Mob $mob){
        $this->entity = $mob;
        $this->pos = $mob->asVector3();
    }

    public function isUpdating() : bool{
        return $this->update;
    }

    public function getSpeed() : float{
        return $this->speed;
    }

    public function setMoveTo(Vector3 $pos, float $speed) : void{
        $this->pos = $pos;
        $this->speed = $speed;
        $this->update = true;
    }

    public function onUpdateMoveHelper() : void{
        $this->entity->setMoveForward(0.0);

        if($this->update){
            $this->update = false;
            $i = Math::floorFloat($this->entity->getBoundingBox()->minY + 0.5);
            $fark = $this->pos->subtract($this->entity);
            $farkX = $fark->x;
            $farkZ = $fark->z;
            $farkY = $this->pos->y - $i;
            $fark = $farkX * $farkX + $farkY * $farkY + $farkZ * $farkZ;

            if($fark >= 2.500000277905201E-7){
                $f = (atan2($farkZ, $farkX) * 180.0 / M_PI) - 90.0;
                $this->entity->yaw = $this->limitAngle($this->entity->yaw, $f, 30.0);
                $this->entity->setAIMoveSpeed($this->speed * $this->entity->getMovementSpeed());

                if($farkY > 0.0 && $farkX * $farkX + $farkZ * $farkZ < 1.0)
                    $this->entity->getJumpHelper()->setJumping();
            }
        }
    }

    protected function limitAngle(float $yaw, float $f, float $f1){
        $wrap = Math::wrapAngleTo180($f - $yaw);

        if($wrap > $f1){
            $wrap = $f1;
        }

        if($wrap < -$f1){
            $wrap = -$f1;
        }

        $f2 = $yaw + $wrap;

        if($f2 < 0.0){
            $f2 += 360.0;
        }elseif($f2 > 360.0){
            $f2 -= 360.0;
        }

        return $f2;
    }

    public function getX() : float{
        return $this->pos->x;
    }

    public function getY() : float{
        return $this->pos->y;
    }

    public function getZ() : float{
        return $this->pos->z;
    }

}