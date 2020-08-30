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
 * @name PaymentCashAPIProvider
 * @api 3.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\PaymentCashAPIProvider
 */

namespace blugin\api\paymentpool;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use z\CashAPI;

class PaymentCashAPIProvider extends PluginBase{
    private $falied = false;

    public function onLoad(){
        try{
            if(!class_exists(PaymentPool::class))
                throw new \RuntimeException("Could not load provider of 'CashAPI': PaymentPool missing");
            if(!class_exists(CashAPI::class))
                throw new \RuntimeException("Could not load provider of 'CashAPI': Target payment class missing");
        }catch(\RuntimeException $exception){
            $this->getServer()->getLogger()->error($exception->getMessage());
            $this->falied = true;
            return;
        }

        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            /** @var \ReflectionProperty */
            private $dbReflention = null;

            public function getRefelction() : \ReflectionProperty{
                if($this->dbReflention === null){
                    $reflectionClass = new \ReflectionClass(CashAPI::getInstance());
                    $this->dbReflention = $reflectionClass->getProperty("db");
                    $this->dbReflention->setAccessible(true);
                }

                return $this->dbReflention;
            }

            public function getName() : string{
                return "cashapi";
            }

            /** @return float[] player name => money */
            public function getAll() : array{
                return $this->dbReflention->getValue(CashAPI::getInstance());
            }

            public function exists($player) : bool{
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                return isset($this->getAll()[$player]);
            }

            public function create($player, float $value) : bool{
                if($this->exists($player))
                    return false;

                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                $db = $this->getAll();
                $db[$player] = (int) $value;
                $this->dbReflention->setValue(CashAPI::getInstance(), $db);
                return true;
            }

            public function get($player) : ?float{
                if(!$this->exists($player))
                    return null;

                return (float) CashAPI::getInstance()->getCash($player);
            }

            public function set($player, float $value) : void{
                if(!$this->exists($player))
                    return;

                if($player instanceof Player){
                    $player = $player->getName();
                }
                $player = strtolower($player);

                $db = $this->getAll();
                $db[$player] = (int) $value;
                $this->dbReflention->setValue(CashAPI::getInstance(), $db);
            }

            public function increase($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                CashAPI::getInstance()->addCash($player, (int) $value);
                return $this->get($player);
            }

            public function decrease($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                CashAPI::getInstance()->reduceCash($player, (int) $value);
                return $this->get($player);
            }
        }, ["zaoui267:cashapi", "zcash"]);
    }

    public function onEnable(){
        if($this->falied)
            $this->setEnabled(false);
    }
}