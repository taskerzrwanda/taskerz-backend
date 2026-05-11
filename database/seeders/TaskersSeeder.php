<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TaskersSeeder extends Seeder
{
    public function run()
    {
        $taskers = $this->getTaskersFromData();

        // Insert into the unified users table with role='tasker'.
        // Skip any whose email already exists (idempotent reseed).
        $existingEmails = DB::table('users')->pluck('email')->all();
        $existingSet    = array_flip($existingEmails);
        $toInsert       = array_values(array_filter(
            $taskers,
            fn ($row) => !isset($existingSet[$row['email']])
        ));

        if (!empty($toInsert)) {
            DB::table('users')->insert($toInsert);
        }

        $taskerQuery = DB::table('users')->where('role', 'tasker');
        $this->command->info('Taskers seeded into users table.');
        $this->command->info('Inserted this run: ' . count($toInsert) . ' (skipped existing: ' . (count($taskers) - count($toInsert)) . ')');
        $this->command->info('Total taskers: ' . $taskerQuery->count());
        $this->command->info('Approved: ' . (clone $taskerQuery)->where('status', 'approved')->count());
        $this->command->info('Pending:  ' . (clone $taskerQuery)->where('status', 'pending')->count());
        $this->command->info('Rejected: ' . (clone $taskerQuery)->where('status', 'rejected')->count());
    }

    private function getTaskersFromData()
    {
        // Raw data from the provided JSON
        $rawTaskers = [
            [
                "id" => 105,
                "name" => "Testtt",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "sgsdgs",
                "email" => "1halinton@gmail.com",
                "phone" => "0739383870",
                "profession" => "czcz",
                "work_experience" => "5 fdggwetweetegerhre",
                "status" => "approved"
            ],
            [
                "id" => 104,
                "name" => "Rurangwa Emmanuel",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "ruremmy081@gmail.com",
                "phone" => "0785398125",
                "profession" => "Accounting",
                "work_experience" => "3years of experience",
                "status" => "rejected"
            ],
            [
                "id" => 103,
                "name" => "Manase",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "Manase767@gmail.com",
                "phone" => "+250788642802",
                "profession" => "IT Technician",
                "work_experience" => "6 Years as a computer techinician.",
                "status" => "approved"
            ],
            [
                "id" => 102,
                "name" => "Kalisa N",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "kalisan98@gmail.com",
                "phone" => "+250738606022",
                "profession" => "Supervisor and Project management",
                "work_experience" => "1 year as project manager and site supervisor.",
                "status" => "approved"
            ],
            [
                "id" => 101,
                "name" => "Joseph MUNYESHYAKA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "Josephmunyeshyaka@gmail.com",
                "phone" => "+250784092031",
                "profession" => "Tailor",
                "work_experience" => "Professional tailor for 7 years now.",
                "status" => "approved"
            ],
            [
                "id" => 100,
                "name" => "Diane M",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "Secondary School",
                "email" => "Dianebarista@gmail.com",
                "phone" => "+250791367053",
                "profession" => "Barista",
                "work_experience" => "2 Years as a barista (Coffee)",
                "status" => "approved"
            ],
            [
                "id" => 99,
                "name" => "Jacques",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "jacquestech@gmail.com",
                "phone" => "0783287299",
                "profession" => "Electronic technician",
                "work_experience" => "9 Years of experience in Electronic device setup and repair.",
                "status" => "approved"
            ],
            [
                "id" => 98,
                "name" => "Olivier NKURUNZIZA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "nkurunzizaolivier@gmail.com",
                "phone" => "0788523634",
                "profession" => "IT Technician",
                "work_experience" => "5 Years of experience in Computer network configuration and maintenance, and printer maintenance.",
                "status" => "approved"
            ],
            [
                "id" => 97,
                "name" => "Innocent",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "innocnt2022@gmail.com",
                "phone" => "0786267261",
                "profession" => "Umudozi",
                "work_experience" => "Imyaka 6 ndi umudozi",
                "status" => "approved"
            ],
            [
                "id" => 96,
                "name" => "IMPANO Cynthia",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "impanocynthia@gmail.com",
                "phone" => "+250788235774",
                "profession" => "Decolator",
                "work_experience" => "3 Years doing decolation and supplying decolation materials.",
                "status" => "approved"
            ],
            [
                "id" => 95,
                "name" => "Hussein",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "husseintek@gmail.com",
                "phone" => "0790970200",
                "profession" => "Tv stand techinican",
                "work_experience" => "4 Years of experience in TV Stand setup and repair.",
                "status" => "approved"
            ],
            [
                "id" => 94,
                "name" => "Humbel designer",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "humberdesigner@gmail.com",
                "phone" => "0782673980",
                "profession" => "Designer",
                "work_experience" => "4 Years of experience as a Graphical designer.",
                "status" => "pending"
            ],
            [
                "id" => 93,
                "name" => "Desire MBONIGABA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "mbonigabadesire@gmail.com",
                "phone" => "0782056486",
                "profession" => "Photographer",
                "work_experience" => "Imyaka 14 ndi umufotozi.",
                "status" => "pending"
            ],
            [
                "id" => 92,
                "name" => "Enzo castar",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "enzocastar@gmail.com",
                "phone" => "+250780474871",
                "profession" => "Photographer",
                "work_experience" => "4 Years of experience as a photographer and video editor.",
                "status" => "pending"
            ],
            [
                "id" => 91,
                "name" => "Janvier",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "janvierm@gmail.com",
                "phone" => "+250722862932",
                "profession" => "Driver",
                "work_experience" => "9 Years of experience as a driver.",
                "status" => "pending"
            ],
            [
                "id" => 90,
                "name" => "Emmanuel",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "emmanuel123@gmail.om",
                "phone" => "+250796134688",
                "profession" => "Tekinisiye w'ibikoresho bya erekitotonike",
                "work_experience" => "Imyaka 9 ndi umjutekenisiye wa Radio, Televiziyo, Telephone na Antene",
                "status" => "pending"
            ],
            [
                "id" => 89,
                "name" => "Emmy Tech",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "emmytech@gmail.com",
                "phone" => "+250787698288",
                "profession" => "Television Technician",
                "work_experience" => "Imyaka 8 ndi umutekenisiye wa Televiziyo na Radiyo by'ubwoko byose.",
                "status" => "pending"
            ],
            [
                "id" => 88,
                "name" => "Eric NIYONGIRA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "erictech@gmail.com",
                "phone" => "+250782529728",
                "profession" => "IT Technician",
                "work_experience" => "3 Years of experience in printer configuration and maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 87,
                "name" => "Eugenie KAYITESI",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "Secondary School",
                "email" => "Eugeniek@gmail.com",
                "phone" => "+250788822627",
                "profession" => "Tailor",
                "work_experience" => "7 Years of experience in sewing.",
                "status" => "pending"
            ],
            [
                "id" => 86,
                "name" => "Eric MANISHIMWE",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "Manishimweeric@gmail.com",
                "phone" => "+250780596345",
                "profession" => "Network Engineer",
                "work_experience" => "1 Year of experience in network configuration and maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 85,
                "name" => "Eric",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "ericdj@gmail.com",
                "phone" => "+250781079882",
                "profession" => "Carpenter",
                "work_experience" => "2 Years of experience making furniture.",
                "status" => "pending"
            ],
            [
                "id" => 84,
                "name" => "Enock NIYONGIRA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "S3",
                "email" => "Enockniyongira@gmail.com",
                "phone" => "+250788238000",
                "profession" => "Umubaji",
                "work_experience" => "Imyaka 3 nkora umwuga w'ububaji.",
                "status" => "pending"
            ],
            [
                "id" => 83,
                "name" => "BATAMURIZA Joyce",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "Secondary School",
                "email" => "batamurizajoyce@gmail.com",
                "phone" => "0787500756",
                "profession" => "Barista",
                "work_experience" => "1 year as a barista.",
                "status" => "pending"
            ],
            [
                "id" => 82,
                "name" => "GASIGWA MANIRAFASHA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "Gasigwamanirafasha@gmail.com",
                "phone" => "+250791773781",
                "profession" => "Umubaji",
                "work_experience" => "Imyaka 12 nkora umwuga w'ububaji.",
                "status" => "pending"
            ],
            [
                "id" => 81,
                "name" => "Valentin",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "alenttech@gmail.com",
                "phone" => "+250788250766",
                "profession" => "CCTV Installation techinician",
                "work_experience" => "1 Year of experience in CCTV Camera Installation and maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 80,
                "name" => "Zachee NIYOMUGABO",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "zacheeniyomugabo@gmail.com",
                "phone" => "+250788292124",
                "profession" => "Electrician",
                "work_experience" => "1 year of experience in Electricity installation.",
                "status" => "pending"
            ],
            [
                "id" => 79,
                "name" => "William",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "williamn@gmail.com",
                "phone" => "+250784739403",
                "profession" => "Driver",
                "work_experience" => "2 years of experience in driving.",
                "status" => "pending"
            ],
            [
                "id" => 78,
                "name" => "Fabrice N",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "fabricen@gmail.com",
                "phone" => "+250780676167",
                "profession" => "an artitst",
                "work_experience" => "2 years of experience as an artist in drawing and painting.",
                "status" => "pending"
            ],
            [
                "id" => 77,
                "name" => "Epiphanie",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "epiphanien@gmail.com",
                "phone" => "+250786463560",
                "profession" => "IT Techinician",
                "work_experience" => "1 year of experience in Printer and computer maintenance and repair.",
                "status" => "pending"
            ],
            [
                "id" => 76,
                "name" => "Emile",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "emilerwanda@gmail.com",
                "phone" => "+250789462323",
                "profession" => "plumbing",
                "work_experience" => "2 Years of experience in plumbing.",
                "status" => "pending"
            ],
            [
                "id" => 75,
                "name" => "Cedro HAKIZIMANA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "Cedro21@gmail.com",
                "phone" => "+250726085999",
                "profession" => "Web developer and Network Installation Technician",
                "work_experience" => "6 Years working as a full-stack developer.",
                "status" => "pending"
            ],
            [
                "id" => 74,
                "name" => "Benods",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "benodstech@gmail.com",
                "phone" => "+250784817203",
                "profession" => "Web and App Developer",
                "work_experience" => "6 years in Website and Mobile application development.",
                "status" => "pending"
            ],
            [
                "id" => 73,
                "name" => "Jado Okello",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "okello21@gmail.com",
                "phone" => "+250738741132",
                "profession" => "CCTV Installation techinician",
                "work_experience" => "3 years in CCTV installation and maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 72,
                "name" => "KAMIRI Fred",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "amirifred@gmail.com",
                "phone" => "+250782724996",
                "profession" => "Telecommunication Engineering",
                "work_experience" => "2 years in telecommunication services and electricity",
                "status" => "pending"
            ],
            [
                "id" => 71,
                "name" => "Jeanine",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "Amashuri yisumbuye",
                "email" => "jeannine1@gmail.com",
                "phone" => "+250784066734",
                "profession" => "Kudoda",
                "work_experience" => "Maze imyaka 5 nkora umwuga w'ubudozi.",
                "status" => "pending"
            ],
            [
                "id" => 70,
                "name" => "Adam",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "adamstech@gmail.com",
                "phone" => "+250785721149",
                "profession" => "Computer techinician",
                "work_experience" => "1 year in Computer maintenance",
                "status" => "pending"
            ],
            [
                "id" => 69,
                "name" => "Pio Tech",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "piotech@gmail.com",
                "phone" => "+250782017402",
                "profession" => "Computer techinician",
                "work_experience" => "1 year in smart TV Installation, CCTV installation, and configuration.",
                "status" => "pending"
            ],
            [
                "id" => 68,
                "name" => "Paccy",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "paccytech@gmail.com",
                "phone" => "+250788439085",
                "profession" => "Techinician",
                "work_experience" => "4 years in CCTV Installation and maintenance",
                "status" => "pending"
            ],
            [
                "id" => 67,
                "name" => "Innocent RUHUMURIZA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "Innocenttech@gmail.com",
                "phone" => "+250788919693",
                "profession" => "Techinician",
                "work_experience" => "7 Years of experience in computer hardware maintenance and repair, sound system management.",
                "status" => "pending"
            ],
            [
                "id" => 66,
                "name" => "Destiny",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "destinytechnologies@gmail.com",
                "phone" => "+250781334264",
                "profession" => "health care",
                "work_experience" => "4 years in Printing services.",
                "status" => "pending"
            ],
            [
                "id" => 65,
                "name" => "Didos",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "Didosupplyltd@gmail.com",
                "phone" => "+250781713868",
                "profession" => "Techinician",
                "work_experience" => "6 Years of electronic devices supply and installation.",
                "status" => "pending"
            ],
            [
                "id" => 64,
                "name" => "Samuel ITANGISHAKA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "ericitangishaka1@gmail.com",
                "phone" => "+250725253646",
                "profession" => "Techinician",
                "work_experience" => "5 Years of Experience in Radio and TV maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 63,
                "name" => "Eric",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary School",
                "email" => "ericthech@gmail.com",
                "phone" => "+250782529728",
                "profession" => "Techinician",
                "work_experience" => "8 Years of experience in Computer maintenance, Printer, and scanner maintenance.",
                "status" => "pending"
            ],
            [
                "id" => 62,
                "name" => "Mr Enock",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "alintonfx@gmail.com",
                "phone" => "0780000000",
                "profession" => "Electrician",
                "work_experience" => "10 Years of experience",
                "status" => "pending"
            ],
            [
                "id" => 61,
                "name" => "Jean Paul",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "amizerostatsupply@gmail.com",
                "phone" => "0788357561",
                "profession" => "Printer maintenance techinician",
                "work_experience" => "7 Years of experience supplying technology devices, printers, computer and maintenance of the devices",
                "status" => "pending"
            ],
            [
                "id" => 60,
                "name" => "Emmanuel NDENGEYINGOMA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "allinoneltd@gmail.com",
                "phone" => "0787187224",
                "profession" => "Techinician",
                "work_experience" => "CCTV installation and Printer maintenance technician",
                "status" => "pending"
            ],
            [
                "id" => 59,
                "name" => "Alliane N",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "allianev@gmail.com",
                "phone" => "0780288861",
                "profession" => "Techinician",
                "work_experience" => "2 years in Computer maintenance and software developer",
                "status" => "pending"
            ],
            [
                "id" => 58,
                "name" => "Allain Tresor",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "allaintresor@gmail.com",
                "phone" => "0780591269",
                "profession" => "Web developer",
                "work_experience" => "5 years of experience in web and app dev opp as a full stack dev",
                "status" => "pending"
            ],
            [
                "id" => 57,
                "name" => "Abbax",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "abbaselcurry@gmail.com",
                "phone" => "0791474066",
                "profession" => "Tax declaration technician",
                "work_experience" => "1 year experience doin tax declaration and other financial technical jobs",
                "status" => "pending"
            ],
            [
                "id" => 56,
                "name" => "jado",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "jado@gmail.com",
                "phone" => "+250784945202",
                "profession" => "painting",
                "work_experience" => "6 years  while working",
                "status" => "pending"
            ],
            [
                "id" => 55,
                "name" => "YVES",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "ive@gmail.com",
                "phone" => "+250788932726",
                "profession" => "printing and designer",
                "work_experience" => "2 years of working",
                "status" => "pending"
            ],
            [
                "id" => 54,
                "name" => "itangaishaka",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "....",
                "email" => "itangf@gmail.com",
                "phone" => "0795467121",
                "profession" => "weilding activities",
                "work_experience" => "5 yars of work",
                "status" => "approved"
            ],
            [
                "id" => 53,
                "name" => "Flugence",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "....",
                "email" => "flugence@gmail.com",
                "phone" => "+250782322017",
                "profession" => "ipamba,and other accessories expert",
                "work_experience" => "6 years of experience",
                "status" => "approved"
            ],
            [
                "id" => 52,
                "name" => "Fisto",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "fisto@gmail.com",
                "phone" => "+250784807359",
                "profession" => "furniture expert",
                "work_experience" => "6 years of furniture field",
                "status" => "approved"
            ],
            [
                "id" => 51,
                "name" => "Eugene",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "eugene@gmail.com",
                "phone" => "+250785399213",
                "profession" => "gardening and flower proffesional",
                "work_experience" => "3 years of experience",
                "status" => "approved"
            ],
            [
                "id" => 50,
                "name" => "Eldephonse",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "eled@gmail.com",
                "phone" => "+250798597758",
                "profession" => "real estate proffesional",
                "work_experience" => "5 years of expereince",
                "status" => "pending"
            ],
            [
                "id" => 49,
                "name" => "Cyiza",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "cyiza@gmail.com",
                "phone" => "+250782613727",
                "profession" => "an artist",
                "work_experience" => "3 years of exprience",
                "status" => "pending"
            ],
            [
                "id" => 48,
                "name" => "Jean paul HAKIZIMANA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "High school",
                "email" => "Jeanpaul@gmail.com",
                "phone" => "+250723863811",
                "profession" => "Techinician",
                "work_experience" => "12 Years of experience in phones and computer repair",
                "status" => "pending"
            ],
            [
                "id" => 47,
                "name" => "Janvier",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "High school",
                "email" => "janvier2020@gmail.com",
                "phone" => "0722862932",
                "profession" => "Driver",
                "work_experience" => "10 years of driving",
                "status" => "pending"
            ],
            [
                "id" => 46,
                "name" => "Claude",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "claude@gmail.com",
                "phone" => "0786584614",
                "profession" => "washing machine technician",
                "work_experience" => "4+years experience",
                "status" => "pending"
            ],
            [
                "id" => 45,
                "name" => "BAHATI Julien",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "bgdesigns@gmail.com",
                "phone" => "0787884518",
                "profession" => "Photographer and designer",
                "work_experience" => "8 years in photo and video shouting, graphical designer and an Engineer",
                "status" => "pending"
            ],
            [
                "id" => 44,
                "name" => "Antionne NTIRENGANYA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "High school",
                "email" => "antoin1987@gmail.com",
                "phone" => "0788285414",
                "profession" => "Driver",
                "work_experience" => "2 years in driving",
                "status" => "pending"
            ],
            [
                "id" => 43,
                "name" => "Aldo Tech",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "aldomugabo@gmail.com",
                "phone" => "+250788224437",
                "profession" => "IT technician",
                "work_experience" => "3 Years of experience in Computer hardware and software maintenance",
                "status" => "approved"
            ],
            [
                "id" => 42,
                "name" => "francis",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "francis@gmail.com",
                "phone" => "0792415302",
                "profession" => "technician",
                "work_experience" => "6 YEARS OF DEALING WITH FURNITURE AND MDF",
                "status" => "approved"
            ],
            [
                "id" => 41,
                "name" => "Atanaazi",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "atanazi@gmail.com",
                "phone" => "0787804968",
                "profession" => "kudoda intebe",
                "work_experience" => "imyaka itanu",
                "status" => "approved"
            ],
            [
                "id" => 40,
                "name" => "Deo",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "deo@gmail.com",
                "phone" => "+250791979935",
                "profession" => "cleaning services",
                "work_experience" => "5 years of experience",
                "status" => "approved"
            ],
            [
                "id" => 39,
                "name" => "emmanuel nsekanabo",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "ema@gmail.com",
                "phone" => "0785155027",
                "profession" => "chair maker",
                "work_experience" => "for 10+ i have been in chair making and repairing",
                "status" => "approved"
            ],
            [
                "id" => 38,
                "name" => "abdul",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "abdul2@gmail.com",
                "phone" => "+250784874351",
                "profession" => "chair maker",
                "work_experience" => "i have been manufacturing chairs and repairing them",
                "status" => "approved"
            ],
            [
                "id" => 37,
                "name" => "Pacifique",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "Secondary school",
                "email" => "paccy1990@gmail.com",
                "phone" => "0788439085",
                "profession" => "Techinician",
                "work_experience" => "8 Years in technical work, computer maintenance and selling spare parts and full computer and phones.",
                "status" => "approved"
            ],
            [
                "id" => 36,
                "name" => "Emile NYANDWI",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "Emilenyandwi@gmail.com",
                "phone" => "0725765411",
                "profession" => "Techinician",
                "work_experience" => "10 Years of experience in Phone and computer repair, Electricity and Sound system management.",
                "status" => "approved"
            ],
            [
                "id" => 35,
                "name" => "Nyandwi Isaie",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "nyandwiisaa57@gmail.com",
                "phone" => "0784168330",
                "profession" => "Construction",
                "work_experience" => "5years of work experience",
                "status" => "approved"
            ],
            [
                "id" => 34,
                "name" => "Test Test",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "test@gmail.com",
                "phone" => "0780000000",
                "profession" => "plumbing",
                "work_experience" => "7 years of experience in plumbing",
                "status" => "pending"
            ],
            [
                "id" => 33,
                "name" => "Alexis HAKIZIMANA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "hakizimanaalexis123@gmail.com",
                "phone" => "0786731449",
                "profession" => "plumbing",
                "work_experience" => "uhwdjgjgdjgfea",
                "status" => "approved"
            ],
            [
                "id" => 32,
                "name" => "Hategekimana Eric",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "erickerix8@gmail.com",
                "phone" => "0793163301",
                "profession" => "plumbing",
                "work_experience" => "I  usually work as a causal in painting houses in our area",
                "status" => "approved"
            ],
            [
                "id" => 31,
                "name" => "Uwimpuhwe clementine",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "Bachelor degree",
                "email" => "uwimpuhwec98@gmail.com",
                "phone" => "0781466506",
                "profession" => "Artist Painter",
                "work_experience" => "I have been working for 6 years i have experience of painting on wall(mural painting), painting on canvas, i'm able to be creative or imitating what a client wants, i",
                "status" => "approved"
            ],
            [
                "id" => 30,
                "name" => "Umutoni Farida",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "umutonifarida5@gmail.com",
                "phone" => "0789169974",
                "profession" => "Cleaning services",
                "work_experience" => "Bright and clean cleaning service ltd \nKigali -Rwanda\n\nEstablished and grew a premier cleaning services company in Rwanda...",
                "status" => "approved"
            ],
            [
                "id" => 29,
                "name" => "Jean Paul NSABIMANA",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "niwelena2014@gmail.com",
                "phone" => "0788666768",
                "profession" => "Electrical and Electronics",
                "work_experience" => "I'm experienced within 18 years in Satellite TV Channels Installation , CCTV Cameras, Air Conditionner &Refrigeration System, Power System (transformer and Switcgear)& Electrical Installation .",
                "status" => "approved"
            ],
            [
                "id" => 28,
                "name" => "david twizera",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "davidtwizere122@gmail.com",
                "phone" => "+250791516018",
                "profession" => "plumbing",
                "work_experience" => "5 years, and good for repairing and for domestic problems",
                "status" => "approved"
            ],
            [
                "id" => 27,
                "name" => "Sam mugisha",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "High school graduate",
                "email" => "kwizallan@gmail.com",
                "phone" => "07878715579",
                "profession" => "Electrician",
                "work_experience" => "1 year experience  of internship in retail shopping  shop keeper , participated  internship 2two months power center  in electric maintenance  domestic  installation",
                "status" => "approved"
            ],
            [
                "id" => 26,
                "name" => "Boris Manzi",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "borisxkjb@gmail.com",
                "phone" => "0790902787",
                "profession" => "health care",
                "work_experience" => "iba;os dakbjsd lkajbs flzkjxgfovisludgfl ksjdbfpaw9efg pkdjbf sipfy ap9w7 sozjdp syf w0ef gzdofuw97etf",
                "status" => "rejected"
            ],
            [
                "id" => 25,
                "name" => "Boris Manzi",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "boris@gmail.com",
                "phone" => "0790902787",
                "profession" => "plumbing",
                "work_experience" => "Please provide at least 10 characters about your experi",
                "status" => "rejected"
            ],
            [
                "id" => 24,
                "name" => "Ndatimana Pierre",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "ndatimanapierre0@gmail.com",
                "phone" => "0781447770",
                "profession" => "plumbing",
                "work_experience" => "Hi, In 2022 I do internship in HORIZON SOPYRWA in irrigation system and pumping installations \n2023I work in virunga inn spa and resort in installation of cold and hot water systems \nAnd also I have experience in swimming pool installation and it's advice maintenance filters and pumps  thanks",
                "status" => "pending"
            ],
            [
                "id" => 23,
                "name" => "Nsengiyumva Alphonse",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "nsengalphonse02@gmail.com",
                "phone" => "0728696341",
                "profession" => "Electrical , cctv cameras, Local area network extension & installation technician",
                "work_experience" => "More than 5 years",
                "status" => "pending"
            ],
            [
                "id" => 22,
                "name" => "destin iraguha",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "irdestin2@gmail.com",
                "phone" => "0785868145",
                "profession" => "plumbing",
                "work_experience" => "hello test",
                "status" => "pending"
            ],
            [
                "id" => 21,
                "name" => "GASASIRA EMMY",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "gasasiraemmy36@gmail.com",
                "phone" => "0783544930",
                "profession" => "plumbing",
                "work_experience" => "Online services\n\nDesign\n\nVisa applications",
                "status" => "pending"
            ],
            [
                "id" => 20,
                "name" => "Umuriza  Dinah",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "dinaumuliza12@gmail.com",
                "phone" => "0781044960",
                "profession" => "health care",
                "work_experience" => "Worked at hairdressers rwanda",
                "status" => "approved"
            ],
            [
                "id" => 19,
                "name" => "bagena",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "hellow",
                "email" => "bassagena@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "ssssssssssssssssssss",
                "status" => "approved"
            ],
            [
                "id" => 18,
                "name" => "bagena",
                "nationality" => "kenya",
                "gender" => "female",
                "education" => "mathe",
                "email" => "baawgena@gmail.com",
                "phone" => "0785830612",
                "profession" => "technic",
                "work_experience" => "wwwwwwwwwwwwwwwwwwwwwww",
                "status" => "rejected"
            ],
            [
                "id" => 17,
                "name" => "Boris og",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "borisog@gmail.com",
                "phone" => "0790902787",
                "profession" => "plumbing",
                "work_experience" => "rt the conversation and tell us what you need done. This helps us send you only qualified and available Taskers for the job.",
                "status" => "approved"
            ],
            [
                "id" => 16,
                "name" => "Rutagengwa Erics",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "admin@email.com",
                "phone" => "0785171717",
                "profession" => "plumbing",
                "work_experience" => "Hello shfjhskdfhk",
                "status" => "approved"
            ],
            [
                "id" => 15,
                "name" => "prince bagena",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "baagena@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "4 years Experience and 7 years of Education and graduating",
                "status" => "approved"
            ],
            [
                "id" => 14,
                "name" => "Rutagengwa Eric",
                "nationality" => "kenya",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "rutagengwaeric250@gmail.cdom",
                "phone" => "0785171717",
                "profession" => "plumbing",
                "work_experience" => "sdfsdffffffffffffffffff",
                "status" => "approved"
            ],
            [
                "id" => 13,
                "name" => "Rutagengwa Eric",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "tumusifuolivier5@gsmail.com",
                "phone" => "0785171717",
                "profession" => "plumbing",
                "work_experience" => "Professional plusdfsdfmbing In RPC(Rwanda plastic campany) 1year experiences",
                "status" => "pending"
            ],
            [
                "id" => 12,
                "name" => "prince bagena",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "princebagezzna@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "hellow dddddddffffffffffddd",
                "status" => "pending"
            ],
            [
                "id" => 11,
                "name" => "Tumusifu Olivier",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "tumusifuolivier5@gmail.com",
                "phone" => "0789538249",
                "profession" => "plumbing",
                "work_experience" => "Professional plumbing In RPC(Rwanda plastic campany) 1year experience",
                "status" => "approved"
            ],
            [
                "id" => 10,
                "name" => "Asiimwe Allan",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "asiimweallan1000@gmail.com",
                "phone" => "0785775280",
                "profession" => "plumbing",
                "work_experience" => "worked at Rubis Energy",
                "status" => "pending"
            ],
            [
                "id" => 9,
                "name" => "Boris Manzi",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "undergraduate",
                "email" => "borisog784@gmail.com",
                "phone" => "0790902787",
                "profession" => "plumbing",
                "work_experience" => "Ysidjnd djdidhd s sisbe",
                "status" => "pending"
            ],
            [
                "id" => 8,
                "name" => "prince bagenaddddd",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "postgraduate",
                "email" => "princebagenaaa@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "ddddddddddssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssssss",
                "status" => "pending"
            ],
            [
                "id" => 7,
                "name" => "ss",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "postgraduate",
                "email" => "princebana@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "Hello brother how are you my ghee",
                "status" => "approved"
            ],
            [
                "id" => 6,
                "name" => "sdsdsdsdsd",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "princebagenaa@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "sssssssssssssssssss",
                "status" => "pending"
            ],
            [
                "id" => 5,
                "name" => "prince bagena",
                "nationality" => "rwanda",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "princessbagena@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "ssssssssssssssssssssss",
                "status" => "pending"
            ],
            [
                "id" => 4,
                "name" => "prince bagena",
                "nationality" => "rwanda",
                "gender" => "male",
                "education" => "postgraduate",
                "email" => "princebagena@gmail.com",
                "phone" => "0785830612",
                "profession" => "plumbing",
                "work_experience" => "hhhhhhhhhhhhhhhhhhhhhhhhhhhh",
                "status" => "pending"
            ],
            [
                "id" => 1,
                "name" => "John Doe",
                "nationality" => "Rwandan",
                "gender" => "Male",
                "education" => "Bachelor",
                "email" => "john@example.com",
                "phone" => "0788888888",
                "profession" => "Software Developer",
                "work_experience" => "5 years experience in Laravel and React",
                "status" => "pending"
            ],
            [
                "id" => 2,
                "name" => "Jane Smith",
                "nationality" => "Kenyan",
                "gender" => "Female",
                "education" => "Masters",
                "email" => "jane@example.com",
                "phone" => "0712345678",
                "profession" => "Data Analyst",
                "work_experience" => "3 years experience in data science and Python",
                "status" => "pending"
            ],
            [
                "id" => 3,
                "name" => "Bagena prince",
                "nationality" => "kenya",
                "gender" => "female",
                "education" => "undergraduate",
                "email" => "rutagengwaeric250@gmail.com",
                "phone" => "0798765432",
                "profession" => "plumbing",
                "work_experience" => "Expert in Laravel, Vue.js, React, and GoLang",
                "status" => "approved"
            ],
        ];

        // Filter out test/invalid entries
        $filteredTaskers = array_filter($rawTaskers, function($tasker) {
            // Remove entries with obviously fake/test names
            if (preg_match('/test|ss|sdsdsdsd|bagena(d*)/i', $tasker['name'])) {
                return false;
            }

            // Remove entries with test emails
            if (preg_match('/test|example|admin/i', $tasker['email'])) {
                return false;
            }

            // Remove entries with clearly fake phone numbers (all zeros or repeated digits)
            if (preg_match('/^0{8,}|^0780000000|^0798765432$/', $tasker['phone'])) {
                return false;
            }

            // Remove entries with gibberish education
            if (preg_match('/sgsdgs|hellow|\.\.\.\.|mathe/i', $tasker['education'])) {
                return false;
            }

            // Remove entries with gibberish professions
            if (preg_match('/czcz|technic/i', $tasker['profession'])) {
                return false;
            }

            // Remove entries with clearly fake work experience (repetitive characters, gibberish)
            if (preg_match('/^s+$|^d+$|^w+$|^h+$|fdggwetweetegerhre|uhwdjgjgdjgfea|shfjhskdfhk/', $tasker['work_experience'])) {
                return false;
            }

            // Remove duplicate entries (keep one instance of each valid tasker)
            if ($tasker['id'] == 25 || $tasker['id'] == 26) {
                return false; // Duplicate/very similar to others
            }

            return true;
        });

        // Re-index the array
        $filteredTaskers = array_values($filteredTaskers);

        // Transform to match database schema
        $taskers = [];
        $kigaliDistricts = [
            'Nyarugenge', 'Kicukiro', 'Gasabo', 'Remera', 'Kacyiru',
            'Gikondo', 'Kimihurura', 'Nyamirambo', 'Kagarama', 'Gisozi',
            'Kibagabaga', 'Kanombe', 'Kimironko', 'Kinyinya', 'Biryogo'
        ];

        foreach ($filteredTaskers as $tasker) {
            // Assign a random district in Kigali (since original data doesn't have location)
            $district = $kigaliDistricts[array_rand($kigaliDistricts)];
            $coordinates = $this->getKigaliCoordinates($district);

            // Extract skills from profession and work experience
            $skills = $this->extractSkills($tasker['profession'], $tasker['work_experience']);

            // Clean up phone number format
            $phone = $this->formatPhoneNumber($tasker['phone']);

            // Ensure nationality is properly capitalized
            $nationality = ucfirst(strtolower($tasker['nationality']));

            $taskers[] = [
                'name'              => $tasker['name'],
                'email'             => strtolower($tasker['email']),
                'password'          => Hash::make(env('TASKER_SEED_PASSWORD', 'TaskerSeed!2026')),
                'role'              => 'tasker',
                'status'            => $tasker['status'],
                'email_verified_at' => $tasker['status'] === 'approved' ? Carbon::now() : null,
                'phone'             => $phone,
                'nationality'       => $nationality,
                'gender'            => ucfirst(strtolower($tasker['gender'])),
                'education'         => $this->cleanEducation($tasker['education']),
                'profession'        => $this->cleanProfession($tasker['profession']),
                'work_experience'   => $tasker['work_experience'],
                'city'              => 'Kigali',
                'district'          => $district,
                'latitude'          => $coordinates['latitude'],
                'longitude'         => $coordinates['longitude'],
                'skills'            => json_encode($skills),
                'completed_tasks'   => 0,
                'rating'            => 0,
                'created_at'        => Carbon::now()->subDays(rand(0, 365)),
                'updated_at'        => Carbon::now(),
            ];
        }

        return $taskers;
    }

    private function extractSkills($profession, $workExperience)
    {
        $skills = [];

        // Map professions to skills
        $professionSkills = [
            'IT Technician' => ['computer hardware', 'network configuration', 'printer maintenance', 'system repair'],
            'Electronic technician' => ['electronics repair', 'device setup', 'troubleshooting', 'circuit testing'],
            'Tailor' => ['sewing', 'alterations', 'fabric cutting', 'garment making'],
            'Umudozi' => ['sewing', 'tailoring', 'fabric work', 'garment repair'],
            'Barista' => ['coffee making', 'espresso', 'latte art', 'customer service'],
            'Decolator' => ['decoration', 'event setup', 'material supply', 'design'],
            'Tv stand techinican' => ['tv mounting', 'wall installation', 'cable management', 'setup'],
            'Designer' => ['graphic design', 'creative design', 'digital art', 'visual communication'],
            'Photographer' => ['photography', 'video editing', 'photo editing', 'shooting'],
            'Driver' => ['driving', 'vehicle maintenance', 'route planning', 'safe transport'],
            'CCTV Installation techinician' => ['cctv installation', 'security systems', 'camera setup', 'surveillance'],
            'Electrician' => ['electrical installation', 'wiring', 'lighting', 'circuit repair'],
            'Carpenter' => ['woodworking', 'furniture making', 'cabinet making', 'repair'],
            'Umubaji' => ['carpentry', 'woodworking', 'furniture making', 'joinery'],
            'Network Engineer' => ['network configuration', 'network maintenance', 'cabling', 'routing'],
            'plumbing' => ['pipe installation', 'leak repair', 'fixture installation', 'drainage'],
            'Web developer' => ['web development', 'programming', 'full stack', 'coding'],
            'Cleaning services' => ['deep cleaning', 'sanitization', 'housekeeping', 'office cleaning'],
            'Artist Painter' => ['painting', 'mural art', 'canvas painting', 'decorative art'],
            'Electrical and Electronics' => ['electrical systems', 'electronics repair', 'satellite installation', 'cctv'],
            'Construction' => ['building', 'construction work', 'site supervision', 'project management'],
            'furniture expert' => ['furniture making', 'furniture repair', 'wood finishing', 'upholstery'],
            'chair maker' => ['chair manufacturing', 'furniture repair', 'woodworking', 'upholstery'],
            'gardening and flower proffesional' => ['gardening', 'landscaping', 'plant care', 'flower arrangement'],
        ];

        // Check if profession exists in our mapping
        foreach ($professionSkills as $key => $skillSet) {
            if (stripos($profession, $key) !== false || stripos($key, $profession) !== false) {
                $skills = array_merge($skills, $skillSet);
                break;
            }
        }

        // If no skills found, extract from profession
        if (empty($skills)) {
            $words = explode(' ', strtolower($profession));
            foreach ($words as $word) {
                if (strlen($word) > 3 && !in_array($word, ['with', 'and', 'the', 'for', 'expert'])) {
                    $skills[] = $word;
                }
            }
        }

        return array_slice(array_unique($skills), 0, 5);
    }

    private function formatPhoneNumber($phone)
    {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // If it starts with 0 and is 10 digits, add +250 prefix
        if (preg_match('/^0[0-9]{9}$/', $phone)) {
            $phone = '+250' . substr($phone, 1);
        }

        return $phone;
    }

    private function cleanEducation($education)
    {
        $education = trim($education);

        // Map common education values
        $educationMap = [
            'Secondary School' => 'Secondary School Certificate',
            'Secondary school' => 'Secondary School Certificate',
            'High school' => 'High School Diploma',
            'High school graduate' => 'High School Diploma',
            'undergraduate' => "Bachelor's Degree",
            'postgraduate' => "Master's Degree",
            'Bachelor degree' => "Bachelor's Degree",
            'TVET Certificate' => "TVET Certificate",
            'S3' => 'Secondary School (S3)',
            'Amashuri yisumbuye' => 'Secondary School',
        ];

        foreach ($educationMap as $key => $value) {
            if (stripos($education, $key) !== false) {
                return $value;
            }
        }

        return $education ?: 'Vocational Training';
    }

    private function cleanProfession($profession)
    {
        $profession = trim($profession);

        // Standardize profession names
        $professionMap = [
            'IT Technician' => 'IT Technician',
            'Electronic technician' => 'Electronics Technician',
            'Tv stand techinican' => 'TV Installation Technician',
            'CCTV Installation techinician' => 'CCTV Installation Technician',
            'Web developer' => 'Web Developer',
            'Network Engineer' => 'Network Engineer',
            'Telecommunication Engineering' => 'Telecommunications Engineer',
            'Electrical and Electronics' => 'Electrical & Electronics Technician',
            'Umudozi' => 'Tailor',
            'Umubaji' => 'Carpenter',
            'kudoda intebe' => 'Chair Maker',
            'Kudoda' => 'Tailor',
            'ipamba,and other accessories expert' => 'Accessories & Crafts Expert',
            'an artitst' => 'Artist',
            'an artist' => 'Artist',
            'Artist Painter' => 'Artist Painter',
            'furniture expert' => 'Furniture Expert',
            'chair maker' => 'Chair Maker',
            'gardening and flower proffesional' => 'Gardening Specialist',
            'real estate proffesional' => 'Real Estate Professional',
            'health care' => 'Healthcare Assistant',
            'cleaning services' => 'Cleaning Services',
            'Decolator' => 'Decoration Specialist',
            'Supervisor and Project management' => 'Project Supervisor',
            'Accounting' => 'Accountant',
            'Tax declaration technician' => 'Tax Consultant',
            'printing and designer' => 'Printing & Design Specialist',
            'weilding activities' => 'Welding Specialist',
        ];

        foreach ($professionMap as $key => $value) {
            if (stripos($profession, $key) !== false || stripos($key, $profession) !== false) {
                return $value;
            }
        }

        // Capitalize each word
        return ucwords(strtolower($profession));
    }

    private function getKigaliCoordinates($district)
    {
        // Approximate coordinates for Kigali districts
        $coordinates = [
            'Nyarugenge' => ['latitude' => -1.9487, 'longitude' => 30.0596],
            'Kicukiro' => ['latitude' => -1.9845, 'longitude' => 30.0980],
            'Gasabo' => ['latitude' => -1.9441, 'longitude' => 30.0619],
            'Remera' => ['latitude' => -1.9536, 'longitude' => 30.1252],
            'Kacyiru' => ['latitude' => -1.9306, 'longitude' => 30.0870],
            'Gikondo' => ['latitude' => -1.9782, 'longitude' => 30.0897],
            'Kimihurura' => ['latitude' => -1.9514, 'longitude' => 30.0935],
            'Nyamirambo' => ['latitude' => -1.9660, 'longitude' => 30.0443],
            'Kagarama' => ['latitude' => -2.0012, 'longitude' => 30.1147],
            'Gisozi' => ['latitude' => -1.9386, 'longitude' => 30.0737],
            'Kibagabaga' => ['latitude' => -1.9094, 'longitude' => 30.1101],
            'Kanombe' => ['latitude' => -1.9742, 'longitude' => 30.1381],
            'Kimironko' => ['latitude' => -1.9167, 'longitude' => 30.1333],
            'Kinyinya' => ['latitude' => -1.8936, 'longitude' => 30.1219],
            'Biryogo' => ['latitude' => -1.9575, 'longitude' => 30.0528],
        ];

        return $coordinates[$district] ?? ['latitude' => -1.9497, 'longitude' => 30.0587];
    }
}
