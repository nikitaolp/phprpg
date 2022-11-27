<?php

//this is the ugliest code of this entire project. one day i will make it object-oriented.

namespace Phprpg;

use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\State\{Database,GameState};
use Phprpg\Core\Io\{Input,Output};
use Phprpg\Core\World\{WorldBuilder,WorldCommander,Level,LevelManager};
use Phprpg\Core\Entities\Factories\{TileFactory,MobFactory,PlayerFactory,ItemFactory,PushableBlockFactory};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,PlayerStorage,GameEntityStorage,StorageBundle};
use Phprpg\Core\VictoryDefeat\{VictoryDefeat,VictoryDefeatManager};

session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

$time_start = microtime(true);


require '../vendor/autoload.php';
Lo::g('Log start');

AppStorage::set('cfg',require CFG);
AppStorage::set('tiles',require TILES);
AppStorage::set('mobs',require MOBS);
AppStorage::set('players',require PLAYERS);
AppStorage::set('items',require ITEMS);
AppStorage::set('pushables',require PUSHABLES);


AppStorage::set('db', new Database(require 'cred.php'));



$input = new Input(json_decode(file_get_contents("php://input"), true));
$state = new GameState($_POST);
$output = new Output();


if ($state->isGameStarted()){
    
    
    
    if (!($world = $state->getWorld())){
        
        $world = new WorldBuilder(
                    new TileFactory(), 
                    new MobFactory(), 
                    new PlayerFactory(), 
                    new ItemFactory(), 
                    new PushableBlockFactory(), 
                    new StorageBundle(new TileStorage(),[
                        'Mob' => new MobStorage(), 
                        'Item' => new GameEntityStorage(),
                        'PushableBlock' =>new GameEntityStorage(),
                        'Player' => new PlayerStorage()
                    ])
                );

        
                $levels = new LevelManager(require LEVELS,require VICTORY);
                
                
                if ($firstLevel = $levels->getLevel()){
                    $world->setLevel($firstLevel);
                } else {
                    $world->setLevel(new Level(require VICTORY, [], 'Random level', 0));
                }
                


                $world->build();
                
        
        $state->setWorld($world);
        
    }
    
    
    
    $victoryDefeatManager = new VictoryDefeatManager($world->getLevel()->getVictoryDefeatArray(), $world);
    

    
    


    $output->setWorld($world);

    $time_world_built = microtime(true);
    
    $input_action = $input->getAction();




    $worldCommander = new WorldCommander($world,$state->getPlayerId(),$input);
    

    
    $my_turn = $state->checkIfYourTurnV3();
    $output->setTurnMessage($state->getMessage());
    
    if ($my_turn){
        

        if (in_array($input_action,['move','skip','reset']) || $world->getStorageBundle()->getPlayerStorage()->isPlayerDead($state->getPlayerId())){

            $worldCommander->playerTurn();
            
            
            if ($state->checkIfLastTurn()){
                $worldCommander->mobTurn();
            }

            $output->setTurnMessage("");

            Lo::g('Before turn insert');
            $state->completeTurn();
            Lo::g('After turn insert');

        }

        

        
        if (!($world->getVictoryDefeat()) || !($world->getVictoryDefeat()->isConcluded())){
            $victoryDefeatManager->check();
            
            $victoryDefeat = $victoryDefeatManager->getVictoryDefeat();
            
            $world->setVictoryDefeat($victoryDefeat);
            
            if ($victoryDefeat->getVictory()){
                
                $levels = new LevelManager(require LEVELS,require VICTORY);
                
                $current_level = $world->getLevel();
                
                if ($new_level = $levels->getLevel($current_level)){
                    $world->setLevel($new_level);
                    $world->build();

                    $newVictoryDefeatManager = new VictoryDefeatManager($world->getLevel()->getVictoryDefeatArray(), $world);
                    $newVictoryDefeat = $newVictoryDefeatManager->getVictoryDefeat();


                    $world->setVictoryDefeat($newVictoryDefeat);
                    
                    Lo::gG($current_level->getLevelPassedString());

                    if ($newVictoryDefeat->getVictory()){

                        

                    }

                    $worldCommander->findPlayerCoordinates();
                }
                
                
            }
        }

        $time_moved = microtime(true);
        
    }
    
    if ($player_is_new = $state->isPlayerNew()){
        $worldCommander->addPlayer();
    }
    
    $output->setCoordinates($worldCommander->getPlayerCoordinates());
    
    
    if ($player_is_new || $my_turn){
        $state->saveWorld($world);
    }
    
    
    
    $output->setJoinCode($state->getJoinCode());
    
    
    if ($world->getStorageBundle()->getPlayerStorage()->isPlayerDead($state->getPlayerId())){
        $output->setTurnMessage("You are DEAD!!!");
    }
    
    $output->setPlayerSlots($state->getPlayerSlots());


    $output->setVictoryDefeatMessage($world->getVictoryDefeat()->getMessage());
    
    Lo::g("Mobs left ".$world->getStorageBundle()->getMobStorage()->getAllEntityCount());
}




$mem = memory_get_usage()/1024;

$total_time = microtime(true) - $time_start;


Lo::g("{$mem}kb used; total runtime $total_time");

echo $output->printJson();