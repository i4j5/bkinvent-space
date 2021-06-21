<?php

return [
    
    'target_call_length' => 45, //Длительность целевого звонка в секундах 
    'track_time' => 600, //Длительность сессии в секундах 
    'host' => '*', //Имя домена
    'mask' => '8 ($2) $3-$4-$5', //Маска
    'ga_id' => env('GOOGLE_ANALYTICS_ID'), //Идентификатор Google Analytics
    'metrika_id' => env('YANDEX_METRIKA_ID'), //Идентификатор Яндекс Метрики
    'css_class' => 'dynamic-phone', //css класс
];