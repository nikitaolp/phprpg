<?php
namespace Phprpg\Core\Entities;

use Phprpg\Core\{Lo,AppStorage};

class Mob extends GameEntity{
    
    protected ?string $moveDirection = null;
    protected int $level = 1;
    //protected Coordinates $coord;
    protected int $hp = 0;
    
    protected int $xp_earned = 0;
    
    protected string $nickname;
    
    protected array $statusEffects = [];
    
    protected array $inventory = [];
    

    
    public function __construct(
            protected string $name,
            protected string $gfx,
            protected string $char, 
            protected string $desc,
            protected int $chance,
            protected int $maxhp,
            protected int $dmg,
            protected string $team,
            protected int $xp_value,
            protected int $xp_to_level_up) {
        $this->hp = $maxhp;
        //$this->coord = new Coordinates(0,0);
        
    }
    
    public function setNickname(string $nick):void{
        $this->nickname = $nick;
    }
    
    public function getNickname():string{
        return $this->nickname;
    }
    
    public function gethealthPercentage():int{
        return ceil($this->hp/($this->maxhp/100));
    }
    
    
    public function getDirection():?string{
        return $this->moveDirection;
    }
    
    public function setDirection(?string $string):void{
        $this->moveDirection = $string;
    }
    
    public function getTeam():string{
        return $this->team;
    }
    
    public function getHealth():int{
        return $this->hp;
    }
    
    public function getMaxHealth():int{
        return $this->maxhp;
    }
    
    public function healUp():void{
        $this->hp = $this->maxhp;
    }
    
    public function affectHp(int $hp):void{
        $newHp = $this->hp + $hp;
        if ($newHp >= $this->maxhp){
            $this->healUp();
        } else {
            $this->hp = $newHp;
        }
        if ($newHp <= 0){
            $this->hp = 0;
            $this->expire();
        }
    }
    
    private function affectMaxhp(int $hp):void{
        $this->maxhp += $hp;
    }
    
    public function affectXp(int $xp):void{
        $this->xp_earned += $xp;
        
        $xp_to_level_up = $this->xp_to_level_up;
        
        if ($this->level > 1){
            
            for ($i = 2;$i<=$this->level;$i++){
                $xp_to_level_up = round($xp_to_level_up + ($xp_to_level_up/10));
            }
        }
        
        Lo::gG("<span class='logColor1'>".$this->nickname."</span> (level {$this->level}) got {$xp} xp , has {$this->xp_earned} in total, and needs {$xp_to_level_up} to level up");
        
        if ($this->xp_earned >= $xp_to_level_up && ($this->level < 30)){
            $this->levelUp($xp_to_level_up);
        }
    }
   

    
    public function getDmg():int{
        return $this->dmg;
    }
    
    public function getXpValue():int{
        return $this->xp_value;
    }
    
    public function getXpEarned():int{
        return $this->xp_earned;
    }
    
    public function levelUp(int $xp_to_level_up):void{

            $this->xp_earned -= $xp_to_level_up;
            if ($this->xp_earned <0) {
                $this->xp_earned = 0;
            }
            $this->level += 1;
            $this->dmg = $this->dmg + round(($this->dmg / 100) * 10);
            $this->maxhp = $this->maxhp + round(($this->maxhp / 100) * 10);
            $this->xp_value = $this->xp_value + round(($this->xp_value / 100) * 10);
            $this->healUp();

    }
    
    public function getLevel():int{
        return $this->level;
    }
    
    public function getStatusClasses():string{
        $return = '';
        
        if (!empty($this->statusEffects)){
            $return = implode(' ',$this->statusEffects);
        }
        
        return $return;
    }
    
    public function checkStatus(string $status):bool{
        if (!empty($this->statusEffects[$status])){
            return true;
        }
        return false;
    }
    
    public function addStatus(string $status):void{
        $this->statusEffects[$status] = $status;
    }
    
    public function removeStatus(string $status):void{
        unset($this->statusEffects[$status]);
    }
    
    public function betrayalCheck():void{
        //simple way to control population - mobs have a small chance to "go mad" and attack their allies, thus letting repopulation spawn new mobs
        if (rand(1,200) == 1){
            $this->team = $this->nickname;
            Lo::gG("<span class='logColor1'>".$this->nickname.'</span> went mad and betrayed their comrades');
            $this->addStatus('traitor');
        }
        
    }
    
    public function getInventory():array{
        return $this->inventory;
    }
    
    public function getInventoryString():string{
        $str = '';
        foreach ($this->inventory as $item){
            $str = $item.'; ';
        }
        return $str;
    }
    
    public function addToInventory(Item $item):void{
        if (empty($this->inventory[$item->getName()])){
            $this->inventory[$item->getName()] = $item;
        } else {
            $this->inventory[$item->getName()]->add($item->getAmount());
        }
    }
    
    public function receiveInventory(array $inv):void{
        foreach ($inv as $item){
            if (is_a($item,'Item')){
                $this->addToInventory($item);
            }
        }
    }
    
    public function pickupItem(Item $item):void{
        $actionArray = $item->getAction();
        
        Lo::gG('<span class="logColor1">'.$this->nickname.'</span> has interacted with <span class="logColor3">'.$item->getName().'</span> item');
        
        foreach ($actionArray as $prop=>$act){
            if ($prop == 'inventory'){
                $this->addToInventory($item);
                $item->expire();
                continue;
            } else {
                $method = 'affect'.mb_convert_case($prop, MB_CASE_TITLE, "UTF-8");
                if (method_exists($this, $method)){
                    $this->$method($act);
                    $item->expire();
                } else {
                    throw new Exception("No such property, something bad in item config");
                }
                
            }
        }
        
        
    }
    
}