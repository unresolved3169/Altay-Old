<?php

namespace pocketmine\utils\navigator;

class TileNavigator{
	
	private $blockedProvider;
	private $neighborProvider;
	private $distanceAlgorithm;
	private $heuristicAlgorithm;
	
	public function navigate(Tile $from, Tile $to, int $maxAttempts = PHP_INT_MAX){
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
	}
}