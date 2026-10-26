<?php

return [
    'accepted' => 'Поле «:attribute» должно быть принято.',
    'array' => 'Поле «:attribute» должно быть массивом.',
    'boolean' => 'Поле «:attribute» должно быть true или false.',
    'between' => [
        'numeric' => 'Поле «:attribute» должно быть между :min и :max.',
        'string' => 'Поле «:attribute» должно содержать от :min до :max символов.',
        'array' => 'Поле «:attribute» должно содержать от :min до :max элементов.',
    ],
    'confirmed' => 'Пароли не совпадают.',
    'date' => 'Поле «:attribute» должно быть корректной датой.',
    'date_format' => 'Поле «:attribute» должно соответствовать формату :format.',
    'distinct' => 'Значение поля «:attribute» повторяется.',
    'email' => 'Укажите корректный email.',
    'exists' => 'Выбранное значение поля «:attribute» некорректно.',
    'in' => 'Выбранное значение поля «:attribute» некорректно.',
    'integer' => 'Поле «:attribute» должно быть целым числом.',
    'max' => [
        'numeric' => 'Поле «:attribute» не должно быть больше :max.',
        'string' => 'Поле «:attribute» не должно быть длиннее :max символов.',
        'array' => 'Поле «:attribute» не должно содержать больше :max элементов.',
    ],
    'min' => [
        'numeric' => 'Поле «:attribute» должно быть не меньше :min.',
        'string' => 'Поле «:attribute» должно содержать не меньше :min символов.',
        'array' => 'Поле «:attribute» должно содержать не меньше :min элементов.',
    ],
    'numeric' => 'Поле «:attribute» должно быть числом.',
    'regex' => 'Поле «:attribute» имеет некорректный формат.',
    'required' => 'Заполните поле «:attribute».',
    'required_if' => 'Заполните поле «:attribute».',
    'size' => [
        'numeric' => 'Поле «:attribute» должно быть равно :size.',
        'string' => 'Поле «:attribute» должно содержать :size символов.',
        'array' => 'Поле «:attribute» должно содержать :size элементов.',
    ],
    'string' => 'Поле «:attribute» должно быть текстом.',
    'timezone' => 'Выберите корректный часовой пояс.',
    'unique' => 'Значение поля «:attribute» уже занято.',
    'url' => 'Укажите корректный адрес, например https://example.com.',
    'current_password' => 'Текущий пароль указан неверно.',
    'attributes' => [
        'email' => 'email',
        'password' => 'пароль',
        'token' => 'токен',
    ],
];
