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
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,PlayerStorage,GameEntityStorage};
use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};
use Phprpg\Core\Turns\{Coordinates};

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
    
    public function check():void{

        
        if (!$this->vd->getVictory() && !$this->vd->getDefeat()){
            
            if (!empty($this->victoryConditions)){
                $this->vd->setVictory($this->checkConditions($this->victoryConditions));
            }

            if (!empty($this->defeatConditions)){
                $this->vd->setDefeat($this->checkConditions($this->defeatConditions));
            }
        }
        

    }
   
    /*
     * "players" and "pushableblocks" not having checkStorageForCondition is not cool, but they are just too different
     */
    private function checkConditions(array $array):bool{
        
        $any_condition_true = false;
        
        foreach ($array as $target=>$conditions){

            
            switch ($target) {
                case 'Phprpg\Core\Entities\Player':
                    $any_condition_true = $this->checkStorageForCondition($this->world->getStorageBundle()->getPlayerStorage(),'Phprpg\Core\Entities\Player',$conditions);
                    break;
                case 'Phprpg\Core\Entities\Mob':
                    $any_condition_true = $this->checkStorageForCondition($this->world->getStorageBundle()->getMobStorage(),'Phprpg\Core\Entities\Mob',$conditions);
                    break;
                case 'players':
                    $any_condition_true = $this->checkPlayers($conditions);
                    break;
                case 'Phprpg\Core\Entities\PushableBlock':
                    $any_condition_true = $this->checkIfEntityIdsAtCoordinates($this->world->getStorageBundle()->getPushableBlockStorage(),$conditions);
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
                        
                        $type_exp = explode('\\',$object_type);
                        $type_string = end($type_exp);
                        
                        switch ($cond_type) {
                            case 'stats':
                                if ($any_condition_true = $this->checkStats($entity,$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$type_string} stats");
                                }
                                break;
                            case 'inventory':
                                if ($any_condition_true = $this->checkInventory($entity,$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$type_string} inventory contents");
                                }
                                break;
                            case 'coordinatesSingle':
                                if ($any_condition_true = $this->checkCoordinates(new Coordinates($x,$y),$cond_val_array)){
                                    $this->vd->setMessage("Condition: {$type_string} coordinates");
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
    
    private function checkPlayers(array $conditions):bool{
        $condition_bool = false;
        //i guess i will just make a singel check and be done with this
        if (isset($conditions['all_dead'])){
            if (count($this->world->getStorageBundle()->getPlayerStorage()->getDeadPlayers()) >= AppStorage::get('cfg','player_limit')){
                $condition_bool = true;
            }
        }
        return $condition_bool;
    }
    
    private function checkStats(GameEntity $entity, array $stat_array):bool{
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
    
    private function checkInventory(GameEntity $entity, array $inventory_cond):bool{
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
    
    private function checkIfEntityIdsAtCoordinates(GameEntityStorage $storage, $coordArray):bool{
        
        if (!isset($coordArray['coordinatesMultipleByEntityId'])){
            return false;
        }
        
        
        $allConditionsMet = false;
        
        foreach ($coordArray['coordinatesMultipleByEntityId'] as $id =>$coordinatesArray){
            
            foreach ($coordinatesArray as $k=>$coords){
                if (!isset($coords[0]) || !isset($coords[1])){
                    return false;
                }
                $coords = new Coordinates($coords[0],$coords[1]);
                
                $ent = $storage->getEntity($coords);
                if (!$ent){
                    return false;
                } 
                if ($ent->getEntityId() != $id){
                    return false;
                }
                $allConditionsMet = true;
                
            }
            
        }
        
        return $allConditionsMet;
        
    }
    
    
    private function checkCoordinates(Coordinates $mobCoord, array $checkCoordArr):bool{

        $condition_bool = false;
        if (isset($checkCoordArr[0]) && isset($checkCoordArr[1])){
            if ($mobCoord->getX() == $checkCoordArr[0] && $mobCoord->getY() == $checkCoordArr[1]){
                $condition_bool = true;
            }
        }
        return $condition_bool;
    }
    
    
   
         
    
}
