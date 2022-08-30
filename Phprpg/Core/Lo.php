<?php

namespace Phprpg\Core;

class Lo {
    //put your code here
    static protected array $log = [];
    static protected array $game_log = [];
    static protected array $tech_log = [];
    
    
//    public static function g(Mixed $data = '',string $color=''):void{
//        if (is_array($data) || is_object($data)){
//            $store['txt'] = "<pre style='color:$color;'>".print_r($data, true)."</pre>"; 
//        } else {
//            $store['txt'] = "<span style='color:$color;'>$data</span>";
//        }
//        $store['time'] = microtime(true);
//        self::$log[] = $store;
//    }
    
    public static function print():void{
        echo $this->getString();
    }
    
    
    
    private static function addLogData(Mixed $data = '',string $color='',string $array_name = 'tech_log'){
        
        $color_string = '';
        if ($color){
            $color_string = "style='color:". htmlentities($color)."'";
        }
        
        if (is_array($data) || is_object($data)){
            $store['txt'] = "<pre {$color_string}>".print_r($data, true)."</pre>"; 
        } else {
            $store['txt'] = "<span {$color_string}>$data</span>";
        }
        $store['time'] = microtime(true);
        self::$$array_name[] = $store;
    }
    
    public static function gG(Mixed $data = '',string $color=''){
        self::addLogData($data,$color,'game_log');
    }
    
    public static function g(Mixed $data = '',string $color=''){
        self::addLogData($data,$color,'tech_log');
    }
    
    public static function getString(string $array_name = 'tech_log',$debug = false):string{
        $output = self::$$array_name;
        $output_str = '';
        if (!empty($output[0]['time'])){
            //array_walk($output, fn(&$x) => $x = "<p>{$x['txt']} (".$x['time']-$output[0]['time'].'s)</p>');
            //echo implode(PHP_EOL, $output); // 'a','b','c'
            
            
            
            foreach($output as $k=>$entry){
                
                $time_since_beginning = $entry['time']-$output[0]['time'];
                
                $time_since_beginning = number_format($time_since_beginning,6);
                
                $time_since_previous_entry = '0.000000';
                
                if (!empty($output[$k-1]['time'])){
                    $time_since_previous_entry = $entry['time'] - $output[$k-1]['time'];
                    $time_since_previous_entry = number_format($time_since_previous_entry,6);
                }
                
                $debug_string = '';
                
                if ($debug){
                    $debug_string = " (since start:{$time_since_beginning}s; prev_log:{$time_since_previous_entry}s)";
                }
                
                $output_str .= "<p>{$entry['txt']}{$debug_string}</p>";
            }
        }
        return $output_str;
        
    }
}
