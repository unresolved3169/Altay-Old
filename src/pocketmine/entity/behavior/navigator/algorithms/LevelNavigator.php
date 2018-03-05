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

namespace pocketmine\entity\behavior\navigator\algorithms;

use pocketmine\entity\behavior\navigator\providers\BlockedProvider;
use pocketmine\entity\behavior\navigator\Tile;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\math\Vector2;
use pocketmine\entity\Entity;

class LevelNavigator implements BlockedProvider{

    /** @var Entity */
    private $entity;
    /** @var Vector3 */
    private $entityPos;
    /** @var Level */
    private $level;
    /** @var float */
    private $distance;
    /** @var Block[] */
    private $blockCache = [];
    /** @var Vector3[] */
    private $entityCoords = [];

    public function __construct(Entity $entity, Level $level, float $distance, array $blockCache, array $entityCoords){
        $this->entity = $entity;
        $this->entityPos = $entity->asVector3();
        $this->level = $level;
        $this->distance = $distance;
        $this->blockCache = $blockCache;
        $this->entityCoords = $entityCoords;
    }

    public function isBlocked(Tile $coord): bool{
        if (!isset($this->blockCache[$coord->__toString()])) {
            return true;
        }

        $block = $this->blockCache[$coord->__toString()];

        if ($block->isSolid()) return true;
        if (in_array($block->asVector3(), $this->entityCoords)) return true;

        $entityPos = new Vector2($this->entityPos->x, $this->entityPos->z);
        $tilePos = new Vector2((float)$coord->x, (float)$coord->y);

        if ($entityPos->distance($tilePos) > $this->distance) return true;

        if ($this->isObstructed($block->asVector3())) return true;

        return false;
    }

    public function isObstructed(Vector3 $coord): bool{
        for($i = 1; $i < $this->entity->height; $i++) {
            if($this->isBlock($coord->add(0, $i, 0))) return true;
        }

        return false;
    }

    public function isBlock(Vector3 $coord){
        $block = $this->level->getBlock($coord);
        return $block == null || $block->isSolid();
    }
}