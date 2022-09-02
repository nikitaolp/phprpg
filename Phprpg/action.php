<?php

namespace Phprpg;

use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\State\{Database,GameState};
use Phprpg\Core\Io\{Input,Output};
use Phprpg\Core\World\{WorldBuilder,WorldCommander};
use Phprpg\Core\Entities\Factories\{TileFactory,MobFactory,PlayerFactory,ItemFactory};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,GameEntityStorage};
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
                    new TileStorage(), 
                    new MobStorage(), 
                    new GameEntityStorage() 
                );

        $world->build();
        $state->setWorld($world);
        
    }
    
    $victoryDefeatManager = new VictoryDefeatManager(require 'Config/victory.php', $world);
    

    
    


    $output->setWorld($world);

    $time_world_built = microtime(true);
    
    $input_action = $input->getAction();




    $worldCommander = new WorldCommander($world,$state->getPlayerId(),$input);
    

    
    $my_turn = $state->checkIfYourTurnV3();
    $output->setTurnMessage($state->getMessage());
    
    if ($my_turn){
        

        if (in_array($input_action,['move','skip']) || $world->isPlayerDead($state->getPlayerId())){

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
            $world->setVictoryDefeat($victoryDefeatManager->getVictoryDefeat());
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
    
    
    if ($world->isPlayerDead($state->getPlayerId())){
        $output->setTurnMessage("You are DEAD!!!");
    }
    
    $output->setPlayerSlots($state->getPlayerSlots());


    $output->setVictoryDefeatMessage($world->getVictoryDefeat()->getMessage());
    
    Lo::g("Mobs left ".$world->getMobStorage()->getAllEntityCount());
}




$mem = memory_get_usage()/1024;

$total_time = microtime(true) - $time_start;


Lo::g("{$mem}kb used; total runtime $total_time");

echo $output->printJson();