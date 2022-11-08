<?php

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
// notices and warnings
$time_start = microtime(true);


require '../vendor/autoload.php';
Lo::g('Log start');

AppStorage::set('cfg',require 'Config/cfg.php');
AppStorage::set('tiles',require 'Config/tiles.php');
AppStorage::set('mobs',require 'Config/mobs.php');
AppStorage::set('players',require 'Config/players.php');
AppStorage::set('items',require 'Config/items.php');
AppStorage::set('pushables',require 'Config/pushableblocks.php');


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


        //$world->build();
        
        //testing custom levels, unstable hack {
        
                /**
                 * 
                 * so, the problem: level shouldn't contain VictoryDefeatManager, it contains $world reference, and if i want to store
                 * level inside world... we end up with property object having reference to containing object, this is weird... well this is gross, shouldn't be this way
                 * 
                 * thus, level should only have victory defeat array
                 * 
                 * and how should the whole thing work then? 
                 * 
                 * 1. we check if world exists in state
                 * 
                 * if no, we check if there are any levels in level config
                 * 
                 * if yes, we get level, store it inside world, and build it. 
                 * 
                 * next turn, there is a level in world. then we check world->victorydefeat. if victory, then
                 * 
                 * levelmanager->nextLevel($world->get level)
                 * if this is null... well, then we delete the level inside world, and build a random one. 
                 * 
                 * then victory defeat stuff...
                 * 
                 * 1. check if there is a level with vd array inside world
                 * 2. if yes, create vd manager with this array. if no, use the default array
                 * 3. then... everything is same? 
                 * 
                 */
        
                $levels = new LevelManager(require 'Config/levels.php',require 'Config/victory.php');
                
                
                if ($firstLevel = $levels->getLevel()){
                    $world->setLevel($firstLevel);
                } else {
                    $world->setLevel(new Level(require 'Config/victory.php', [], 'Random level', 0));
                }
                

                
                

                $world->build();
                
        //} testing custom levels
        
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
        

        if (in_array($input_action,['move','skip']) || $world->getStorageBundle()->getPlayerStorage()->isPlayerDead($state->getPlayerId())){

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
                
                $levels = new LevelManager(require 'Config/levels.php',require 'Config/victory.php');
                
                $world->setLevel($levels->getLevel($world->getLevel()));
                $world->build();
                
                $newVictoryDefeatManager = new VictoryDefeatManager($world->getLevel()->getVictoryDefeatArray(), $world);
                $newVictoryDefeat = $newVictoryDefeatManager->getVictoryDefeat();
                
                
                $world->setVictoryDefeat($newVictoryDefeat);
                
                if ($newVictoryDefeat->getVictory()){
                
                Lo::gG($world->getLevel()->getVictoryDefeatArray());
                
                }
                
                $worldCommander->findPlayerCoordinates();
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