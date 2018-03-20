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

use pocketmine\block\Block;
use pocketmine\level\pathfinder\WalkNodeProcessor;
use pocketmine\math\Math;
use pocketmine\math\Vector3;

class PathNavigateGround extends PathNavigate{

	/** @var WalkNodeProcessor */
	protected $nodeProcessor;
	/** @var bool */
	private $shouldAvoidSun;

	protected function getPathFinder() : PathFinder{
		$this->nodeProcessor = new WalkNodeProcessor();
		$this->nodeProcessor->setEnterDoors(true);
		return new PathFinder($this->nodeProcessor);
	}

	protected  function canNavigate(): bool{
		return $this->entity->onGround || $this->getCanSwim() && $this->entity->isInsideOfLiquid() /*|| $this->entity->isRiding() && $this->entity instanceof Zombie && $this->entity->ridingEntity instanceof Chicken*/;
	}

	protected  function getEntityPosition() : Vector3{
		return new Vector3($this->entity->x, $this->getPathablePosY(), $this->entity->z);
	}

	private function getPathablePosY() : int{
		if($this->entity->isInsideOfWater() && $this->getCanSwim()){
			$block = $this->entity->level->getBlock(new Vector3(Math::floorFloat($this->entity->x), (int)$this->entity->getBoundingBox()->minY, Math::floorFloat($this->entity->z)));
			$j = 0;

			while($block->getId() == Block::FLOWING_WATER || $block->getId() == Block::WATER){
				$block = $block->getSide(Vector3::SIDE_UP);
				if(++$j > 16)
					return (int)$this->entity->getBoundingBox()->minY;
			}

			return $block->getY();
		}else{
			return (int) ($this->entity->getBoundingBox()->minY + 0.5);
		}
	}

	protected function removeSunnyPath() : void{
		parent::removeSunnyPath();

		if($this->shouldAvoidSun){
			if($this->entity->level->canSeeSky(new Vector3(Math::floorFloat($this->entity->x), (int)($this->entity->getBoundingBox()->minY + 0.5), Math::floorFloat($this->entity->z)))){
				return;
			}

			for($i = 0; $i < $this->currentPath->getCurrentPathLength(); ++$i){
				$pathpoint = $this->currentPath->getPathPointFromIndex($i);

				if($this->entity->level->canSeeSky($pathpoint->toVector3())){
					$this->currentPath->setCurrentPathLength($i - 1);
					return;
				}
			}
		}
	}

	protected function isDirectPathBetweenPoints(Vector3 $pos, Vector3 $pos2, int $sizeX, int $sizeY, int $sizeZ) : bool{
		$i = Math::floorFloat($pos->x);
		$j = Math::floorFloat($pos->z);
		$fark = $pos2->subtract($pos);
		$farkX = $fark->x;
		$farkZ = $fark->z;
		$farkY = $farkX * $farkX + $farkZ * $farkZ;

		if($farkY < 1.0E-8){
			return false;
		}else{
			$d = 1.0 / sqrt($farkY);
			$farkX *= $d;
			$farkZ *= $d;
			$sizeX += 2;
			$sizeZ += 2;

			if(!$this->isSafeToStandAt($i, (int)$pos->y, $j, $sizeX, $sizeY, $sizeZ, $pos, $farkX, $farkZ)){
				return false;
			}else{
				$sizeX -= 2;
				$sizeZ -= 2;
				$d2 = 1.0 / abs($farkX);
				$d3 = 1.0 / abs($farkZ);
				$d4 = ($i * 1) - $pos->x;
				$d5 = ($j * 1) - $pos->z;

				if($farkX >= 0.0){
					++$d4;
				}

				if($farkZ >= 0.0){
					++$d5;
				}

				$d4 /= $farkX;
				$d5 /= $farkZ;
				$k = $farkX < 0.0 ? -1 : 1;
				$l = $farkZ < 0.0 ? -1 : 1;
				$i1 = Math::floorFloat($pos2->x);
				$j1 = Math::floorFloat($pos2->z);
				$k1 = $i1 - $i;
				$l1 = $j1 - $j;

				while($k1 * $k > 0 || $l1 * $l > 0){
					if($d4 < $d5){
						$d4 += $d2;
						$i += $k;
						$k1 = $i1 - $i;
					}else{
						$d5 += $d3;
						$j += $l;
						$l1 = $j1 - $j;
					}

					if(!$this->isSafeToStandAt($i, (int)$pos->y, $j, $sizeX, $sizeY, $sizeZ, $pos, $farkX, $farkZ)){
						return false;
					}
				}

				return true;
			}
		}
	}

	private function isSafeToStandAt(int $x, int $y, int $z, int $sizeX, int $sizeY, int $sizeZ, Vector3 $pos, float $f, float $f1){
		$i = $x - $sizeX / 2;
		$j = $z - $sizeZ / 2;

		if(!$this->isPositionClear($i, $y, $j, $sizeX, $sizeY, $sizeZ, $pos, $f, $f1)){
			return false;
		}else{
			for($k = $i; $k < $i + $sizeX; ++$k){
				for($l = $j; $l < $j + $sizeZ; ++$l){
					$d0 = $k + 0.5 - $pos->x;
					$d1 = $l + 0.5 - $pos->z;

					if($d0 * $f + $d1 * $f1 >= 0.0){
						$blockId = $this->entity->level->getBlock(new Vector3($k, $y - 1, $l))->getId();

						if($blockId == Block::AIR or $blockId == Block::LAVA or ($blockId == Block::WATER && !$this->entity->isInsideOfWater())){
							return false;
						}
					}
				}
			}

			return true;
		}
	}

	private function isPositionClear(int $x, int $y, int $z, int $sizeX, int $sizeY, int $sizeZ, Vector3 $pos, float $f, float $f1) : bool{
		$v = new Vector3();
		for($x1 = min($x, $x + $sizeX - 1); $x1 <= max($x, $x + $sizeX - 1); $x1++){
			for($y1 = min($y, $x + $sizeY - 1); $y1 <= max($y, $y + $sizeY - 1); $y1++){
				for($z1 = min($z, $z + $sizeZ - 1); $z1 <= max($z, $z + $sizeZ - 1); $z1++){
					$v->setComponents($x1, $y1, $z1);
					$d0 = $v->getX() + 0.5 - $pos->x;
					$d1 = $v->getZ() + 0.5 - $pos->z;
					if($d0 * $f + $d1 * $f1 >= 0.0){
						$block = $this->entity->level->getBlock($v);
						if(!$block->isTransparent()){
							return false;
						}
					}
				}
			}
		}

		return true;
	}

	public function setAvoidsWater(bool $value) : void{
		$this->nodeProcessor->setAvoidsWater($value);
	}

	public function getAvoidsWater() : bool{
		return $this->nodeProcessor->getAvoidsWater();
	}

	public function setBreakDoors(bool $value){
		$this->nodeProcessor->setBreakDoors($value);
	}

	public function setEnterDoors(bool $value){
		$this->nodeProcessor->setEnterDoors($value);
	}

	public function getEnterDoors() : bool{
		return $this->nodeProcessor->getEnterDoors();
	}

	public function setCanSwim(bool $value){
		$this->nodeProcessor->setCanSwim($value);
	}

	public function getCanSwim() : bool{
		return $this->nodeProcessor->getCanSwim();
	}

	public function setAvoidSun(bool $value){
		$this->shouldAvoidSun = $value;
	}

}