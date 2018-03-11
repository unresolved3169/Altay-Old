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

namespace pocketmine\entity\behavior\navigator;

use pocketmine\entity\behavior\navigator\algorithms\DistanceAlgorithm;
use pocketmine\entity\behavior\navigator\providers\{NeighborProvider, BlockedProvider};

class TileNavigator{

    /** @var BlockedProvider */
	private $blockedProvider;
	/** @var NeighborProvider */
	private $neighborProvider;
	/** @var DistanceAlgorithm */
	private $distanceAlgorithm;
	/** @var DistanceAlgorithm */
	private $heuristicAlgorithm;
	
	public function __construct(BlockedProvider $blockedProvider, NeighborProvider $neighborProvider, DistanceAlgorithm $distanceAlgorithm, DistanceAlgorithm $heuristicAlgorithm){
		$this->blockedProvider = $blockedProvider;
		$this->neighborProvider = $neighborProvider;
		$this->distanceAlgorithm = $distanceAlgorithm;
		$this->heuristicAlgorithm = $heuristicAlgorithm;
	}
	
	public function navigate(Tile $from, Tile $to, int $maxAttempts = PHP_INT_MAX) : array{
		$closed = [];
		$open = [$from];
		$path = [];
		
		$from->fScore = $this->heuristicAlgorithm->calculate($from, $to);
		
		$noOfAttempts = 0;

		$highScore = $from;
		$last = $from;
		while(!empty($open)){
			$current = $last;
			if($last !== $highScore){
                usort($open, function($a, $b){
                    if($a->fScore == $b->fScore) return 0;

                    return ($a->fScore > $b->fScore) ? 1 : -1;
                });
				$current = reset($open);
			}
			
			$last = null;
			
			if(++$noOfAttempts > $maxAttempts){
				return array_reverse($path);
			}
			if($current->equals($to)){
				return array_reverse($path);
			}
			
			unset($open[array_search($current, $open)]);
			$closed[] = $current;

			foreach($this->neighborProvider->getNeighbors($current) as $neighbor){
				if(in_array($neighbor, $closed) or $this->blockedProvider->isBlocked($neighbor)){
					continue;
				}
				
				$tentativeG = $current->gScore + $this->distanceAlgorithm->calculate($current, $neighbor);
				
				/*if(in_array($neighbor, $open) and $tentativeG >= $neighbor->gScore){
					continue;
				}*/
				
				$path[$neighbor->getHashCode()] = $current;
				
				$neighbor->gScore = $tentativeG;
				$neighbor->fScore = $neighbor->gScore + $this->heuristicAlgorithm->calculate($neighbor, $to);
				if($neighbor->fScore <= $highScore->fScore){
					$highScore = $neighbor;
					$last = $neighbor;
				}
       $open[] = $neighbor;
			}
		}
		
		return [];
	}
}