<?php

namespace pocketmine\entity\behavior\navigator;

use pocketmine\entity\Entity;
use pocketmine\math\Vector2;

class EntityNavigator{
	
	protected $entity;
	
	protected $neighbors = [
        [0, -1],
        [1, 0],
        [0, 1],
        [-1, 0],
        [-1, -1],
        [1, -1],
        [1, 1],
        [-1, 1]
    ];
	
	public function __construct(Entity $entity){
		$this->entity = $entity;
	}
	
	public function navigate(Vector2 $from, Vector2 $to, int $maxAttempt = PHP_INT_MAX) : array{
		$current = $from;
		$attempts = 0;
		$path = [$from];
		$level = $this->entity->level;
		while($current->equals($to) or ++$attempts < $maxAttempt){
			$last = null;
			foreach($this->neighbors as $n){
				$tile = new Vector2($x = $this->entity->x + $n[0], $z = $this->entity->z + $n[1]);
				$coord = new Vector3($x, $this->entity->y, $z);
				
				$block = $level->getBlock($coord);
				
				if($last === null or $last->distance($to) > $tile->distance($to)){
					$last = $tile;
				}
			}
			if($last !== null){
				$path[] = $last;
			}
		}
		return $path;
	}
}