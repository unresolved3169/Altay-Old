<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol\types;

class EntityLink{

    public const TYPE_REMOVE = 0;
    public const TYPE_RIDE = 1;
    public const TYPE_PASSENGER = 0;

	/** @var int */
	public $riddenId;
	/** @var int */
	public $riderId;
	/** @var int */
	public $type;
	/** @var bool */
	public $bool1;

	public function __construct(int $riddenId = null, int $riderId = null, int $type = null, bool $bool1 = null){
		$this->riddenId = $riddenId;
		$this->riderId = $riderId;
		$this->type = $type;
		$this->bool1 = $bool1;
	}
}
