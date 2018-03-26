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
use pocketmine\utils\Random;

class RandomPositionGenerator{

	/** @var Vector3 */
	private static $staticVector;

	public static function findRandomTarget(Mob $mob, int $xz, int $y): ?Vector3{
		return self::findRandomTargetBlock($mob, $xz, $y, null);
	}

	public static function findRandomTargetBlockTowards(Mob $mob, int $xz, int $y, Vector3 $target): ?Vector3{
		self::$staticVector = $target->subtract($mob);
		return self::findRandomTargetBlock($mob, $xz, $y, self::$staticVector);
	}

	public static function findRandomTargetBlockAwayFrom(Mob $mob, int $xz, int $y, Vector3 $target): ?Vector3{
		self::$staticVector = $mob->subtract($target);
		return self::findRandomTargetBlock($mob, $xz, $y, self::$staticVector);
	}

	private static function findRandomTargetBlock(Mob $entity, int $xz, int $y, ?Vector3 $target): ?Vector3{
		$random = new Random();
		$flag = false;
		$i = $j = $k = 0;
		$f = -99999.0;

		if($entity->hasHome()){
			$d0 = $entity->getHomePosition()->distanceSquared(new Vector3(Math::floorFloat($entity->x), Math::floorFloat($entity->y), Math::floorFloat($entity->z))) + 4.0;
			$d1 = $entity->getMaximumHomeDistance() + $xz;
			$flag1 = $d0 < $d1 * $d1;
		}else{
			$flag1 = false;
		}

		for($j1 = 0; $j1 < 10; ++$j1){
			$l = $random->nextBoundedInt(2 * $xz + 1) - $xz;
			$k1 = $random->nextBoundedInt(2 * $y + 1) - $y;
			$i1 = $random->nextBoundedInt(2 * $xz + 1) - $xz;

			if($target == null || $l * $target->x + $i1 * $target->z >= 0.0){
				if($entity->hasHome() && $xz > 1){
					$blockpos = $entity->getHomePosition();

					if($entity->x > $blockpos->getX()){
						$l -= $random->nextBoundedInt($xz / 2);
					}else{
						$l += $random->nextBoundedInt($xz / 2);
					}

					if($entity->z > $blockpos->z){
						$i1 -= $random->nextBoundedInt($xz / 2);
					}else{
						$i1 += $random->nextBoundedInt($xz / 2);
					}
				}

				$l += Math::floorFloat($entity->x);
				$k1 += Math::floorFloat($entity->y);
				$i1 += Math::floorFloat($entity->z);
				$blockpos1 = new Vector3($l, $k1, $i1);

				if(!$flag1 || $entity->isWithinHomeDistanceFromPosition($blockpos1)){
					$f1 = $entity->getBlockPathWeight($blockpos1);

					if($f1 > $f){
						$f = $f1;
						$i = $l;
						$j = $k1;
						$k = $i1;
						$flag = true;
					}
				}
			}
		}

		return $flag ? new Vector3($i, $j, $k) : null;
	}

}