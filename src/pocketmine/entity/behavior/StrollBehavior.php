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

use pocketmine\entity\Mob;

class StrollBehavior extends Behavior
{

    /** @var int */
    protected $duration;
    /** @var float */
    protected $speedMultiplier;
    /** @var int */
    protected $timeLeft;

    public function __construct(Mob $mob, int $duration, float $speedMultiplier)
    {
        parent::__construct($mob);

        $this->duration = $this->timeLeft = $duration;
        $this->speedMultiplier = $speedMultiplier;
    }

    public function canStart(): bool
    {
        return $this->random->nextBoundedInt(120) == 0;
    }

    public function canContinue(): bool
    {
        return $this->timeLeft-- > 0;
    }

    public function onTick(): void
    {
        if (!$this->mob->moveForward($this->speedMultiplier)) {
            $rot = $this->random->nextBoundedInt(2) == 0 ? $this->random->nextMinMax(45, 180) : $this->random->nextMinMax(-180, -45);
            $this->mob->yaw += $rot;
            $this->mob->lookAt($this->mob->getDirectionVector());
        }
    }

    public function onEnd(): void
    {
        $this->timeLeft = $this->duration;
        $this->mob->resetMotion();
    }
}