<?php

return [
    // Разделяем видимость маршрутов для безопасности
    'groups' => [
        'admin' => ['admin.*', 'admin.login', 'logout'], // Роуты для админки + авторизация
        'account' => ['account.*', 'account.login', 'logout'], // Роуты для личного кабинета
    ],
];
