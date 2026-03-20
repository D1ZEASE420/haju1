<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Nahast seljakott',
                'description' => 'Käsitsi valmistatud täisnahast seljakott igapäevaseks kasutamiseks. Ruumi on sülearvutile, dokumentidele ja kõigele muule vajalikule.',
                'price'       => 89.99,
                'image_url'   => 'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&q=80',
                'category'    => 'kotid',
            ],
            [
                'name'        => 'Juhtmevabad kõrvaklapid',
                'description' => 'Premium juhtmevabad kõrvaklapid aktiivse mürasummutusega. Kuni 30h aku, kiirlaadimise tugi ja premium helikvaliteet.',
                'price'       => 149.00,
                'image_url'   => 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=600&q=80',
                'category'    => 'elektroonika',
            ],
            [
                'name'        => 'Skandinaavia laud',
                'description' => 'Minimalistlik tammepuidust kirjutuslaud. Puhas disain, vastupidavad jalad ja siledalt viimistletud pind. Mõõt 120×60 cm.',
                'price'       => 349.00,
                'image_url'   => 'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?w=600&q=80',
                'category'    => 'mööbel',
            ],
            [
                'name'        => 'Keraamiline kohvikann',
                'description' => 'Käsitsi valmistatud keraamiline kohvikann koos filtriga. Maht 600 ml. Sobib nii kaasaegse kui klassikalise köögiga.',
                'price'       => 34.50,
                'image_url'   => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=600&q=80',
                'category'    => 'köök',
            ],
            [
                'name'        => 'Jooksujalatsid',
                'description' => 'Kerged ja hingavad jooksujalatsid. Membraanvahtkummi tald, reflekteerivad detailid ja ergonoomiliselt kujundatud.',
                'price'       => 119.95,
                'image_url'   => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&q=80',
                'category'    => 'jalanõud',
            ],
            [
                'name'        => 'Taimne eeterlik õli komplekt',
                'description' => 'Komplekt sisaldab 6 erinevat eeterlikku õli: lavendel, piparmünt, eukalüpt, teepuu, sidrunhein ja rosmariini.',
                'price'       => 29.90,
                'image_url'   => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=600&q=80',
                'category'    => 'ilu',
            ],
            [
                'name'        => 'Raamat "Sügav töö"',
                'description' => 'Cal Newport\'i bestseller keskendumisvõime ja produktiivsuse teemal. Õpi töötama häirimatult ja saavutama rohkem lühema ajaga.',
                'price'       => 18.00,
                'image_url'   => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?w=600&q=80',
                'category'    => 'raamatud',
            ],
            [
                'name'        => 'Meditatsiooni matt',
                'description' => 'Paks ja vastupidav meditatsiooni- ja joogamatt. Libisemiskindel põhi, 6mm paksus, keskkonnasõbralik materjal.',
                'price'       => 45.00,
                'image_url'   => 'https://images.unsplash.com/photo-1544367567-0f2fcb009e0b?w=600&q=80',
                'category'    => 'sport',
            ],
            [
                'name'        => 'Nutikell',
                'description' => 'Spordi- ja terviseseire nutikell. GPS, südame löögisageduse monitor, une jälgimine ja 7-päevane aku. Veekindel 5ATM.',
                'price'       => 199.00,
                'image_url'   => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&q=80',
                'category'    => 'elektroonika',
            ],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}