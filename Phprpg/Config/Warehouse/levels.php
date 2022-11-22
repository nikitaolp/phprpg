<?php
/**
 * entityIdArray format:
 * 
 * each array element represents either a tile id (starts with 1), or array of [tile id, entity id]
 * 
 * entities starting with 2 are mobs, with 3 are items, with 4 are players, but i don't think i will use 4
 * 
 * entities starting with 9 will be "special", so far i plan to use 999 as a player spawn point
 * 
 * 
 */


return [
    [
    'entityIdArray' => [
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,124,124,124,123,123,123],
        [123,123,123,124,125,124,123,123,123],
        [123,124,124,124,[123,501],124,124,124,123],
        [123,124,125,[123,501],[123,999],[123,501],125,124,123],
        [123,124,124,124,[123,501],124,124,124,123],
        [123,123,123,124,125,124,123,123,123],
        [123,123,123,124,124,124,123,123,123],
        [123,123,123,123,123,123,123,123,123],
    ],
    'name' => 'Level 1',
    'order'=>1,
    'maxItemCount'=>0,
    'maxMobCount'=>0,
    'victoryDefeatConditions' =>[
        'victory' => [
            'Phprpg\Core\Entities\PushableBlock'=>[
                'coordinatesMultipleByEntityId'=>['501'=>[[4,2],[6,4],[4,6],[2,4]]]
            ]
            ]
            
        ]
    ],
    [
    'entityIdArray' => [
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,123,123,123,123,123,123],
        [124,124,124,124,124,124,124,124,124],
        [123,123,123,123,[123,999],123,123,123,125],
        [124,124,124,124,124,124,124,124,124],
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,123,123,123,123,123,123],
        [123,123,123,123,123,123,123,123,123],
    ],
    'name' => 'Intermission 1',
    'order'=>2,
    'maxItemCount'=>0,
    'maxMobCount'=>0,
    'victoryDefeatConditions' =>[
        'victory' => [
            'Phprpg\Core\Entities\Player'=>[
                'coordinatesSingle'=>[8,4]
            ]
            ]
            
        ]
    ],

];