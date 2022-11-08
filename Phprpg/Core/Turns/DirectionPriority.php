<?php

/**
 * the point is to get mob decision priorities from config, get possible directions, order them
 * according to config priorities, and return an array of directions
 * however, problem: do i really want directions to be Direction objects? in that case, array would be 
 * less "readable" for methods in World Commander... 
 *
 * @author Nikita
 */

namespace Phprpg\Core\Turns;
use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Turns\{DirectionTools,Coordinates};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,PlayerStorage,GameEntityStorage};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};

class DirectionPriority {
    //put your code here
    
    public  function __construct(private Mob $mob, private Coordinates $mobCoords, private GameEntityStorage $mobStorage, private GameEntityStorage $itemStorage,private GameEntityStorage $playerStorage){
        
    }
    
    public function getDirectionPriorities():array{
        $return = [];
        if (empty($return = $this->nearbyPriorities())){
            $return = $this->farawayPriorities();
        }
        
        return $return;
    }
    
    private function farawayPriorities():array{
        
        
        $orderedByConfig = array_filter(
            array_replace(
                    AppStorage::get('cfg','mob_priorities'), 
                    $this->enemies(AppStorage::get('cfg','mob_sight'),false)
                    +
                    $this->items(AppStorage::get('cfg','mob_sight'),false))
        );
        
        $flat = [];        
        foreach ($orderedByConfig as $action_type=>$coord_arr){
            foreach ($coord_arr as $dir){
                $flat[$dir] = $dir;
            }
        }
        
        //huum. i need to check whether any real priorities were returned, and if not, existing direction must be on top
        
        
        if (($existingDirection = $this->mob->getDirection()) && empty($flat[$existingDirection])){
            
            
            if (rand(1,20) == 1){
                $existingDirection = DirectionTools::getRandom();
            }
            
            $flat[$existingDirection] = $existingDirection;
        }
        
        //Lo::g($flat);
        $rnd = DirectionTools::getRandomDirectionsArray();
        foreach ($rnd as $dir){
            if (empty($flat[$dir])){
                $flat[$dir] = $dir;
            } 
        }
        
       //Lo::g($this->mob->getNickname().' faraway');
       //Lo::g($flat);

        return $flat;
    }
    
    
    private function nearbyPriorities():array{
        
        $flat = []; 
        
        $nearby_enemies = $this->enemies(1,true);
        
        $nearby_items = $this->items(1,true);
        

        
        if (!empty($nearby_enemies['attack']) || !empty($nearby_enemies['escape']) || 
            !empty($nearby_items['pickup_desireable']) || !empty($nearby_items['avoid_undesireable'])){
            
            $orderedByConfig = array_filter(
                array_replace(
                    AppStorage::get('cfg','mob_priorities'), $nearby_enemies + $nearby_items
                )
            );

            foreach ($orderedByConfig as $action_type=>$coord_arr){
                foreach ($coord_arr as $dir){
                    $flat[$dir] = $dir;
                }
            }
            
            if (($existingDirection = $this->mob->getDirection()) && empty($flat[$existingDirection])){


                if (rand(1,20) == 1){
                    $existingDirection = DirectionTools::getRandom();
                }

                $flat[$existingDirection] = $existingDirection;
            }


            $rnd = DirectionTools::getRandomDirectionsArray();
            foreach ($rnd as $dir){
                if (empty($flat[$dir])){
                    $flat[$dir] = $dir;
                } 
            }
            
            
        }
        
        
        
        return $flat;
    }
    
    
    
    private function enemies(int $sight, bool $includeOpposite):array{
        
        $enemies['attack'] = [];
        $enemies['escape'] = [];
        
        foreach ([$this->mobStorage,$this->playerStorage] as $storage){
            
            if ($sight == 1){
                $mobs = $storage->getDirectlyAccessibleEntities($this->mobCoords);
            } else {
                $mobs = $storage->getSurroundingEntities($this->mobCoords,$sight);
            }

            $attackEscapeArrays = $this->checkStoragesForEnemies($sight,$includeOpposite,$mobs);

            $enemies['attack'] = array_merge($enemies['attack'],$attackEscapeArrays['attack']);
            $enemies['escape'] = array_merge($enemies['escape'],$attackEscapeArrays['escape']);
        }
        
        

        return $enemies;
    }
    
    private function checkStoragesForEnemies(int $sight, bool $includeOpposite,array $mobs):array{
        
        $enemies['attack'] = [];
        $enemies['escape'] = [];
        
        foreach ($mobs as $y => $line){
            
            foreach ($line as $x => $anotherMob){
                
                if ($this->mob->getTeam() != $anotherMob->getTeam()){
                    
                    $attack_direction = DirectionTools::checkDirection($this->mobCoords,new Coordinates($x,$y),$sight);
                    
                    $enemies['attack'] += $attack_direction;
                    //mad mobs don't care about their  health
                    //others run when their hp is below 30% and lower then the opponents
                    if ($includeOpposite && (!$this->mob->checkStatus('traitor') && $this->mob->gethealthPercentage()<20 && ($this->mob->getHealth() < $anotherMob->getHealth()))){
                        
                        $escape_direction = DirectionTools::getOpposite($attack_direction);
                        $enemies['escape'] += $escape_direction;
                    }
                    
                }
            }
            
        }

        return $enemies;
    }
    
    private function items(int $sight, bool $includeOpposite):array{
        if ($sight == 1){
            $sur_items = $this->itemStorage->getDirectlyAccessibleEntities($this->mobCoords);
        } else {
            $sur_items = $this->itemStorage->getSurroundingEntities($this->mobCoords,$sight);
        }
        
        $items['pickup_desireable'] = [];
        $items['avoid_undesireable'] = [];
        
        foreach ($sur_items as $y => $line){
            
            foreach ($line as $x => $itm){
                
                $desireable = $itm->isDesireable();
                
                
                
                if (isset($desireable) && $desireable){
                    $items['pickup_desireable'] += DirectionTools::checkDirection($this->mobCoords,new Coordinates($x,$y),$sight);

                } else if ($includeOpposite && (isset($desireable) && !$desireable)){
                    
                    
                    
                    $direction = DirectionTools::checkDirection($this->mobCoords,new Coordinates($x,$y),$sight);
                    
                    //hum, with enemies, you want to go to an opposite direction, to cover as much distance as possible to get away
                    //however, items don't chase mobs, so you don't need an opposite direction, just random direction that differs from bad item direction
                    //maybe this will help with the whole "go back and forth" situation that happens when there are multiple bad items
                    //$opposite = DirectionTools::getOpposite($direction);
                    $opposite = DirectionTools::getRandomDirectionsArray($direction);
                    
                    $items['avoid_undesireable'] += $opposite;
                    
                }
                
            }
            
            
        }
        
        
        return $items;
    }
    

}
