<?php
namespace Phprpg\Core\Io;

use Phprpg\Core\World\WorldBuilder;
use Phprpg\Core\{Lo,AppStorage};
use Phprpg\Core\Turns\{Coordinates};


class Output {
    //put your code here
    private string $turn_message = '';
    private ?WorldBuilder $world = null;
    private ?Coordinates $player_coordinates = null;
    private ?string $join_code = null;
    private string $victory_defeat_message = '';
    private ?string $player_slots = null;
    
    
    public function __construct(){
        
    }
    
    public function setPlayerSlots(string $str):void{
        $this->player_slots = $str;
    }
    
    public function setWorld(WorldBuilder $world){
        $this->world = $world;
    }
    
    public function setCoordinates(?Coordinates $coord){
        $this->player_coordinates = $coord;
    }
    
    
    public function setTurnMessage(string $str):void{
        $this->turn_message = $str;
    }
    
    public function setVictoryDefeatMessage(string $str){
        $this->victory_defeat_message = $str;
    }
    
    public function setJoinCode(?string $join_code){
        $this->join_code = $join_code;
    }
    
    private function getPlayerInfo(Coordinates $coord):string{
        
        $str = '';
        
        if ($this->world){
            $player = $this->world->getMobStorage()->getEntity($coord);
            
            if ($player){
                $str = "<p>Name: {$player->getNickname()}</p>
                <p>Level: {$player->getLevel()}</p>
                <p>Health: {$player->getHealth()}/{$player->getMaxHealth()}</p>
                <p>Attack: {$player->getDmg()}</p>
                <p>XP: {$player->getXpEarned()}</p>
                
                
                ";
            }
        }
        
        return $str;
    }
    
    public function printJson(){
        $output['player_info'] = '';
        if($this->player_coordinates){
            if ($radius = AppStorage::get('cfg','print_radius')){
                $output['map'] = $this->getMapByRadius($this->player_coordinates,$radius);
            }
            $output['player_info'] = $this->getPlayerInfo($this->player_coordinates);
        } else {
            $output['map'] = $this->getFullMap();
        }
        
        $output['join_code'] = '';
        
        if ($this->join_code) {
            $output['join_code'] = $this->join_code;
        }
        
        $output['victory_defeat_message'] = $this->victory_defeat_message;
        
        $output['turn_message'] = $this->turn_message;
        $output['player_slots'] = $this->player_slots;
        $output['log'] = Lo::getString();
        
        echo json_encode($output);
        die();
    }
    
    
    private function getMapByRadius(Coordinates $coord,int $radius):string{
        $x_start = $coord->getX()-$radius;
        $x_end = $coord->getX()+$radius;
        
        $y_start = $coord->getY()-$radius;
        $y_end = $coord->getY()+$radius;
        
        $radial_array = [];
        
        for ($y = $y_start;$y<=$y_end;$y++){
            for ($x = $x_start;$x<=$x_end;$x++){
                $radial_array[$y][$x] = true;
            }
        }
        
        return $this->getMapFromArray($radial_array);
    }
    
    private function getFullMap():string{
        $map = '';
        if ($this->world){
           $map =  $this->getMapFromArray($this->world->getTileStorage()->getEntities());
        }
        return $map;
    }
    
    
    private function getMapFromArray( array $tilesArray):string{
        
        $gfxMap = '';
        
        if ($this->world){
            foreach($tilesArray as $y => $line){
                $gfxMap .= '<div class="mapLine">';
                foreach ($line as $x => $v){

                    $tile = $this->world->getTileStorage()->getEntityByXY($x, $y);

                    if ($tile){
                        $gfxMap .= "<div class='tilebg {$tile->getName()} coordX_{$x} coordY_{$y}' >";
                            $mob = $this->world->getMobStorage()->getEntityByXY($x,$y);
                            //$mob = false;
                            if ($mob){


                                $inv = '';
                                if ($invent = $mob->getInventoryString()){
                                    $inv = ", inventory: $invent";
                                }

                                $gfxMap .= "<span class='mobHelper {$mob->getStatusClasses()}' 
                                    data-health='{$mob->getHealth()}' 
                                    data-level='{$mob->getLevel()}'
                                    data-dmg='{$mob->getDmg()}'
                                    style='--health-width:{$mob->getHealthPercentage()}%'><img 
                                    title='{$mob->getNickname()}, lvl. {$mob->getLevel()} $inv' 
                                    class='mobImg direction_{$mob->getDirection()} coordX_{$x} coordY_{$y} {$mob->getStatusClasses()}' 
                                    src='/Phprpg/resources/gfx/{$mob->getGfx()}'></span>";
                            } else if ($item = $this->world->getItemStorage()->getEntityByXY($x,$y)){
                                $gfxMap .= "<span class='itemHelper'><img 
                                    title='{$item->getName()}' 
                                    class='mobImg coordX_{$x} coordY_{$y}' 
                                    src='/Phprpg/resources/gfx/{$item->getGfx()}'></span>";
                            } else {
                                $gfxMap .= "<span class='emptyHelper'></span>";
                            }
                        $gfxMap .= '</div>';
                    } else {
                        $gfxMap .= "<div class='tilebg tileblack coordX_{$x} coordY_{$y}' ><span class='emptyHelper'></span></div>";
                    }


                }
                $gfxMap .= '</div>';

            }
        }
        

        
        
        return $gfxMap;
    }
    
}
