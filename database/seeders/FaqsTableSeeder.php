<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class FaqsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $faqs = [
            [
                'id' => 1,
                'question' => 'What is TASKERZ?',
                'answer' => 'TASKERZ is your all-in-one solution for skilled technical services and handyman work — from plumbing and electrical tasks to deep cleaning, furniture assembly, gardening, and much more. Whether you need help at home or on a job site, we\'ve got a skilled tasker for you.',
                'created_at' => '2025-03-20 07:25:46',
                'updated_at' => '2025-04-21 15:54:09'
            ],
            [
                'id' => 2,
                'question' => 'What types of services can I book through TASKERZ?',
                'answer' => 'We provide a wide range of services, including:\nFurniture Assembly\nPlumbing\nElectrical Work\nDeep & Machine-Aided Cleaning\nGeneral Handyman Repairs\nPainting & Renovation\nGardening & Outdoor Maintenance\nMoving Support\nSmart Home Installations & more!',
                'created_at' => '2025-03-20 07:26:36',
                'updated_at' => '2025-04-21 15:56:01'
            ],
            [
                'id' => 5,
                'question' => 'How do I book a tasker?',
                'answer' => 'Booking is easy!\nVisit our Booking Page\nSelect the service you need\nProvide your location and preferred time\nConfirm the request — and we\'ll match you with a qualified tasker right away!',
                'created_at' => '2025-04-21 15:57:22',
                'updated_at' => '2025-04-21 15:57:22'
            ],
            [
                'id' => 6,
                'question' => 'Can I become a tasker with TASKERZ?',
                'answer' => 'Absolutely! If you have skills in any of the sectors we cover — plumbing, electrical, cleaning, furniture assembly, etc. — you can apply to join our network of professional taskers.\nVisit our Join as a Tasker page.\nhttps://www.taskers.rw/become-tasker ,\n fill out the application, and our team will be in touch!',
                'created_at' => '2025-04-21 15:58:47',
                'updated_at' => '2025-04-21 15:59:36'
            ],
            [
                'id' => 7,
                'question' => 'Are your taskers experienced and verified?',
                'answer' => 'Yes, all our taskers go through a verification process to ensure they are skilled, reliable, and professional. Many of our taskers also bring years of hands-on experience in their respective trades.',
                'created_at' => '2025-04-21 16:00:34',
                'updated_at' => '2025-04-21 16:00:34'
            ],
            [
                'id' => 8,
                'question' => 'Is there a minimum booking time or fee?',
                'answer' => 'We aim to keep things flexible and affordable. Some services may have a minimum call-out charge or duration, depending on the task type and location. You\'ll see all pricing details before you confirm your booking.',
                'created_at' => '2025-04-21 16:01:43',
                'updated_at' => '2025-04-21 16:01:43'
            ]
        ];

        DB::table('faqs')->insert($faqs);
    }
}
