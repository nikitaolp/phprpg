<?php

/**
 * This class should be named differently, like... uhh... Turn Commander. Or no.
 * What is the  "single  responsibility" here? Process turn, 
 * make mobs move, pick items and attack each other
 * spawn more items and mobs if needed
 * lots of stuff should be moved to separate classes, and this one should have less busywork
 * but how...
 *
 * @author Nikita
 */

namespace Phprpg\Core\World;

use Phprpg\Core\World\WorldBuilder;
use Phprpg\Core\Io\Input;
use Phprpg\Core\{Lo,AppStorage};

use Phprpg\Core\Turns\{DirectionTools,DirectionPriority,Coordinates,Attack};

use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,GameEntityStorage,StorageBundle};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};

class WorldCommander {
    //put your code here
    
    private ?Coordinates $current_player_coordinates = null;
    
    public function __construct(private WorldBuilder $world,private int $player_id,private Input $input){
        $this->findPlayerCoordinates();
    }
    
    
    public function getPlayerCoordinates():?Coordinates{
        return $this->current_player_coordinates;
    }
    
    public function findPlayerCoordinates():void{

        $mobs = $this->world->getMobs();
        
        
        //ok so first we try to make turn for a player with given ID

        if (!$this->world->isPlayerDead($this->player_id)){
            
            foreach ($mobs as $y => $line){

                foreach ($line as $x => $mob){
                    //these class checks are bad
                    if ('Phprpg\Core\Entities\Player' != get_class($mob) || $mob->isExpired()){
                        continue;
                    }

                    if ($mob->getId() == $this->player_id){

                        
                        $mobCoords = new Coordinates($x,$y);

                        $this->current_player_coordinates = $mobCoords;
                       

                        break;
                    }
                }

            }
            

            
        }
        

    }
    
    private function checkIfPlayerIsDead():void{
        $player = $this->world->getMobStorage()->getEntity($this->current_player_coordinates);
        
        if (!$player || $player->isExpired()){
            $this->world->addDeadPlayer($player->getId());
        }
    }
    
    public function playerTurn():void{
        if ($mobCoords = $this->current_player_coordinates){
            $mob = $this->world->getMobStorage()->getEntity($mobCoords);

            $mob->removeStatus('attack');
            $direction = $this->input->getDirection();
            if ($direction){
                $newCoordinates = DirectionTools::$direction($mobCoords);

                if ($this->moveCheck($direction,$mob,$mobCoords,$newCoordinates)){

                    $this->current_player_coordinates = $newCoordinates;

                }                        
            }
        }
 
    }
    


    
    public function mobTurn():void{
        
        //new main loop.
        //goals: most of the stuff should happen in this loop
        // as little culculations as possible
        //self descriptive api-like method calls
        
        Lo::g("Mob loop start",'yellow');
        $mobs = $this->world->getMobs();

        
        //and then we should process mob turns, IF the current player turn is the last turn
        foreach ($mobs as $y => $line){
            
            foreach ($line as $x => $mob){

                if ('Phprpg\Core\Entities\Mob' != get_class($mob) || $mob->isExpired()){
                    continue;
                }
                
                $turnActionComplete = false;
                
                $mob->removeStatus('attack');
                
                $mobCoords = new Coordinates($x,$y);
                
                
                $dirprior = new DirectionPriority($mob,$mobCoords,$this->world->getMobStorage(),$this->world->getItemStorage());
                $movePriorities = $dirprior->getDirectionPriorities();
                
                
                foreach ($movePriorities as $direction){

                        
                    $newCoordinates = DirectionTools::$direction($mobCoords);

                    if (!is_null($this->moveCheck($direction,$mob,$mobCoords,$newCoordinates))){
                        $turnActionComplete = true;
                        break;
                    }

                  
                }

                if (!$turnActionComplete){
                    $mob->setDirection(null);
                }
               
                
                $mob->betrayalCheck();
            }
            
        }

        $this->world->topUpEntities();
        
        Lo::g("mob loop end",'yellow');
    }
    
    
    
    private function attackCheck($direction,$mob,$newCoordinates):bool{
        if ($defender = $this->world->getMobStorage()->checkForAttackableMob($mob,$newCoordinates)){
            $mob->setDirection($direction);
            $atck = new Attack($mob,$defender);

            if ($defender->isExpired()){
                
                if ('Phprpg\Core\Entities\Player' == get_class($defender)){
                    $this->world->addDeadPlayer($defender->getId());
                }
                $this->world->getMobStorage()->unsetEntity($newCoordinates);
            }
            return true;
        }
        return false;
    }
    
    
    private function moveCheck($direction,$mob,$mobCoords,$newCoordinates):?bool{
        
        //Lo::gG($mob->getNickname()." tries to move from {$mobCoords} to {$newCoordinates}");
        
        $mob->setDirection($direction);
        
        return $this->world->getStorageBundle()->collisionActionCheck($mob, $mobCoords, $newCoordinates);
    }
 
    public function addPlayer():void{
        if (empty($this->current_player_coordinates)){
            $this->world->addPlayer($this->player_id);
            $this->findPlayerCoordinates();
        }
    }
}
