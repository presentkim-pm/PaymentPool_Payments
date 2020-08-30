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
 * @name PaymentCoinProvider
 * @api 3.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\PaymentCoinProvider
 */

namespace blugin\api\paymentpool;

use ojy\coin\Coin;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;

class PaymentCoinProvider extends PluginBase{
    private $falied = false;

    public function onLoad(){
        try{
            if(!class_exists(PaymentPool::class))
                throw new \RuntimeException("Could not load provider of 'Coin': PaymentPool missing");
            if(!class_exists(Coin::class))
                throw new \RuntimeException("Could not load provider of 'Coin': Target payment class missing");
        }catch(\RuntimeException $exception){
            $this->getServer()->getLogger()->error($exception->getMessage());
            $this->falied = true;
            return;
        }

        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            public function getName() : string{
                return "coin";
            }

            public function getAll() : array{
                return Coin::getAll();
            }

            public function exists($player) : bool{
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                return isset(Coin::$coin[$player]);
            }

            public function create($player, float $value) : bool{
                if($this->exists($player))
                    return false;

                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                Coin::$coin[$player] = $value;
                return true;
            }

            public function get($player) : ?float{
                if(!$this->exists($player))
                    return null;

                return Coin::getCoin($player);
            }

            public function set($player, float $value) : void{
                if(!$this->exists($player))
                    return;

                Coin::setCoin($player, $value);
            }

            public function increase($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                Coin::addCoin($player, $value);
                return $this->get($player);
            }

            public function decrease($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                Coin::reduceCoin($player, $value);
                return $this->get($player);
            }
        }, ["ojy:coin"]);
    }

    public function onEnable(){
        if($this->falied)
            $this->setEnabled(false);
    }
}