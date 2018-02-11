<?php

namespace pocketmine\entiy\behavior\navigator;

use pocketmine\utils\navigator\Tile;
use pocketmine\utils\navigator\providers\NeighborProvider;
use pocketmine\level\Level;
use pocketmine\block\Block;
use pocketmine\math\Vector3;
use pocketmine\entity\Entity;

class BlockDiagonalNeighborProvider implements NeighborProvider{
	
	private $blockCache = [];
	private $entity;
	private $level;
	private $startY = 0;
	
	protected $neigbors = [
	[0,-1],
	[1,0],
	[0,1],
	[-1,0],
	[-1,-1],
	[1,-1],
	[1,1],
	[-1,1]];
	
	public function __construct(Level $level, int $startY, array $blockCache, Entity $entity){
		$this->level = $level;
		$this->startY = $startY;
		$this->blockCache = $blockCache;
		$this->entity = $entity;
	}
	
	public function getNeighbors(Tile $tile) : array{
		if(!isset($this->blockCache[$tile->__toString()])){
		    $block = $this->level->getBlock(new Vector3($tile->x, $this->startY, $tile->y));
		    $this->blockCache[$tile->__toString()] = $block;
		}
		
		$list = [];
		for ($index = 0; $index < count($this->neighbors); ++$index)
		{
			$item = new Tile($tile->x + $this->neighbors[$index][0], $tile->y + $this->neighbors[$index][1]);

			// Check for too high steps
			$$coord = new Vector3((int) $item->x, $block->y, (int) $item->y);
			if ($this->level->getBlock($$coord)->isSolid())
			{
				if ($this->entity->canClimb())
				{
				$blockUp = $this->level->getBlock($coord->add(0,1,0));
				$canMove = false;
				for ($i = 0; $i < 10; $i++)
				{
					if ($this->isBlocked($blockUp->asVector3()))
					{
						$blockUp = $this->level->getBlock($blockUp->add(0,1,0));
						continue;
					}

					$canMove = true;
					break;
				}

				if (!$canMove) continue;

				if ($this->isObstructed($blockUp)) continue;

				$this->blockCache[$item->__toString()] = $blockUp;
				}
				else
				{
				$blockUp = $this->level->getBlock($coord->add(0,1,0));
				if ($blockUp->isSolid())
				{
					// Can't jump
					continue;
				}

				if ($this->isObstructed($blockUp)) continue;

				$this->blockCache[$item->__toString()] = $blockUp;
				}
			}
			else
			{
				$blockDown = $this->level->getBlock($coord->add(0,-1,0));
				if (!$blockDown->isSolid())
				{
				if ($this->entity->canClimb())
				{
					$canClimb = false;
					$blockDown = $this->level->getBlock($blockDown->add(0,-1,0));
					for ($i = 0; $i < 10; $i++)
					{
						if (!$blockDown->isSolid())
						{
						$blockDown = $this->level->getBlock($blockDown->add(0,-1,0));
						continue;
						}

						$canClimb = true;
						break;
					}

					if (!$canClimb) continue;

					$blockDown = $this->level->getBlock($blockDown->add(0,1,0));

					if ($this->isObstructed($blockDown)) continue;

					$this->blockCache[$item->__toString()] = $blockDown;
				}
				else
				{
					if (!$this->level->getBlock($coord->add(0,-2,0))->isSolid())
					{
						// Will fall
						continue;
					}

					if ($this->isObstructed($blockDown)) continue;

					$this->blockCache[$item->__toString()] = $blockDown;
				}
				}
				else
				{
				if ($this->isObstructed($coord)) continue;

				$this->blockCache[$item->__toString()] = $this->level->getBlock($coord);
				}
			}

			$list[] = $item;
		}

		$this->checkDiagonals($block, $list);

		return $list;
	}
	
	public function checkDiagonals(Block $block, array &$list){
		//if(!in_array($this->getTileFromBlock($block->getSide(Vector3::NORTH)))){
			//unset($list[array_search()]);
		//}
		// to be continue...
	}
	
	public function isObstructed(Vector3 $coord) : bool{
		for($i = 1; $i < $this->entity->height; $i++){
			if($this->isBlocked($coord->add(0,$i,0))) return true;
		}
		return false;
	}
	
	public function isBlocked(Vector3 $coord) : bool{
		$block = $this->level->getBlock($coord);
		
		if($block === null or $block->isSolid()){
			return true;
		}
		return false;
	}
	
	public function getTileFromBlock(Vector3 $coord) : Tile{
		return new Tile($coord->x, $coord->z);
	}
}