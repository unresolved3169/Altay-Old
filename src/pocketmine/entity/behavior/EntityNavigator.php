<?php

namespace pocketmine\entity\behavior;

use pocketmine\entity\Entity;
use pocketmine\math\Vector2;
use pocketmine\math\Vector3;

class EntityNavigator{

	protected $entity;
	protected $level;

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

	/**
	 * EntityNavigator constructor.
	 * @param Entity $entity
	 */
	public function __construct(Entity $entity)
	{
		$this->entity = $entity;
		$this->level = $entity->getLevel();
	}


	/**
	 * @param Vector2 $from
	 * @param Vector2 $to
	 * @param int $maxAttempt
	 * @return array
	 */
	public function navigate(Vector2 $from, Vector2 $to, int $maxAttempt = 200) : array
	{
		$attempt = 0;
		$current = $to;
		$level = $this->entity->level;
		$path = [];
		while(!$current->equals($from) and ++$attempt < $maxAttempt)
		{
			$last = null;
			foreach($this->getNeighbors($current) as $tile){
				if($last === null or $last->distance($from) >= $tile->distance($from)){
					$last = $tile;
                }
			}

			if($last !== null)
			{
				$path[] = $last;
				$current = $last;
			}else{
				break;
			}
		}

		$path = array_reverse($path);

		unset($path[0]);

		$path[] = $to;

		return $path;
	}

	public function getNeighbors(Vector2 $tile) : array{
		$block = $this->level->getBlock(new Vector3($tile->x, $this->entity->y, $tile->y));

		$list = [];
		for($index = 0; $index < count($this->neighbors); ++$index){
			$item = new Vector2($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);

			// Check for too high steps

			$coord = new Vector3((int)$item->x, $block->y, (int)$item->y);
			if($this->level->getBlock($coord)->isSolid()){
				if($this->entity->canClimb()){
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					$canMove = false;
					for($i = 0; $i < 10; $i++){
						if($this->isBlocked($blockUp->asVector3())){
							$blockUp = $this->level->getBlock($blockUp->getSide(Vector3::SIDE_UP));
							continue;
						}

						$canMove = true;
						break;
					}

					if(!$canMove) continue;

					if($this->isObstructed($blockUp)) continue;
				}else{
					$blockUp = $this->level->getBlock($coord->getSide(Vector3::SIDE_UP));
					if($blockUp->isSolid()){
						// Can't jump
						continue;
					}

					if($this->isObstructed($blockUp)) continue;
				}
			}else{
				$blockDown = $this->level->getBlock($coord->add(0, -1, 0));
				if(!$blockDown->isSolid()){
					if($this->entity->canClimb()){
						$canClimb = false;
						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_DOWN));
						for($i = 0; $i < 10; $i++){
							if(!$blockDown->isSolid()){
								$blockDown = $this->level->getBlock($blockDown->add(0, -1, 0));
								continue;
							}

							$canClimb = true;
							break;
						}

						if(!$canClimb) continue;

						$blockDown = $this->level->getBlock($blockDown->getSide(Vector3::SIDE_UP));

						if($this->isObstructed($blockDown)) continue;
					}else{
						if(!$this->level->getBlock($coord->getSide(Vector3::SIDE_DOWN, 2))->isSolid()){
							// Will fall
							continue;
						}

						if($this->isObstructed($blockDown)) continue;
					}
				}elseif($this->isObstructed($coord)) continue;
			}

			$list[] = $item;
		}

		return $list;
	}

	public function isObstructed(Vector3 $coord): bool{
		for($i = 1; $i < $this->entity->height; $i++)
			if($this->isBlocked($coord->add(0, $i, 0))) return true;

		return false;
	}

	public function isBlocked(Vector3 $coord): bool{
		$block = $this->level->getBlock($coord);
		return $block === null or $block->isSolid();
	}
}