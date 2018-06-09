<?php

/*
 *     _______
 *    |__   __|
 *       | |_   _ _ __ _____  ___   _
 *       | | | | | '__/ _ \ \/ / | | |
 *       | | |_| | | | (_) >  <| |_| |
 *       |_|\__,_|_|  \___/_/\_\\__, |
 *                               __/ |
 *                              |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author TuranicTeam
 * @link https://turanic.io
 * 
 */

declare(strict_types=1);

namespace pocketmine\level\biome;

/**
 * Polyfill class for biomes that are unknown to Altay
 */
class UnknownBiome extends Biome{

	public function getName() : string{
		return "Unknown";
	}
}