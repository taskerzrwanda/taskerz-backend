<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestimonialsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $testimonials = [
            [
                'id' => 4,
                'quote' => '"Thanks to the amazing team at Taskers, our transition to the new headquarters was seamless! They handled everything with care and professionalism, making the process stress-free."',
                'author_name' => 'Nyarwaya Wilson',
                'author_title' => 'Managing Director',
                'company' => 'Ishema Tours',
                'media_type' => 'image',
                'media_path' => 'testimonials/PKEMBeE33sNP2jZmgGQOhwLDdcxBFsXgJ6N7tMd8.png',
                'created_at' => '2025-04-06 17:56:59',
                'updated_at' => '2025-04-22 14:38:42'
            ],
            [
                'id' => 5,
                'quote' => '"A huge shoutout to the incredible crew at Taskers for making our move to the new headquarters so smooth! Their attention to detail and professionalism."',
                'author_name' => 'mr. Steven',
                'author_title' => 'a Lawyer',
                'company' => 'preferred not to say',
                'media_type' => 'image',
                'media_path' => 'testimonials/L9RvU0Jci1BTlqYoYXj4QNqNU7MFMyIHZkPj7AIt.jpg',
                'created_at' => '2025-04-06 17:57:29',
                'updated_at' => '2025-04-22 14:38:14'
            ],
            [
                'id' => 7,
                'quote' => '"We just moved and needed furniture assembly, cleaning, and TV mounting all in one day. TASKERZ sent a full team and handled everything smoothly. They made moving way less stressful."',
                'author_name' => 'mr. Ishimwe',
                'author_title' => 'a Businessman',
                'company' => 'preferred not say',
                'media_type' => 'image',
                'media_path' => 'testimonials/Z4zjCcLe0ffIKfYpUHnwpTj4XRBD4uxNoPk9KHo2.jpg',
                'created_at' => '2025-04-21 16:13:21',
                'updated_at' => '2025-04-21 16:13:21'
            ],
            [
                'id' => 8,
                'quote' => '"I needed someone to assemble a big wardrobe. The tasker came right on time, had all the tools, and finished everything in under 3 hours. Zero stress. Will definitely book again."',
                'author_name' => 'Mr. kanamugire',
                'author_title' => 'a business man',
                'company' => 'preffered not to say',
                'media_type' => 'image',
                'media_path' => 'testimonials/uogLLl7X76YnlshU9QPetmUS857dcc6RuwPJDEMf.jpg',
                'created_at' => '2025-04-21 16:16:22',
                'updated_at' => '2025-04-21 16:16:22'
            ]
        ];

        DB::table('testimonials')->insert($testimonials);
    }
}
