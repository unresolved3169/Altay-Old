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

namespace pocketmine\level\pathfinder;

use pocketmine\block\Air;
use pocketmine\block\Block;
use pocketmine\block\Door;
use pocketmine\block\Rail;
use pocketmine\block\IronDoor;
use pocketmine\entity\behaviors\pathfinding\PathPoint;
use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

class WalkNodeProcessor extends NodeProcessor{

    /** @var bool */
    private $canEnterDoors;
    /** @var bool */
    private $canBreakDoors;
    /** @var bool */
    private $avoidsWater;
    /** @var bool */
    private $canSwim;
    /** @var bool */
    private $shouldAvoidWater;

    public function initProcessor(Level $level, Entity $entity) : void{
        parent::initProcessor($level, $entity);
        $this->shouldAvoidWater = $this->avoidsWater;
    }

    public function postProcess() : void{
        parent::postProcess();
        $this->avoidsWater = $this->shouldAvoidWater;
    }

    public function getPathPointTo(Entity $entity) : PathPoint{
        if($this->canSwim && $entity->isInsideOfWater()){
            $i = (int)$entity->getBoundingBox()->minY;
            $mb = new Vector3(Math::floorFloat($entity->x), $i, Math::floorFloat($entity->z));

            for($blockId = $this->level->getBlock($mb)->getId(); $blockId == Block::FLOWING_WATER || $blockId == Block::WATER; $blockId = $this->level->getBlock($mb)->getId())
                $mb->y = ++$i;

            $this->avoidsWater = false;
        }else{
            $i = Math::floorFloat($entity->getBoundingBox()->minY + 0.5);
        }

        return $this->openPoint(Math::floorFloat($entity->getBoundingBox()->minX), $i, Math::floorFloat($entity->getBoundingBox()->minZ));
    }

    public function getPathPointToCoords(Entity $entity, float $x, float $y, float $target): PathPoint{
        return $this->openPoint(Math::floorFloat($x - ($entity->width / 2.0)), Math::floorFloat($y), Math::floorFloat($target - ($entity->width / 2.0)));
    }

    public function findPathOptions(array $pathOptions, Entity $entity, PathPoint $currentPoint, PathPoint $targetPoint, float $maxDistance): int{
        $i = $j = 0;

        if($this->getVerticalOffset($entity, $currentPoint->xCoord, $currentPoint->yCoord + 1, $currentPoint->zCoord) == 1) {
            $j = 1;
        }

        $pp = $this->getSafePoint($entity, $currentPoint->xCoord, $currentPoint->yCoord, $currentPoint->zCoord + 1, $j);
        $pp1 = $this->getSafePoint($entity, $currentPoint->xCoord - 1, $currentPoint->yCoord, $currentPoint->zCoord, $j);
        $pp2 = $this->getSafePoint($entity, $currentPoint->xCoord + 1, $currentPoint->yCoord, $currentPoint->zCoord, $j);
        $pp3 = $this->getSafePoint($entity, $currentPoint->xCoord, $currentPoint->yCoord, $currentPoint->zCoord - 1, $j);

        if($pp != null && !$pp->visited && $pp->distanceTo($targetPoint) < $maxDistance){
            $pathOptions[$i++] = $pp;
        }

        if($pp1 != null && !$pp1->visited && $pp1->distanceTo($targetPoint) < $maxDistance){
            $pathOptions[$i++] = $pp1;
        }

        if($pp2 != null && !$pp2->visited && $pp2->distanceTo($targetPoint) < $maxDistance){
            $pathOptions[$i++] = $pp2;
        }

        if($pp3 != null && !$pp3->visited && $pp3->distanceTo($targetPoint) < $maxDistance){
            $pathOptions[$i++] = $pp3;
        }

        return $i;
    }

    private function getSafePoint(Entity $entity, int $x, int $y, int $z, int $j): PathPoint{
        $pp = null;
        $i = $this->getVerticalOffset($entity, $x, $y, $z);

        if($i == 2){
            return $this->openPoint($x, $y, $z);
        }else{
            if($i == 1){
                $pp = $this->openPoint($x, $y, $z);
            }

            if($pp == null && $j > 0 && $i != -3 && $i != -4 && $this->getVerticalOffset($entity, $x, $y + $j, $z) == 1){
                $pp = $this->openPoint($x, $y + $j, $z);
                $y += $j;
            }

            if($pp != null){
                $j = 0;

                for($k = 0; $y > 0; $pp = $this->openPoint($x, $y, $z)){
                    $k = $this->getVerticalOffset($entity, $x, $y - 1, $z);

                    if($this->avoidsWater && $k == -1)
                        return null;

                    if($k != 1)
                        break;

                    if($j++ >= 3 or --$y <= 0)
                        return null;
                }

                if($k == -2){
                    return null;
                }
            }

            return $pp;
        }
    }

    private function getVerticalOffset(Entity $entity, int $x, int $y, int $z){
        $flag = false;
        $pos = $entity->asVector3();
        $mb = new Vector3();

        for($i = $x; $i < $x + $this->entitySize->x; ++$i)
            for($j = $y; $j < $y + $this->entitySize->y; ++$j)
                for($k = $z; $k < $z + $this->entitySize->z; ++$k){
                    $mb->setComponents($i, $j, $k);
                    $block = $this->level->getBlock($mb);

                    if(!($block instanceof Air)){
                        $id = $block->getId();
                        if($id != Block::TRAPDOOR && $id != Block::IRON_TRAPDOOR){
                            if($id != Block::FLOWING_WATER && $id != Block::WATER)
                                if(!$this->canEnterDoors && ($block instanceof Door and !($block instanceof IronDoor)))
                                    return 0;
                            else{
                                if($this->avoidsWater)
                                    return -1;

                                $flag = true;
                            }
                        }else
                            $flag = true;

                        if($entity->level->getBlock($mb) instanceof Rail){
                            $posBlock = $entity->level->getBlock($pos);
                            if(!($posBlock instanceof Rail) && !($posBlock->getSide(Vector3::SIDE_DOWN) instanceof Rail))
                                return -3;
                        }elseif((!$this->canBreakDoors || !($block instanceof IronDoor))){
                            switch($id){
                                case Block::FENCE:
                                case Block::FENCE_GATE:
                                case Block::STONE_WALL:
                                    return -3;
                                case Block::TRAPDOOR:
                                case Block::IRON_TRAPDOOR:
                                    return -4;
                                case Block::LAVA:
                                case Block::FLOWING_LAVA:
                                    if(!$entity->isInsideOfLava())
                                        return -2;
                                    break;
                                default:
                                    return 0;
                            }
                        }
                    }
                }


        return $flag ? 2 : 1;
    }

    public function canBreakDoors() : bool{
        return $this->canBreakDoors;
    }

    public function canEnterDoors() : bool{
        return $this->canEnterDoors;
    }

    public function canSwim(): bool{
        return $this->canSwim;
    }

}