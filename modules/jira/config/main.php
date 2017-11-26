<?php

$module_name = basename(dirname(dirname(__FILE__)));

return [
    'modules' => [
        $module_name => [
            'class' => 'app\modules\\'.$module_name.'\\Module'
       ],
    ],

    'components' => [
        'urlManager' => [
            'rules' => [
                '<module:'.$module_name.'>/<controller:\w+>' => '<module>/<controller>/index',
                '<module:'.$module_name.'>/<controller:\w+>/<id:\d+>' => '<module>/<controller>/view',
                '<module:'.$module_name.'>/<controller:\w+>/<action:\w+>' => '<module>/<controller>/<action>',
                '<module:'.$module_name.'>/<controller:\w+>/<action:\w+>/<id:\w+>' => '<module>/<controller>/<action>',
            ],
        ],
    ],
];

