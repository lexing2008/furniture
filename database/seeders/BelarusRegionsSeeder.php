<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Areas of Belarus with their cities, inside the existing country with the subdomain "belarus".
 * Existing regions (found by subdomain) are left untouched, so it is safe to run repeatedly.
 *
 * City: [name, name_en, name_in]. name_in is the locative form without a preposition ("Бресте").
 */
class BelarusRegionsSeeder extends Seeder
{
    private const AREAS = [
        [
            'name' => 'Минская область', 'name_en' => 'Minsk Region', 'name_in' => 'Минской области',
            'subdomain' => 'minskaya-oblast',
            'cities' => [
                ['Минск', 'Minsk', 'Минске'],
                ['Борисов', 'Borisov', 'Борисове'],
                ['Березино', 'Berezino', 'Березино'],
                ['Вилейка', 'Vileyka', 'Вилейке'],
                ['Воложин', 'Volozhin', 'Воложине'],
                ['Дзержинск', 'Dzerzhinsk', 'Дзержинске'],
                ['Жодино', 'Zhodino', 'Жодино'],
                ['Заславль', 'Zaslavl', 'Заславле'],
                ['Клецк', 'Kletsk', 'Клецке'],
                ['Копыль', 'Kopyl', 'Копыле'],
                ['Крупки', 'Krupki', 'Крупках'],
                ['Логойск', 'Logoysk', 'Логойске'],
                ['Любань', 'Lyuban', 'Любани'],
                ['Молодечно', 'Molodechno', 'Молодечно'],
                ['Мядель', 'Myadel', 'Мяделе'],
                ['Марьина Горка', 'Marina Gorka', 'Марьиной Горке'],
                ['Несвиж', 'Nesvizh', 'Несвиже'],
                ['Слуцк', 'Slutsk', 'Слуцке'],
                ['Смолевичи', 'Smolevichi', 'Смолевичах'],
                ['Солигорск', 'Soligorsk', 'Солигорске'],
                ['Старые Дороги', 'Staryye Dorogi', 'Старых Дорогах'],
                ['Столбцы', 'Stolbtsy', 'Столбцах'],
                ['Узда', 'Uzda', 'Узде'],
                ['Фаниполь', 'Fanipol', 'Фаниполе'],
                ['Червень', 'Chervyen', 'Червене'],
            ],
        ],
        [
            'name' => 'Брестская область', 'name_en' => 'Brest Region', 'name_in' => 'Брестской области',
            'subdomain' => 'brestskaya-oblast',
            'cities' => [
                ['Брест', 'Brest', 'Бресте'],
                ['Барановичи', 'Baranovichi', 'Барановичах'],
                ['Белоозёрск', 'Beloozyorsk', 'Белоозёрске'],
                ['Берёза', 'Bereza', 'Берёзе'],
                ['Высокое', 'Vysokoye', 'Высоком'],
                ['Ганцевичи', 'Gantsevichi', 'Ганцевичах'],
                ['Давид-Городок', 'David-Gorodok', 'Давид-Городке'],
                ['Дрогичин', 'Drogichin', 'Дрогичине'],
                ['Жабинка', 'Zhabinka', 'Жабинке'],
                ['Иваново', 'Ivanovo', 'Иваново'],
                ['Ивацевичи', 'Ivatsevichi', 'Ивацевичах'],
                ['Каменец', 'Kamenets', 'Каменце'],
                ['Кобрин', 'Kobrin', 'Кобрине'],
                ['Коссово', 'Kossovo', 'Коссове'],
                ['Лунинец', 'Luninets', 'Лунинце'],
                ['Ляховичи', 'Lyakhovichi', 'Ляховичах'],
                ['Малорита', 'Malorita', 'Малорите'],
                ['Микашевичи', 'Mikashevichi', 'Микашевичах'],
                ['Пинск', 'Pinsk', 'Пинске'],
                ['Пружаны', 'Pruzhany', 'Пружанах'],
                ['Столин', 'Stolin', 'Столине'],
            ],
        ],
        [
            'name' => 'Витебская область', 'name_en' => 'Vitebsk Region', 'name_in' => 'Витебской области',
            'subdomain' => 'vitebskaya-oblast',
            'cities' => [
                ['Витебск', 'Vitebsk', 'Витебске'],
                ['Браслав', 'Braslav', 'Браславе'],
                ['Барань', 'Baran', 'Барани'],
                ['Бешенковичи', 'Beshenkovichi', 'Бешенковичах'],
                ['Верхнедвинск', 'Verkhnedvinsk', 'Верхнедвинске'],
                ['Глубокое', 'Glubokoye', 'Глубоком'],
                ['Городок', 'Gorodok', 'Городке'],
                ['Дисна', 'Disna', 'Дисне'],
                ['Докшицы', 'Dokshitsy', 'Докшицах'],
                ['Дубровно', 'Dubrovno', 'Дубровно'],
                ['Лепель', 'Lepel', 'Лепеле'],
                ['Лиозно', 'Liozno', 'Лиозно'],
                ['Миоры', 'Miory', 'Миорах'],
                ['Новолукомль', 'Novolukoml', 'Новолукомле'],
                ['Новополоцк', 'Novopolotsk', 'Новополоцке'],
                ['Орша', 'Orsha', 'Орше'],
                ['Полоцк', 'Polotsk', 'Полоцке'],
                ['Поставы', 'Postavy', 'Поставах'],
                ['Россоны', 'Rossony', 'Россонах'],
                ['Сенно', 'Senno', 'Сенно'],
                ['Толочин', 'Tolochin', 'Толочине'],
                ['Ушачи', 'Ushachi', 'Ушачах'],
                ['Чашники', 'Chashniki', 'Чашниках'],
                ['Шарковщина', 'Sharkovshchina', 'Шарковщине'],
                ['Шумилино', 'Shumilino', 'Шумилино'],
            ],
        ],
        [
            'name' => 'Гомельская область', 'name_en' => 'Gomel Region', 'name_in' => 'Гомельской области',
            'subdomain' => 'gomelskaya-oblast',
            'cities' => [
                ['Гомель', 'Gomel', 'Гомеле'],
                ['Брагин', 'Bragin', 'Брагине'],
                ['Буда-Кошелево', 'Buda-Koshelevo', 'Буда-Кошелеве'],
                ['Василевичи', 'Vasilevichi', 'Василевичах'],
                ['Ветка', 'Vetka', 'Ветке'],
                ['Добруш', 'Dobrush', 'Добруше'],
                ['Ельск', 'Yelsk', 'Ельске'],
                ['Житковичи', 'Zhitkovichi', 'Житковичах'],
                ['Жлобин', 'Zhlobin', 'Жлобине'],
                ['Калинковичи', 'Kalinkovichi', 'Калинковичах'],
                ['Корма', 'Korma', 'Корме'],
                ['Лельчицы', 'Lelchitsy', 'Лельчицах'],
                ['Лоев', 'Loyev', 'Лоеве'],
                ['Мозырь', 'Mozyr', 'Мозыре'],
                ['Наровля', 'Narovlya', 'Наровле'],
                ['Октябрьский', 'Oktyabrsky', 'Октябрьском'],
                ['Петриков', 'Petrikov', 'Петрикове'],
                ['Речица', 'Rechitsa', 'Речице'],
                ['Рогачев', 'Rogachev', 'Рогачеве'],
                ['Светлогорск', 'Svetlogorsk', 'Светлогорске'],
                ['Туров', 'Turov', 'Турове'],
                ['Хойники', 'Khoyniki', 'Хойниках'],
                ['Чечерск', 'Chechersk', 'Чечерске'],
            ],
        ],
        [
            'name' => 'Гродненская область', 'name_en' => 'Grodno Region', 'name_in' => 'Гродненской области',
            'subdomain' => 'grodnenskaya-oblast',
            'cities' => [
                ['Гродно', 'Grodno', 'Гродно'],
                ['Березовка', 'Berezovka', 'Березовке'],
                ['Большая Берестовица', 'Bolshaya Berestovitsa', 'Большой Берестовице'],
                ['Волковыск', 'Volkovysk', 'Волковыске'],
                ['Вороново', 'Voronovo', 'Вороново'],
                ['Дятлово', 'Dyatlovo', 'Дятлово'],
                ['Зельва', 'Zelva', 'Зельве'],
                ['Ивье', 'Ivye', 'Ивье'],
                ['Кореличи', 'Korelichi', 'Кореличах'],
                ['Лида', 'Lida', 'Лиде'],
                ['Мосты', 'Mosty', 'Мостах'],
                ['Новогрудок', 'Novogrudok', 'Новогрудке'],
                ['Ошмяны', 'Oshmyany', 'Ошмянах'],
                ['Островец', 'Ostrovets', 'Островце'],
                ['Свислочь', 'Svisloch', 'Свислочи'],
                ['Скидель', 'Skidel', 'Скиделе'],
                ['Слоним', 'Slonim', 'Слониме'],
                ['Сморгонь', 'Smorgon', 'Сморгони'],
                ['Щучин', 'Shchuchin', 'Щучине'],
            ],
        ],
        [
            'name' => 'Могилевская область', 'name_en' => 'Mogilev Region', 'name_in' => 'Могилевской области',
            'subdomain' => 'mogilevskaya-oblast',
            'cities' => [
                ['Могилев', 'Mogilev', 'Могилеве'],
                ['Белыничи', 'Belynichi', 'Белыничах'],
                ['Бобруйск', 'Bobruysk', 'Бобруйске'],
                ['Быхов', 'Bykhov', 'Быхове'],
                ['Горки', 'Gorki', 'Горках'],
                ['Глуск', 'Glusk', 'Глуске'],
                ['Дрибин', 'Dribin', 'Дрибине'],
                ['Кировск', 'Kirovsk', 'Кировске'],
                ['Климовичи', 'Klimovichi', 'Климовичах'],
                ['Кличев', 'Klichev', 'Кличеве'],
                ['Костюковичи', 'Kostyukovichi', 'Костюковичах'],
                ['Краснополье', 'Krasnopolye', 'Краснополье'],
                ['Кричев', 'Krichev', 'Кричеве'],
                ['Круглое', 'Krugloye', 'Круглом'],
                ['Мстиславль', 'Mstislavl', 'Мстиславле'],
                ['Осиповичи', 'Osipovichi', 'Осиповичах'],
                ['Славгород', 'Slavgorod', 'Славгороде'],
                ['Хотимск', 'Khotimsk', 'Хотимске'],
                ['Чаусы', 'Chausy', 'Чаусах'],
                ['Чериков', 'Cherikov', 'Черикове'],
                ['Шклов', 'Shklov', 'Шклове'],
            ],
        ],
    ];

    public function run(): void
    {
        $country = Region::query()->where('subdomain', 'belarus')->firstOrFail();

        $this->assertUniqueSubdomains();

        DB::transaction(function () use ($country) {
            foreach (self::AREAS as $data) {
                $area = Region::firstOrCreate(
                    ['subdomain' => $data['subdomain']],
                    [
                        'parent_id' => $country->id,
                        'name' => $data['name'],
                        'name_en' => $data['name_en'],
                        'name_in' => $data['name_in'],
                    ],
                );

                foreach ($data['cities'] as [$name, $nameEn, $nameIn]) {
                    Region::firstOrCreate(
                        ['subdomain' => Str::slug($nameEn)],
                        [
                            'parent_id' => $area->id,
                            'name' => $name,
                            'name_en' => $nameEn,
                            'name_in' => $nameIn,
                        ],
                    );
                }
            }
        });
    }

    /**
     * firstOrCreate by subdomain would silently skip a city whose subdomain is already taken by another one.
     */
    private function assertUniqueSubdomains(): void
    {
        $subdomains = [];

        foreach (self::AREAS as $area) {
            $subdomains[] = $area['subdomain'];

            foreach ($area['cities'] as [, $nameEn]) {
                $subdomains[] = Str::slug($nameEn);
            }
        }

        $duplicates = array_keys(array_filter(array_count_values($subdomains), fn (int $count) => $count > 1));

        if ($duplicates) {
            throw new \LogicException('Duplicate subdomains: '.implode(', ', $duplicates));
        }
    }
}
