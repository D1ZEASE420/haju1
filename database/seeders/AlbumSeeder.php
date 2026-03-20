<?php

namespace Database\Seeders;

use App\Models\Album;
use Illuminate\Database\Seeder;

class AlbumSeeder extends Seeder
{
    public function run(): void
    {
        $albums = [
            [
                'title'        => 'Thriller',
                'artist'       => 'Michael Jackson',
                'release_year' => 1982,
                'genre'        => 'Pop',
                'rating'       => 5,
                'description'  => 'Maailma enim müüdud album. Sisaldab hitte nagu "Billie Jean", "Beat It" ja "Thriller". Revolutsiooniline pop-muusika verstapost.',
                'image'        => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&q=80',
            ],
            [
                'title'        => 'Dark Side of the Moon',
                'artist'       => 'Pink Floyd',
                'release_year' => 1973,
                'genre'        => 'Progressive Rock',
                'rating'       => 5,
                'description'  => 'Üks sügavamaid ja mõjuvamaid albumeid rock-ajaloos. Teemad hõlmavad konflikti, ahnsust, aega ja vaimset tervist.',
                'image'        => 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?w=400&q=80',
            ],
            [
                'title'        => 'Back in Black',
                'artist'       => 'AC/DC',
                'release_year' => 1980,
                'genre'        => 'Hard Rock',
                'rating'       => 5,
                'description'  => 'Üks enimmüüdud rock-albumeid maailmas. Salvestati pärast laulja Bon Scotti surma, Brian Johnson debüütalbum bändiga.',
                'image'        => 'https://images.unsplash.com/photo-1498038432885-c6f3f1b912ee?w=400&q=80',
            ],
            [
                'title'        => 'Abbey Road',
                'artist'       => 'The Beatles',
                'release_year' => 1969,
                'genre'        => 'Rock',
                'rating'       => 5,
                'description'  => 'The Beatlesi viimane stuudioalbum. "Come Together", "Something" ja "Here Comes the Sun" kuuluvad ikooniliste laulude hulka.',
                'image'        => 'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?w=400&q=80',
            ],
            [
                'title'        => 'Random Access Memories',
                'artist'       => 'Daft Punk',
                'release_year' => 2013,
                'genre'        => 'Electronic',
                'rating'       => 4,
                'description'  => 'Grammy auhinnaga pärjatud elektrooniline meistriteos. "Get Lucky" ja "Instant Crush" olid globaalsed hitid.',
                'image'        => 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?w=400&q=80',
            ],
            [
                'title'        => '21',
                'artist'       => 'Adele',
                'release_year' => 2011,
                'genre'        => 'Soul',
                'rating'       => 4,
                'description'  => 'Üks 21. sajandi enimmüüdud albumeid. "Rolling in the Deep" ja "Someone Like You" tegid Adelest maailmastaar.',
                'image'        => 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4?w=400&q=80',
            ],
            [
                'title'        => 'Kind of Blue',
                'artist'       => 'Miles Davis',
                'release_year' => 1959,
                'genre'        => 'Jazz',
                'rating'       => 5,
                'description'  => 'Kõigi aegade müüdaim jazz-album. Modaalne jazz oma puhtaimal kujul. Iga muusikasõbra kuulamislisti must-have.',
                'image'        => 'https://images.unsplash.com/photo-1415201364774-f6f0bb35f28f?w=400&q=80',
            ],
            [
                'title'        => 'To Pimp a Butterfly',
                'artist'       => 'Kendrick Lamar',
                'release_year' => 2015,
                'genre'        => 'Hip-Hop',
                'rating'       => 5,
                'description'  => 'Kriitikotest kiidetud hiphop-meistriteos, mis ühendab jazz\'i, funk\'i ja spoken word\'i. Sügavalt isiklik ja sotsiaalselt teadlik.',
                'image'        => 'https://images.unsplash.com/photo-1526478806334-5fd488fcaabc?w=400&q=80',
            ],
        ];

        foreach ($albums as $album) {
            Album::create($album);
        }
    }
}