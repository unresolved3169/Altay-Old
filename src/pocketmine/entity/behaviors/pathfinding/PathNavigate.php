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

namespace pocketmine\entity\behaviors\pathfinding;

use pocketmine\entity\Entity;
use pocketmine\entity\Mob;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

abstract class PathNavigate{

    /** @var Mob */
    protected $entity;
    /** @var \pocketmine\level\Level */
    protected $level;

    /** @var PathEntity The PathEntity being followed. */
    protected $currentPath;
    /** @var float */
    protected $speed;

    /** @var float */
    private $pathSearchRange;

    /** @var int Time, in number of ticks, following the current path */
    private $totalTicks;

    /** @var int The time when the last position check was done (to detect successful movement) */
    private $ticksAtLastPos;

    /** @var Vector3 Coordinates of the entity's position last time a check was done (part of monitoring getting 'stuck') */
    private $lastPosCheck;
    /** @var float */
    private $heightRequirement = 1.0;
    /** @var PathFinder */
    private $pathFinder;

    public function __construct(Mob $entity){
        $this->entity = $entity;
        $this->level = $entity->level;
        $this->pathSearchRange = $entity->getFollowRange();
        $this->pathFinder = $this->getPathFinder();
        $this->lastPosCheck = new Vector3();
    }

    abstract protected function getPathFinder(): PathFinder;

    /**
     * Sets the speed
     *
     * @param float $speed
     */
    public function setSpeed(float $speed) : void{
        $this->speed = $speed;
    }

    /**
     * Gets the maximum distance that the path finding will search in.
     *
     * @return float
     */
    public function getPathSearchRange() : float{
        return $this->pathSearchRange;
    }

    /**
     * Returns the path to the given coordinates. Args : x, y, z
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @return PathEntity
     */
    public final function getPathToXYZ(float $x, float $y, float $z) : PathEntity{
        return $this->getPathToPos(new Vector3(Math::floorFloat($x), (int) $y, Math::floorFloat($z)));
    }

    /**
     * Returns path to given BlockPos
     *
     * @param Vector3 $v
     * @return null|PathEntity
     */
    public function getPathToPos(Vector3 $v){
        if(!$this->canNavigate()){
            return null;
        }else{
            $f = $this->getPathSearchRange();
            return $this->pathFinder->createEntityPathTo($this->level, $this->entity, $v, $f);
        }
    }

    /**
     * Try to find and set a path to XYZ. Returns true if successful. Args : x, y, z, speed
     *
     * @param float $x
     * @param float $y
     * @param float $z
     * @param float $speed
     * @return bool
     */
    public function tryMoveToXYZ(float $x, float $y, float $z, float $speed): bool{
        $pathEntity = $this->getPathToXYZ(Math::floorFloat($x), (int)$y, Math::floorFloat($z));
        return $this->setPath($pathEntity, $speed);
    }

    /**
     * Sets vertical space requirement for path
     *
     * @param float $jumpHeight
     */
    public function setHeightRequirement(float $jumpHeight): void{
        $this->heightRequirement = $jumpHeight;
    }

    /**
     * Returns the path to the given EntityLiving. Args : entity
     *
     * @param Entity $entity
     * @return PathEntity
     */
    public function getPathToEntityLiving(Entity $entity): PathEntity{
        if(!$this->canNavigate()){
            return null;
        }else{
            $f = $this->getPathSearchRange();
            return $this->pathFinder->createEntityPathTo($this->level, $this->entity, $entity, $f);
        }
    }

    /**
     * Try to find and set a path to EntityLiving. Returns true if successful. Args : entity, speed
     *
     * @param Entity $entity
     * @param float $speed
     * @return bool
     */
    public function tryMoveToEntityLiving(Entity $entity, float $speed) : bool{
        $pathentity = $this->getPathToEntityLiving($entity);
        return $pathentity != null ? $this->setPath($pathentity, $speed) : false;
    }

    /**
     * Sets a new path. If it's diferent from the old path. Checks to adjust path for sun avoiding, and stores start
     * coords. Args : path, speed
     *
     * @param PathEntity $pathEntity
     * @param float $speed
     * @return bool
     */
    public function setPath(PathEntity $pathEntity, float $speed) : bool{
        if($pathEntity == null){
            $this->currentPath = null;
            return false;
        }else{
            if(!$pathEntity->isSamePath($this->currentPath))
                $this->currentPath = $pathEntity;

            $this->removeSunnyPath();

            if($this->currentPath->getCurrentPathLength() == 0){
                return false;
            }else{
                $this->speed = $speed;
                $v3 = $this->getEntityPosition();
                $this->ticksAtLastPos = $this->totalTicks;
                $this->lastPosCheck = $v3;
                return true;
            }
        }
    }

    /**
     * Gets the actively used PathEntity
     *
     * @return PathEntity
     */
    public function getPath() : PathEntity{
        return $this->currentPath;
    }

    public function onUpdateNavigation() : void{
        ++$this->totalTicks;

        if(!$this->noPath()){
            if($this->canNavigate()){
                $this->pathFollow();
            }elseif($this->currentPath != null && $this->currentPath->getCurrentPathIndex() < $this->currentPath->getCurrentPathLength()){
                $v = $this->getEntityPosition();
                $v1 = $this->currentPath->getVectorFromIndex($this->entity, $this->currentPath->getCurrentPathIndex());

                if($v->y > $v1->y && !$this->entity->isOnGround() && Math::floorFloat($v->x) == MAth::floorFloat($v1->x) && Math::floorFloat($v->z) == Math::floorFloat($v1->z))
                    $this->currentPath->setCurrentPathIndex($this->currentPath->getCurrentPathIndex() + 1);
            }

            if(!$this->noPath()){
                $v2 = $this->currentPath->getPosition($this->entity);

                if($v2 != null){
                    $aabb = (new AxisAlignedBB($v2->x, $v2->y, $v2->z, $v2->x, $v2->y, $v2->z))->expand(0.5, 0.5, 0.5);
                    $list = $this->level->getCollidingEntities($aabb->addCoord(0.0, -1.0, 0.0), $this->entity);
                    $d0 = -1.0;
                    $aabb = $aabb->offset(0.0, 1.0, 0.0);

                    foreach($list as $entity)
                        $d0 = $entity->getBoundingBox()->calculateYOffset($aabb, $d0);

                    $this->entity->getMoveHelper()->setMoveTo($v2->add(0, $d0, 0), $this->speed);
                }
            }
        }
    }

    protected function pathFollow() : void{
        $v = $this->getEntityPosition();
        $i = $this->currentPath->getCurrentPathLength();

        for($j = $this->currentPath->getCurrentPathIndex(); $j < $this->currentPath->getCurrentPathLength(); ++$j){
            if($this->currentPath->getPathPointFromIndex($j)->yCoord != (int)$v->y) {
                $i = $j;
                break;
            }
        }

        $f = $this->entity->width * $this->entity->width * $this->heightRequirement;

        for($k = $this->currentPath->getCurrentPathIndex(); $k < $i; ++$k){
            $v1 = $this->currentPath->getVectorFromIndex($this->entity, $k);

            if($v->distanceSquared($v1) < $f)
                $this->currentPath->setCurrentPathIndex($k + 1);
        }

        $j1 = Math::ceilFloat($this->entity->width);
        $k1 = (int)$this->entity->height + 1;
        $l = $j1;

        for($i1 = $i - 1; $i1 >= $this->currentPath->getCurrentPathIndex(); --$i1){
            if($this->isDirectPathBetweenPoints($v, $this->currentPath->getVectorFromIndex($this->entity, $i1), $j1, $k1, $l)) {
                $this->currentPath->setCurrentPathIndex($i1);
                break;
            }
        }

        $this->checkForStuck($v);
    }

    /**
     * Checks if entity haven't been moved when last checked and if so, clears current {@link PathEntity}
     *
     * @param Vector3 $pos
     */
    protected function checkForStuck(Vector3 $pos) : void{
        if($this->totalTicks - $this->ticksAtLastPos > 100){
            if($pos->distanceSquared($this->lastPosCheck) < 2.25)
                $this->clearPathEntity();

            $this->ticksAtLastPos = $this->totalTicks;
            $this->lastPosCheck = $pos;
        }
    }

    /**
     * If null path or reached the end
     *
     * @return bool
     */
    public function noPath() : bool{
        return $this->currentPath == null || $this->currentPath->isFinished();
    }

    /**
     * Sets active PathEntity to null
     */
    public function clearPathEntity() : void{
        $this->currentPath = null;
    }

    protected abstract function getEntityPosition() : Vector3;

    /**
     * If on ground or swimming and can swim
     *
     * @return bool
     */
    protected abstract function canNavigate() : bool;

    /**
     * Trims path data from the end to the first sun covered block
     */
    protected function removeSunnyPath() : void{}

    /**
     * Returns true when an entity of specified size could safely walk in a straight line between the two points. Args:
     * pos1, pos2, entityXSize, entityYSize, entityZSize
     *
     * @param Vector3 $pos
     * @param Vector3 $pos2
     * @param int $sizeX
     * @param int $sizeY
     * @param int $sizeZ
     * @return bool
     */
    protected abstract function isDirectPathBetweenPoints(Vector3 $pos, Vector3 $pos2, int $sizeX, int $sizeY, int $sizeZ) : bool;

}