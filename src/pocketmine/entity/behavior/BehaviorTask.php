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

namespace pocketmine\entity\behavior;

class BehaviorTask{

	/** @var Behavior[] */
	protected $behaviors = [];
	/** @var Behavior|null */
	protected $currentBehavior = null;

	public function __construct(array $behaviors){
		$this->behaviors = $behaviors;
	}

	public function setBehavior(Behavior $behavior, int $index = null) : void{
		if($index === null){
			$this->behaviors[] = $behavior;
		}else{
			$this->behaviors[$index] = $behavior;
		}
	}

	public function removeBehavior(int $index) : void{
		unset($this->behaviors[$index]);
	}

	/**
	 * Checks behaviors to execute
	 */
	public function checkBehaviors() : void{
		foreach($this->behaviors as $index => $behavior){
			if($behavior == $this->currentBehavior){
				if($behavior->canContinue()){
					$behavior->onTick();
					break;
				}
				$behavior->onEnd();
				$this->currentBehavior = null;
			}
			if($behavior->canStart()){
				if($this->currentBehavior == null or (array_search($this->currentBehavior, $this->behaviors)) > $index){
					if($this->currentBehavior != null){
						$this->currentBehavior->onEnd();
					}
					$behavior->onStart();
					$behavior->onTick();
					$this->currentBehavior = $behavior;
					break;
				}
			}
		}
	}
}