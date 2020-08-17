<?php

/*
 *
 *  ____  _             _         _____
 * | __ )| |_   _  __ _(_)_ __   |_   _|__  __ _ _ __ ___
 * |  _ \| | | | |/ _` | | '_ \    | |/ _ \/ _` | '_ ` _ \
 * | |_) | | |_| | (_| | | | | |   | |  __/ (_| | | | | | |
 * |____/|_|\__,_|\__, |_|_| |_|   |_|\___|\__,_|_| |_| |_|
 *                |___/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  Blugin team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace blugin\api\paymentpool;

use Leader\PointAPI;
use pocketmine\Player;

class PointAPIProvider implements IPaymentProvider{
    /**
     * @return string the unique name of provider for provider key
     */
    public function getName() : string{
        return "pointapi";
    }

    /**
     * @return float[] player name => money
     */
    public function getAll() : array{
        return PointAPI::getInstance()->Pt;
    }

    /**
     * @param Player|string $player
     *
     * @return bool If player's data was exists
     */
    public function exists($player) : bool{
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        return isset(PointAPI::getInstance()->Pt[$player]);
    }

    /**
     * @param Player|string $player
     *
     * @return float|null If player's data was exists return null, else return player's money
     */
    public function get($player) : ?float{
        if(!$this->exists($player))
            return null;
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        return (float) PointAPI::getInstance()->Pt[$player]["포인트"];
    }

    /**
     * @param Player|string $player
     * @param float         $money
     */
    public function set($player, float $money) : void{
        if(!$this->exists($player))
            return;
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        PointAPI::getInstance()->Pt[$player]["포인트"] = (int) $money;
    }

    /**
     * @param Player|string $player
     * @param float         $money
     *
     * @return float|null If player's data was exists return null, else return result money
     */
    public function increase($player, float $money) : ?float{
        if(!$this->exists($player))
            return null;
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        PointAPI::getInstance()->Pt[$player]["포인트"] += (int) $money;
        return $this->get($player);
    }

    /**
     * @param Player|string $player
     * @param float         $money
     *
     * @return float|null If player's data was exists return null, else return result money
     */
    public function decrease($player, float $money) : ?float{
        if(!$this->exists($player))
            return null;
        if($player instanceof Player){
            $player = $player->getName();
        }
        $player = strtolower($player);

        PointAPI::getInstance()->Pt[$player]["포인트"] -= (int) $money;
        return $this->get($player);
    }
}
