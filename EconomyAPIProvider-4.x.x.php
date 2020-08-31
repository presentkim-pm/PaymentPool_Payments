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
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author  Blugin-team
 * @link    https://github.com/Blugin
 * @license https://www.gnu.org/licenses/lgpl-3.0 LGPL-3.0 License
 *
 * @name PaymentEconomyAPIProvider
 * @api 4.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\PaymentEconomyAPIProvider
 */

namespace blugin\api\paymentpool;

use onebone\economyapi\EconomyAPI;
use pocketmine\plugin\PluginBase;

class PaymentEconomyAPIProvider extends PluginBase{
    private $falied = false;

    public function onLoad(){
        try{
            if(!class_exists(PaymentPool::class))
                throw new \RuntimeException("Could not load provider of 'EconomyAPI': PaymentPool missing");
            if(!class_exists(EconomyAPI::class))
                throw new \RuntimeException("Could not load provider of 'EconomyAPI': Target payment class missing");
        }catch(\RuntimeException $exception){
            $this->getServer()->getLogger()->error($exception->getMessage());
            $this->falied = true;
            return;
        }

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

    public function onEnable(){
        if($this->falied)
            $this->setEnabled(false);
    }
}