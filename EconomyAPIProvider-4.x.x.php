<?php

/**
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
 * @author  Blugin-team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 * @name PaymentEconomyAPIProvider
 * @api 4.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\PaymentEconomyAPIProvider
 * @depend PaymentPool
 */

namespace blugin\api\paymentpool;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;

class PaymentEconomyAPIProvider extends PluginBase{
    public function onLoad(){
        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            public function getName() : string{
                return "economyapi";
            }

            public function getAll() : array{
                return EconomyAPI::getInstance()->getAllMoney();
            }

            public function exists($player) : bool{
                return EconomyAPI::getInstance()->hasAccount($player);
            }

            public function create($player, float $value) : bool{
                if($this->exists($player))
                    return false;

                EconomyAPI::getInstance()->createAccount($player, null, $value);
                return true;
            }

            public function get($player) : ?float{
                $result = EconomyAPI::getInstance()->myMoney($player);
                if($result === false){
                    $result = null;
                }
                return $result;
            }

            public function set($player, float $value) : void{
                EconomyAPI::getInstance()->setMoney($player, $value);
            }

            public function increase($player, float $value) : ?float{
                EconomyAPI::getInstance()->addMoney($player, $value);
                return $this->get($player);
            }

            public function decrease($player, float $value) : ?float{
                EconomyAPI::getInstance()->reduceMoney($player, $value);
                return $this->get($player);
            }
        }, ["onebone:economyapi", "economys"]);
    }
}