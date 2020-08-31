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
 * @name MultiEconomyProvider
 * @api 3.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\MultiEconomyProvider
 */

namespace blugin\api\paymentpool;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use twisted\multieconomy\Currency;
use twisted\multieconomy\MultiEconomy;

class MultiEconomyProvider extends PluginBase{
    private $falied = false;

    public function onLoad(){
        try{
            if(!class_exists(PaymentPool::class))
                throw new \RuntimeException("Could not load provider of 'MultiEconomy': PaymentPool missing");
            if(!class_exists(MultiEconomy::class))
                throw new \RuntimeException("Could not load provider of 'MultiEconomy': Target payment class missing");
        }catch(\RuntimeException $exception){
            $this->getServer()->getLogger()->error($exception->getMessage());
            $this->falied = true;
            return;
        }

        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            public function getCurrency() : Currency{
                return MultiEconomy::getInstance()->getCurrencies()[0];
            }

            public function getName() : string{
                return "multieconomy";
            }

            /** @return float[] player name => money */
            public function getAll() : array{
                return $this->getCurrency()->getAllBalances();
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
                $this->getCurrency()->validateBalance($player);
                return true;
            }

            public function get($player) : ?float{
                if(!$this->exists($player))
                    return null;

                if($player instanceof Player){
                    $player = $player->getName();
                }

                return $this->getCurrency()->getBalance($player);
            }

            public function set($player, float $value) : void{
                if(!$this->exists($player))
                    return;

                if($player instanceof Player){
                    $player = $player->getName();
                }
                $this->getCurrency()->setBalance($player, $value);
            }

            public function increase($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                $this->getCurrency()->addToBalance($player, $value);
                return $this->get($player);
            }

            public function decrease($player, float $value) : ?float{
                if(!$this->exists($player))
                    return null;

                $this->getCurrency()->removeFromBalance($player, $value);
                return $this->get($player);
            }
        }, ["twisted:multieconomy"]);
    }

    public function onEnable(){
        if($this->falied)
            $this->setEnabled(false);
    }
}