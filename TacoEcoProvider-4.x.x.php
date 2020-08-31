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
 * @name TacoEcoProvider
 * @api 4.0.0
 * @version 1.0.0
 * @main blugin\api\paymentpool\TacoEcoProvider
 */

namespace blugin\api\paymentpool;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Taco\TacoEco\Loader;

class TacoEcoProvider extends PluginBase{
    private $falied = false;

    public function onLoad(){
        try{
            if(!class_exists(PaymentPool::class))
                throw new \RuntimeException("Could not load provider of 'TacoEco': PaymentPool missing");
            if(!class_exists(Loader::class))
                throw new \RuntimeException("Could not load provider of 'TacoEco': Target payment class missing");
        }catch(\RuntimeException $exception){
            $this->getServer()->getLogger()->error($exception->getMessage());
            $this->falied = true;
            return;
        }

        PaymentPool::getInstance()->registerProvider(new class() implements IPaymentProvider{
            public function getConfig() : Config{
                return new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
            }

            public function getName() : string{
                return "tacoeco";
            }

            public function getAll() : array{
                return $this->getConfig()->getAll();
            }

            public function exists($player) : bool{
                if($player instanceof Player){
                    $player = $player->getName();
                }
                $all = $this->getAll();

                return isset($all[$player]);
            }

            public function create($player, float $value) : bool{
                if($player instanceof Player){
                    $player = $player->getName();
                }

                $log = new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
                if(!$log->exists($player)){
                    $log->set($player, $value);
                    $log->save();
                }
                return true;
            }

            public function get($player) : ?float{
                if($player instanceof Player){
                    $player = $player->getName();
                }

                $log = new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
                if($log->exists($player)){
                    return (float) $log->get($player);
                }
                return null;
            }

            public function set($player, float $value) : void{
                if($player instanceof Player){
                    $player = $player->getName();
                }

                $log = new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
                $log->set($player, $value);
                $log->save();
            }

            public function increase($player, float $value) : ?float{
                if($player instanceof Player){
                    $player = $player->getName();
                }

                $log = new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
                $value = ((float) $log->get($player)) + $value;
                $log->set($player, $value);
                $log->save();
                return $value;
            }

            public function decrease($player, float $value) : ?float{
                if($player instanceof Player){
                    $player = $player->getName();
                }

                $log = new Config(Loader::getInstance()->getDataFolder() . "cash.yml", Config::YAML);
                $value = ((float) $log->get($player)) - $value;
                $log->set($player, $value);
                $log->save();
                return $value;
            }
        }, ["taco:tacoeco"]);
    }

    public function onEnable(){
        if($this->falied)
            $this->setEnabled(false);
    }
}