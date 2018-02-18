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

namespace pocketmine\utils\navigator;

use pocketmine\utils\navigator\providers\{NeighborProvider, BlockedProvider};
use pocketmine\utils\navigator\algorithms\{DistanceAlgorithm, ManhattanHeuristicAlgorithm};

class TileNavigator{
	
	private $blockedProvider;
	private $neighborProvider;
	private $distanceAlgorithm;
	private $heuristicAlgorithm;
	
	public function __construct(BlockedProvider $bp, NeighborProvider $np, DistanceAlgorithm $da, ManhattanHeuristicAlgorithm $ha){
		$this->blockedProvider = $bp;
		$this->neighborProvider = $np;
		$this->distanceAlgorithm = $da;
		$this->heuristicAlgorithm = $ha;
	}
	
	public function navigate(Tile $from, Tile $to, int $maxAttempts = PHP_INT_MAX) : ?array{
		$closed = [];
		$open = [$from];
		$path = [];
		
		$from->fScore = $this->heuristicAlgorithm->calculate($from, $to);
		
		$noOfAttempts = 0;
		$highScore = $from;
		$last = $from;
		
		while(count($open) > 0){
			$current = $last;
			
			if($last !== $highScore){
				$current = first(usort($open, function($a, $b){
					if($a->fScore == $b->fScore) return 0;
					
					return ($a->fScore > $b->fScore) ? 1 : -1;
				}));
			}
			
			$last = null;
			
			if(++$noOfAttempts > $maxAttempts){
				$this->reConstructPath($path, $highScore);
			}
			if($current->equals($to)){
				$this->reConstructPath($path, $current);
			}
			
			$open[array_search($current, $open)];
			array_push($closed, $current);
			
			foreach($this->neighborProvider->getNeighbors($current) as $neighbor){
				if(in_array($neighbor, $closed) or $this->blockedProvider->isBlocked($neighbor)){
					continue;
				}
				
				$tentativeG = $current->gScore + $this->distanceAlgorithm->calculate($current, $neighbor);
				
				if(in_array($neighbor, $open) or $tentativeG >= $neighbor->gScore){
					continue;
				}
				
				$path[$neighbor->__toString()] = $current;
				
				$neighbor->gScore = $tentativeG;
				$neighbor->fScore = $neighbor->gScore + $this->heuristicAlgorithm->calculate($neighbor, $to);
				if($neighbor->fScore <= $highScore->fScore){
					$highScore = $neighbor;
					$last = $neighbor;
				}
			}
		}
		
		return null;
	}
	
	public function reConstructPath(array $path, Tile $current) : array{
		$totalPath = [$current];
		
		while(isset($path[$current->__toString()])){
			$current = $path[$current->__toString()];
			$this->insertToArray($totalPath, 0, $current);
		}
		
		unset($totalPath[0]);
		
		return $totalPath;
	}
	
	public function insertToArray(array &$a, int $key, $value) : void{
		$result = [];
		$changed = false;
		foreach($a as $k => $v){
			if(!is_numeric($k)) continue;
			
			if($k === $key){
				$result[$k] = $value;
				$result[$k + 1] = $v;
				$changed = true;
			}elseif($changed){
				$result[$k + 1] = $v;
			}else{
				$result[$k] = $v;
			}
		}
		$a = $result;
	}
}