<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class HotelFactory extends Factory
{
    private static array $hotelPrefixes = [
        'Grand', 'Royal', 'Palace', 'Park', 'Boutique', 'Panoráma', 'Kristály',
        'Arany', 'Ezüst', 'Korona', 'Napsugár', 'Harmónia', 'Thermal', 'Wellness',
        'Aqua', 'Villa', 'Kastély', 'Premium', 'Elegance', 'Prestige', 'Comfort',
        'Centrum', 'Belváros', 'Citadella', 'Duna', 'Balaton', 'Mátra', 'Bükk',
    ];

    private static array $hotelSuffixes = [
        'Hotel', 'Szálló', 'Hotel & Spa', 'Panzió', 'Resort', 'Apartman Hotel',
        'Wellness Hotel', 'Boutique Hotel', 'Hotel & Resort',
    ];

    private static array $cities = [
        'Budapest' => ['1051', '1052', '1053', '1054', '1055', '1061', '1062', '1071', '1082', '1132'],
        'Debrecen' => ['4024', '4025', '4026'],
        'Szeged' => ['6720', '6722', '6723'],
        'Miskolc' => ['3525', '3526', '3527'],
        'Pécs' => ['7621', '7622', '7623'],
        'Győr' => ['9021', '9022', '9023'],
        'Nyíregyháza' => ['4400', '4401'],
        'Kecskemét' => ['6000', '6001'],
        'Székesfehérvár' => ['8000', '8001'],
        'Szombathely' => ['9700', '9701'],
        'Eger' => ['3300', '3301'],
        'Sopron' => ['9400', '9401'],
        'Siófok' => ['8600', '8601'],
        'Balatonfüred' => ['8230', '8231'],
        'Hévíz' => ['8380'],
        'Hajdúszoboszló' => ['4200'],
        'Gyula' => ['5700'],
        'Zalaegerszeg' => ['8900'],
        'Veszprém' => ['8200'],
        'Kaposvár' => ['7400'],
        'Tatabánya' => ['2800'],
        'Szolnok' => ['5000'],
        'Esztergom' => ['2500'],
        'Visegrád' => ['2025'],
        'Szentendre' => ['2000'],
        'Tihany' => ['8237'],
        'Lillafüred' => ['3517'],
        'Tokaj' => ['3910'],
        'Sárospatak' => ['3950'],
    ];

    private static array $streets = [
        'Kossuth Lajos utca', 'Petőfi Sándor utca', 'Fő utca', 'Széchenyi tér',
        'Deák Ferenc utca', 'Rákóczi út', 'Dózsa György út', 'Bartók Béla út',
        'Ady Endre utca', 'Bajcsy-Zsilinszky út', 'Arany János utca', 'Jókai Mór utca',
        'Vörösmarty tér', 'Szent István körút', 'Múzeum körút', 'Andrássy út',
        'Váci utca', 'Baross utca', 'Bem József tér', 'Hunyadi János utca',
        'Mátyás király útja', 'Táncsics Mihály utca', 'Bethlen Gábor utca',
    ];

    private static array $descriptions = [
        'Elegáns szálloda a város szívében, modern szobákkal és kiváló étteremmel.',
        'Családbarát szálláshely csodálatos kilátással és wellness részleggel.',
        'Történelmi épületben kialakított boutique hotel egyedi hangulattal.',
        'A tökéletes választás üzleti és szabadidős utazóknak egyaránt.',
        'Gyönyörű kerttel körülvett szálloda, ahol a nyugalom és pihenés garantált.',
        'Modern design hotel a belváros központjában, minden látnivaló közelében.',
        'Felújított szálló kiváló közlekedési kapcsolatokkal és ingyenes parkolóval.',
        'Romantikus szállás pároknak, gyertyafényes vacsorával és jakuzzival.',
        'Természetközeli szálloda túrázási lehetőségekkel és helyi gasztronómiával.',
        'Prémium wellness hotel termálvízzel, szaunával és masszázsszolgáltatással.',
        'Panorámás szálloda a Duna-parton, hajnali napfelkeltéket kínálva.',
        'Barátságos panzió házi készítésű reggelivel és vendégszerető házigazdákkal.',
        'Luxus apartmanhotel teljes felszereltséggel, hosszabb tartózkodásra is ideális.',
        'Sportolóknak is tökéletes: fitneszterem, úszómedence és kerékpárkölcsönzés.',
        'Állatbarát szállás, ahol a kiskedvencek is szívesen látottak.',
    ];

    public function definition()
    {
        $prefix = fake()->randomElement(self::$hotelPrefixes);
        $suffix = fake()->randomElement(self::$hotelSuffixes);
        $city = fake()->randomElement(array_keys(self::$cities));
        $zips = self::$cities[$city];
        $zip = fake()->randomElement($zips);
        $street = fake()->randomElement(self::$streets);
        $number = fake()->numberBetween(1, 120);

        return [
            'name' => $prefix . ' ' . $suffix,
            'address' => "$zip $city, $street $number.",
            'description' => fake()->randomElement(self::$descriptions),
            'rating' => fake()->randomFloat(1, 1, 5),
        ];
    }
}
