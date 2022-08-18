<?php

/**
 * I need to access World from the class that checks for victory/defeat.
 * I also need to have the class that stores victory/defeat state as a property of World, as this is how i currently store data in DB
 * Thus, if i use single class for vd, i have World and VD having each other as properties, this feels wrong
 * so i'll have 2 classes, one checks the conditions and stores result inside another, which is stored in World
 * 
 *
 * @author Nikita
 */

namespace Phprpg\Core\VictoryDefeat;
use Phprpg\Core\World\WorldBuilder;
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,GameEntityStorage};
use Phprpg\Core\{Lo,AppStorage};

class VictoryDefeatManager {
    //put your code here
        //put your code here
    
    private ?VictoryDefeat $vd;
    

 
    private ?array $victoryConditions;
    private ?array $defeatConditions;
    
    
    public function __construct(array $cfg, private WorldBuilder $world){
        

        
        
        if ($vd = $world->getVictoryDefeat()){
            $this->vd = $vd;
        } else {
            $this->vd = new VictoryDefeat();
        }
        
        if (!empty($cfg['victory'])){
            $this->victoryConditions = $cfg['victory'];
        }
        
        if (!empty($cfg['defeat']) && !$this->vd->getVictory()){
            $this->defeatConditions = $cfg['defeat'];
        }
    }
    
    public function setVictoryDefeat(VictoryDefeat $vd):void{
        $this->vd = $vd;
    }
    
    public function getVictoryDefeat():?VictoryDefeat{
        return $this->vd;
    }
    
    public function check(){

        
        if (!$this->vd->getVictory() && !$this->vd->getDefeat()){
            
            if (!empty($this->victoryConditions)){
                $this->vd->setVictory($this->checkConditions($this->victoryConditions));
            }

            if (!empty($this->defeatConditions)){
                $this->vd->setDefeat($this->checkConditions($this->defeatConditions));
            }
        }
        

    }
   
    
    private function checkConditions(array $array):bool{
        
        $any_condition_true = false;
        
        foreach ($array as $target=>$conditions){

            
            switch ($target) {
                case 'player':
                    $any_condition_true = $this->checkStorageForCondition($this->world->getMobStorage(),'Player',$conditions);
                    break;
                case 'mob':
                    $any_condition_true = $this->checkStorageForCondition($this->world->getMobStorage(),'Mob',$conditions);
                    break;
                case 'players':
                    $any_condition_true = $this->checkPlayers($conditions);
                    break;
            }
            
      
            
            if ($any_condition_true) {return true;}
        }
        
        return false;
    }
    
    
    
    private function checkStorageForCondition(GameEntityStorage $storage, string $object_type, array $conditions):bool{
        
        //for now (and likely, forever) you only need one condition to be true to win
        
        $any_condition_true = false;
        

        
        foreach ($storage->getEntities() as $y => $row){
            foreach ($row as $x => $entity){
                
                
                
                if ($object_type == get_class($entity) && !$entity->isExpired()){
                    foreach ($conditions as $cond_type => $cond_val_array){
                        
                        
                        
                        switch ($cond_type) {
                            case 'stats':
                                if ($any_condition_true = $this->checkStats($entity,$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$object_type} stats");
                                }
                                break;
                            case 'inventory':
                                if ($any_condition_true = $this->checkInventory($entity,$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$object_type} inventory contents");
                                }
                                break;
                            case 'coordinates':
                                if ($any_condition_true = $this->checkCoordinates(new Coordinates($x,$y),$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$object_type} coordinates");
                                }
                                break;
                        }
                        
                        
                        if ($any_condition_true) {return true;}
                    }
                }
            }
        }
        return false;
    }
    
    private function checkPlayers(array $conditions){
        $condition_bool = false;
        //i guess i will just make a singel check and be done with this
        if (isset($conditions['all_dead'])){
            if (count($this->world->getDeadPlayers()) >= AppStorage::get('cfg','player_limit')){
                $condition_bool = true;
            }
        }
        return $condition_bool;
    }
    
    private function checkStats(GameEntity $entity, array $stat_array){
        $condition_bool = false;
        
        foreach ($stat_array as $stat=>$val){
            if (method_exists($entity,'get'.ucfirst($stat))){
                if ($val <= $entity->{'get'.ucfirst($stat)}()){
                    $condition_bool = true;
                }
            }
        }
        return $condition_bool;
    }
    
    private function checkInventory(GameEntity $entity, array $inventory_cond){
        $ent_invent = $entity->getInventory();
        $condition_bool = false;
        
        foreach ($inventory_cond as $item => $amount){

            if (in_array($item, array_keys($ent_invent))){
                if ($ent_invent[$item]->getAmount() >= $amount){
                    $condition_bool = true;
                }
            }
            
        }
        
        return $condition_bool;
    }
    
    
    private function checkCoordinates(Coordinates $mobCoord, array $checkCoordArr){

        $condition_bool = false;
        if (isset($checkCoordArr[0]) && isset($checkCoordArr[1])){
            if ($mobCoord->getX() == $checkCoordArr[0] && $mobCoord->getY() == $checkCoordArr[1]){
                $condition_bool = true;
            }
        }
        return $condition_bool;
    }
    
    
   
         
    
}
