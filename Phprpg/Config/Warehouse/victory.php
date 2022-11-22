<?php

return [
    'victory' => [
        'Phprpg\Core\Entities\Player'=>[
            'stats'=>['level'=>10],
            'inventory'=>['gold'=>5],
            'coordinatesSingle'=>[1,1]
            
            ]
        ],
    'defeat' => [
        'Phprpg\Core\Entities\Mob' =>[
            'stats'=>['level'=>20],
            'inventory'=>['gold'=>20]
            ],
        'players' => [
            'all_dead'=>true
        ]
    ]
];
