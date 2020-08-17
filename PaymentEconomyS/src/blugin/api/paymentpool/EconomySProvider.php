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

use onebone\economyapi\EconomyAPI;
use pocketmine\Player;

class EconomySProvider implements IPaymentProvider{
    /**
     * @return string the unique name of provider for provider key
     */
    public function getName() : string{
        return "economyapi";
    }

    /**
     * @return float[] player name => money
     */
    public function getAll() : array{
        return EconomyAPI::getInstance()->getAllMoney();
    }

    /**
     * @param Player|string $player
     *
     * @return bool If player's data was exists
     */
    public function exists($player) : bool{
        return EconomyAPI::getInstance()->hasAccount($player);
    }

    /**
     * @param Player|string $player
     *
     * @return float|null If player's data was exists return null, else return player's money
     */
    public function get($player) : ?float{
        $result = EconomyAPI::getInstance()->myMoney($player);
        if($result === false){
            $result = null;
        }
        return $result;
    }

    /**
     * @param Player|string $player
     * @param float         $money
     */
    public function set($player, float $money) : void{
        EconomyAPI::getInstance()->setMoney($player, $money);
    }

    /**
     * @param Player|string $player
     * @param float         $money
     *
     * @return float|null If player's data was exists return null, else return result money
     */
    public function increase($player, float $money) : ?float{
        EconomyAPI::getInstance()->addMoney($player, $money);
        return $this->get($player);
    }

    /**
     * @param Player|string $player
     * @param float         $money
     *
     * @return float|null If player's data was exists return null, else return result money
     */
    public function decrease($player, float $money) : ?float{
        EconomyAPI::getInstance()->reduceMoney($player, $money);
        return $this->get($player);
    }
}
