<?php

namespace pocketmine\utils\navigator;

class TileNavigator{
	
	private $blockedProvider;
	private $neighborProvider;
	private $distanceAlgorithm;
	private $heuristicAlgorithm;
	
	public function __construct(BlockedProvider $bp, NeighborProvider $np, DistanceAlgorithm $da, HeuristicAlgorithm $ha){
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
	
	public function insertToArray(array &$a, $key, $value) : void{
		$result = [];
		$numeric = is_numeric($key);
		foreach($a as $k => $v){
			if($k === $key){
				$result[$key] = $value;
			}else{
				if($numeric and is_numeric($k)){
					$k += 1;
				}
				$result[$k] = $v;
			}
		}
		$a = $result;
	}
}