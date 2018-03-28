<?php
/**
 * Created by PhpStorm.
 * User: EmreTr1
 * Date: 28.03.2018
 * Time: 20:34
 */

namespace pocketmine\entity\behavior;


use pocketmine\entity\Animal;

class MateBehavior extends Behavior
{
	protected $speedMultiplier;
	protected $spawnBabyDelay = 0;
	protected $targetMate;

	public function __construct(Animal $mob, float $speedMultiplier)
	{
		parent::__construct($mob);

		$this->speedMultiplier = $speedMultiplier;
	}

	public function canStart(): bool
	{
		if($this->mob->isInLove()){
			$this->targetMate = $this->getNearbyMate();
			return $this->targetMate !== null;
		}

		return false;
	}

	public function canContinue(): bool
	{
		return $this->targetMate->isAlive() and $this->targetMate->isInLove() and $this->spawnBabyDelay < 60;
	}

	public function onTick(int $tick): void
	{
		$this->mob->getNavigator()->tryMoveTo($this->targetMate, $this->speedMultiplier);

		$this->spawnBabyDelay++;

		if($this->spawnBabyDelay >= 60 and $this->mob->distance($this->targetMate) < 9){
			$this->spawnBaby();
		}
	}

	public function onEnd(): void
	{
		$this->targetMate = null;
		$this->spawnBabyDelay = 0;
	}

	public function getNearbyMate() : ?Animal{
		$list = $this->mob->level->getNearbyEntities($this->mob->getBoundingBox()->grow(8,8,8), $this->mob);
		$dist = PHP_INT_MAX;
		$animal = null;

		foreach ($list as $entity){
			if($entity instanceof Animal and $entity->isInLove() and $entity->distance($this->mob) < $dist and $entity->getSaveId() === $this->mob->getSaveId()){
				$dist = $entity->distance($this->mob);
				$animal = $entity;
			}
		}

		return $animal;
	}

	private function spawnBaby() : void{
		// TODO: Spawn baby
	}

}