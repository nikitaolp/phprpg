<?php

namespace Phprpg\Core\World;

use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Entities\Factories\{TileFactory,MobFactory,PlayerFactory,ItemFactory,GameEntityFactory};
use Phprpg\Core\Entities\Storage\{TileStorage,MobStorage,GameEntityStorage};
use Phprpg\Core\Entities\{GameEntity,Tile,Mob,Player};
use Phprpg\Core\VictoryDefeat\{VictoryDefeat};
class WorldBuilder {
    //put your code here
    
    //private array $worldArray = [];
    private int $maxMobCount;
    private int $maxItemCount;
    
    private int $height;
    private int $width;
    private ?VictoryDefeat $victoryDefeat = null;
    
    private array $deadPlayers = [];
    
    public function __construct(
            private TileFactory $tileAssembler, 
            private MobFactory $mobAssembler,
            private PlayerFactory $playerAssembler,
            private ItemFactory $itemAssembler,
            private TileStorage $tileStorage,
            private MobStorage $mobStorage,
            private GameEntityStorage $itemStorage
            ){
        
            $this->height = AppStorage::get('cfg','height');
            $this->width = AppStorage::get('cfg','width');
        
            $this->tileAssembler->fromArray(AppStorage::get('tiles'));
            $this->mobAssembler->fromArray(AppStorage::get('mobs'));
            $this->itemAssembler->fromArray(AppStorage::get('items'));
            $this->playerAssembler->fromArray(AppStorage::get('players'));
            
    }
    
    public function setVictoryDefeat(VictoryDefeat $vd):void{
        $this->victoryDefeat = $vd;
    }
    
    public function getVictoryDefeat(): ?VictoryDefeat{
        return $this->victoryDefeat;
    }
    
    public function getDeadPlayers():array{
        return $this->deadPlayers;
    }
    
    public function isPlayerDead(int $id):bool{

        return in_array($id,$this->deadPlayers);
       
    }
    
    public function addDeadPlayer(int $id){
        
        $this->deadPlayers[] = $id;

    }
    
    public function getWorldTiles():array{
        return $this->tileStorage->getEntities();
    }
    
    public function getMobs():array{
        return $this->mobStorage->getEntities();
    }
    
    public function getMobStorage():GameEntityStorage{
        return $this->mobStorage;
    }
    
    public function getItemStorage():GameEntityStorage{
        return $this->itemStorage;
    }
    
    
    public function getTileStorage():TileStorage{
        return $this->tileStorage;
    }
    
    public function addPlayer(int $player_id){
        
        
        
        $random_location = $this->tileStorage->getRandomTileCoordinates();
        $surroundingTiles = $this->tileStorage->getSurroundingEntities($random_location,5);

        foreach($surroundingTiles as $y => $line){
            

            foreach ($line as $x=>$tile){
                if ($player = $this->tryToFillTile($tile,$this->mobStorage,$this->playerAssembler, $x, $y)){

                    $player->setId($player_id);
                    
                    break;
                }
            }
            if (!empty($player)){
                break;
            }

        }  
    }
    
    //world array should be GameEntityStorage type object, not just array. But how to "build" then? Maybe just build here and then set the entire array in object
    //ok world array is gone, long live tile storage
    public function build():void{
        
            for ($h = 0; $h<= $this->height-1; $h++){
            
                //$this->worldArray[$h] = [];
                    
                for ($w = 0; $w<= $this->width-1; $w++){
                       $randomTile = $this->tileAssembler->tryToGetRandom();
                       if (!$randomTile) {
                           throw new Exception("Random tile is null, default tile with 100 chance likely not set in cfg");
                       } else {
                           
                           $this->tileStorage->storeAtXY($randomTile, $w, $h);
                           
                           //$this->tileStorage->getEntities();
                           //$this->tileStorage->getEntityByXY()
                           
                           //$this->worldArray[$h][$w] = $randomTile;
                           
                           if (!$this->tryToFillTile($this->tileStorage->getEntityByXY($w,$h),$this->mobStorage,$this->mobAssembler, $w, $h)){
                               $this->tryToFillTile($this->tileStorage->getEntityByXY($w,$h),$this->itemStorage,$this->itemAssembler, $w, $h);
                           }
                           

                       }
                       
                }
            
        }
        
     $this->maxMobCount = $this->mobStorage->getAllEntityCount();   
     $this->maxItemCount = $this->itemStorage->getAllEntityCount();  

    }
    
    public function tryToFillTile(Tile $tile, GameEntityStorage $storage, GameEntityFactory $factory, $x, $y):?GameEntity{
        
        
        if ($tile->isWalkable() && !($this->mobStorage->getEntityByXY($x,$y)) && !($this->itemStorage->getEntityByXY($x,$y)) ){
            $mob = $factory->tryToGetRandom();
            if ($mob){
                $storage->storeAtXY($mob,$x,$y);
                return $mob;
                //Lo::g($mob->getNickname()." is at $x $y , i guess ".$tile->getName().' is walkable...');
            }
        }
        return null;
    }
    
    
    public function topUpEntities(){
        $this->tryToSpawnEntities($this->itemStorage,$this->itemAssembler,$this->maxItemCount);
        $this->tryToSpawnEntities($this->mobStorage,$this->mobAssembler,$this->maxMobCount);
    }
    
    
    public function tryToSpawnEntities(GameEntityStorage $storage, GameEntityFactory $factory,int $max_count):void{
        
        $remainingItems = $storage->getAllEntityCount();
        
        if ($remainingItems < round(($max_count/100)*50)){
            
            $random_location = $this->tileStorage->getRandomTileCoordinates();
            
            $surroundingTiles = $this->tileStorage->getSurroundingEntities($random_location,5);

            foreach($surroundingTiles as $y => $line){

                foreach ($line as $x=>$tile){
                    $this->tryToFillTile($tile,$storage,$factory, $x, $y);
                }

            }  
            
        }
        

    }
    
    
    
    //old repopulation method that used map edges, more realistic, but impractical on larger maps as all the action happened on the edges and the center was empty
    public function tryToRepopulate():void{
        
        $remainingMobs = $this->mobStorage->getAllEntityCount();
        
        

        if ($remainingMobs < round(($this->maxMobCount/100)*50)){
            
           
            
            foreach($this->tileStorage->getEntities() as $y => $line){

                    foreach ($line as $x=>$tile){
                        if ($y == 0 || $y == array_key_last($this->tileStorage->getEntities()) || $x == 0 || $x == array_key_last($line)){
                            $this->tryToFillTile($tile,$this->mobStorage,$this->mobAssembler, $x, $y);
                        }
                        
                    }
                
            }         
        }
        

    }
    

}
