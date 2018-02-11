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

namespace pocketmine\inventory\utils;

class EquipmentSlot{

    /* For MobEquipmentPacket */
    public const MAINHAND = 0;
    // TODO : ADD OFFHAND

    /** For MobArmorEquipmentPacket */
    public const HEAD = 0;
    public const CHEST = 1;
    public const LEGS = 2;
    public const FEET = 3;

    public const HACK_OFFHAND = 1;
    public const HACK_HEAD = 2;
    public const HACK_CHEST = 3;
    public const HACK_LEGS = 4;
    public const HACK_FEET = 5;
}