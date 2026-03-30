<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\SubTask;
use Illuminate\Support\Facades\DB;

class TasksAndSubTasksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Starting Tasks and SubTasks seeding...');

        // Clear existing data (optional - comment out if you want to keep existing data)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        SubTask::truncate();
        Task::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('🗑️  Cleared existing tasks and subtasks');

        // Seed all tasks
        $this->seedAssemblyTask();
        $this->seedMountingTask();
        $this->seedMovingTask();
        $this->seedCleaningTask();
        $this->seedOutdoorHelpTask();
        $this->seedHomeRepairsTask();
        $this->seedPaintingTask();

        $this->command->info('✅ Seeding completed successfully!');
        $this->command->info('📊 Total Tasks: ' . Task::count());
        $this->command->info('📊 Total SubTasks: ' . SubTask::count());
    }

    /**
     * Seed Assembly task and subtasks
     */
    private function seedAssemblyTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/assembly.jpg',
            'title' => 'Furniture Assembly Services',
            'status' => 'active',
            'tags' => ['assembly', 'furniture', 'ikea', 'installation', 'setup']
        ]);

        $subtasks = [
            [
                'name' => 'Bed Frame Assembly',
                'price' => 25000,
                'duration' => '1-2 hours',
                'description' => 'Professional assembly of all types of bed frames including platform beds, bunk beds, and storage beds. We handle IKEA, Furni-nest, and other brands.',
                'status' => 'active'
            ],
            [
                'name' => 'Wardrobe & Closet Assembly',
                'price' => 35000,
                'duration' => '2-3 hours',
                'description' => 'Complete assembly of wardrobes, closets, and armoires. Includes alignment, door adjustment, and drawer installation.',
                'status' => 'active'
            ],
            [
                'name' => 'Desk & Office Furniture Assembly',
                'price' => 20000,
                'duration' => '1-1.5 hours',
                'description' => 'Assembly of office desks, computer tables, filing cabinets, and bookshelves. Perfect for home offices and workspaces.',
                'status' => 'active'
            ],
            [
                'name' => 'Dining Table & Chairs Assembly',
                'price' => 30000,
                'duration' => '1.5-2 hours',
                'description' => 'Assembly of dining tables, chairs, benches, and bar stools. Includes stability testing and leveling.',
                'status' => 'active'
            ],
            [
                'name' => 'Shelving & Storage Unit Assembly',
                'price' => 18000,
                'duration' => '1 hour',
                'description' => 'Installation of wall shelves, standing shelves, storage cubes, and modular storage systems.',
                'status' => 'active'
            ],
            [
                'name' => 'Kids Furniture Assembly',
                'price' => 22000,
                'duration' => '1-2 hours',
                'description' => 'Safe assembly of cribs, changing tables, toy storage, and children\'s furniture with extra attention to safety standards.',
                'status' => 'active'
            ],
            [
                'name' => 'Outdoor Furniture Assembly',
                'price' => 28000,
                'duration' => '1.5-2 hours',
                'description' => 'Assembly of garden furniture, patio sets, gazebos, and outdoor storage units.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Assembly task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Mounting task and subtasks
     */
    private function seedMountingTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/mounting.jpg',
            'title' => 'TV & Wall Mounting Services',
            'status' => 'active',
            'tags' => ['mounting', 'tv', 'wall mount', 'installation', 'hanging']
        ]);

        $subtasks = [
            [
                'name' => 'TV Wall Mounting',
                'price' => 30000,
                'duration' => '1-2 hours',
                'description' => 'Professional TV mounting on any wall type. Includes cable management, leveling, and bracket installation. Supports all TV sizes.',
                'status' => 'active'
            ],
            [
                'name' => 'Mirror & Picture Hanging',
                'price' => 15000,
                'duration' => '30-45 minutes',
                'description' => 'Secure hanging of mirrors, picture frames, artwork, and decorative items. Perfect alignment guaranteed.',
                'status' => 'active'
            ],
            [
                'name' => 'Curtain Rod & Blind Installation',
                'price' => 20000,
                'duration' => '1 hour',
                'description' => 'Installation of curtain rods, blinds, shades, and window treatments. Includes proper alignment and functionality testing.',
                'status' => 'active'
            ],
            [
                'name' => 'Shelf & Cabinet Wall Mounting',
                'price' => 25000,
                'duration' => '1-1.5 hours',
                'description' => 'Secure wall mounting of floating shelves, wall cabinets, and storage units. Weight-tested for safety.',
                'status' => 'active'
            ],
            [
                'name' => 'Soundbar & Speaker Installation',
                'price' => 18000,
                'duration' => '45 minutes',
                'description' => 'Wall mounting of soundbars, speakers, and audio equipment. Includes cable concealment and optimal positioning.',
                'status' => 'active'
            ],
            [
                'name' => 'Whiteboard & Corkboard Installation',
                'price' => 12000,
                'duration' => '30 minutes',
                'description' => 'Installation of whiteboards, corkboards, and bulletin boards for home offices and study areas.',
                'status' => 'active'
            ],
            [
                'name' => 'Bicycle & Sports Equipment Wall Mounting',
                'price' => 16000,
                'duration' => '45 minutes',
                'description' => 'Wall mounting solutions for bicycles, surfboards, skis, and other sports equipment.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Mounting task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Moving task and subtasks
     */
    private function seedMovingTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/moving.jpg',
            'title' => 'Moving & Logistics Support',
            'status' => 'active',
            'tags' => ['moving', 'relocation', 'packing', 'transport', 'logistics']
        ]);

        $subtasks = [
            [
                'name' => 'Full Home Moving Service',
                'price' => 150000,
                'duration' => 'Full day',
                'description' => 'Complete home relocation service including packing, loading, transport, unloading, and unpacking. Professional movers with experience.',
                'status' => 'active'
            ],
            [
                'name' => 'Packing & Unpacking Service',
                'price' => 50000,
                'duration' => '3-4 hours',
                'description' => 'Professional packing of household items with quality materials. Includes unpacking and organizing at new location.',
                'status' => 'active'
            ],
            [
                'name' => 'Furniture Disassembly & Reassembly',
                'price' => 35000,
                'duration' => '2-3 hours',
                'description' => 'Careful disassembly of furniture for moving and expert reassembly at destination. Prevents damage during transport.',
                'status' => 'active'
            ],
            [
                'name' => 'Heavy Item Moving',
                'price' => 40000,
                'duration' => '1-2 hours',
                'description' => 'Specialized moving of heavy items like refrigerators, washing machines, safes, and pianos. Professional equipment used.',
                'status' => 'active'
            ],
            [
                'name' => 'Office Relocation',
                'price' => 200000,
                'duration' => '1-2 days',
                'description' => 'Complete office moving service including furniture, equipment, files, and IT setup. Minimal business disruption.',
                'status' => 'active'
            ],
            [
                'name' => 'Apartment/Room Rearranging',
                'price' => 25000,
                'duration' => '2-3 hours',
                'description' => 'Rearrangement of furniture and items within your home or office. Perfect for redecorating or space optimization.',
                'status' => 'active'
            ],
            [
                'name' => 'Pickup & Delivery Service',
                'price' => 30000,
                'duration' => '1-2 hours',
                'description' => 'Item pickup and delivery anywhere in Kigali. Ideal for furniture purchases, appliances, or single-item transport.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Moving task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Cleaning task and subtasks
     */
    private function seedCleaningTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/cleaning.jpg',
            'title' => 'Professional Cleaning Services',
            'status' => 'active',
            'tags' => ['cleaning', 'deep clean', 'sanitizing', 'housekeeping', 'hygiene']
        ]);

        $subtasks = [
            [
                'name' => 'Deep Home Cleaning',
                'price' => 45000,
                'duration' => '3-4 hours',
                'description' => 'Comprehensive deep cleaning of entire home including scrubbing, sanitizing, and detailed cleaning of all rooms and surfaces.',
                'status' => 'active'
            ],
            [
                'name' => 'Carpet & Upholstery Steam Cleaning',
                'price' => 35000,
                'duration' => '2-3 hours',
                'description' => 'Professional steam cleaning of carpets, rugs, sofas, and upholstered furniture. Removes stains, odors, and allergens.',
                'status' => 'active'
            ],
            [
                'name' => 'Kitchen Deep Cleaning',
                'price' => 30000,
                'duration' => '2 hours',
                'description' => 'Thorough kitchen cleaning including appliances, cabinets, countertops, tiles, and grease removal.',
                'status' => 'active'
            ],
            [
                'name' => 'Bathroom Deep Cleaning',
                'price' => 25000,
                'duration' => '1.5 hours',
                'description' => 'Complete bathroom sanitization including tiles, grout, fixtures, toilet, shower, and mold removal.',
                'status' => 'active'
            ],
            [
                'name' => 'Window & Glass Cleaning',
                'price' => 20000,
                'duration' => '1-2 hours',
                'description' => 'Interior and exterior window cleaning, glass doors, mirrors, and glass surfaces. Streak-free finish guaranteed.',
                'status' => 'active'
            ],
            [
                'name' => 'Post-Construction Cleaning',
                'price' => 80000,
                'duration' => '4-6 hours',
                'description' => 'Specialized cleaning after renovation or construction. Removal of dust, debris, paint stains, and final polishing.',
                'status' => 'active'
            ],
            [
                'name' => 'Floor Polishing & Scrubbing',
                'price' => 40000,
                'duration' => '2-3 hours',
                'description' => 'Professional floor cleaning, scrubbing, and polishing for tiles, marble, hardwood, and laminate floors.',
                'status' => 'active'
            ],
            [
                'name' => 'Pressure Washing',
                'price' => 50000,
                'duration' => '2-3 hours',
                'description' => 'High-pressure washing of exterior walls, driveways, patios, walkways, and outdoor surfaces.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Cleaning task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Outdoor Help task and subtasks
     */
    private function seedOutdoorHelpTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/outdoor.jpg',
            'title' => 'Outdoor & Garden Services',
            'status' => 'active',
            'tags' => ['outdoor', 'gardening', 'landscaping', 'lawn care', 'yard work']
        ]);

        $subtasks = [
            [
                'name' => 'Lawn Mowing & Maintenance',
                'price' => 25000,
                'duration' => '1-2 hours',
                'description' => 'Regular lawn mowing, edging, and basic maintenance. Keeps your lawn neat and healthy.',
                'status' => 'active'
            ],
            [
                'name' => 'Garden Cleanup & Waste Removal',
                'price' => 30000,
                'duration' => '2-3 hours',
                'description' => 'Comprehensive garden cleanup including leaf removal, debris clearing, weeding, and waste disposal.',
                'status' => 'active'
            ],
            [
                'name' => 'Hedge & Tree Trimming',
                'price' => 35000,
                'duration' => '2-3 hours',
                'description' => 'Professional trimming and pruning of hedges, shrubs, and small trees. Shaping and maintenance included.',
                'status' => 'active'
            ],
            [
                'name' => 'Landscaping & Garden Design',
                'price' => 100000,
                'duration' => '1-2 days',
                'description' => 'Complete landscaping service including design, planting, mulching, and garden bed creation.',
                'status' => 'active'
            ],
            [
                'name' => 'Irrigation System Installation',
                'price' => 80000,
                'duration' => '4-6 hours',
                'description' => 'Installation of sprinkler systems, drip irrigation, and automated watering solutions for gardens and lawns.',
                'status' => 'active'
            ],
            [
                'name' => 'Fence Installation & Repair',
                'price' => 120000,
                'duration' => '1-2 days',
                'description' => 'Installation and repair of wooden, chain-link, and decorative fences. Includes posts, gates, and painting.',
                'status' => 'active'
            ],
            [
                'name' => 'Outdoor Lighting Installation',
                'price' => 45000,
                'duration' => '2-3 hours',
                'description' => 'Installation of garden lights, pathway lighting, security lights, and decorative outdoor illumination.',
                'status' => 'active'
            ],
            [
                'name' => 'Deck & Patio Cleaning',
                'price' => 28000,
                'duration' => '1-2 hours',
                'description' => 'Power washing and cleaning of wooden decks, concrete patios, and outdoor living spaces.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Outdoor Help task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Home Repairs task and subtasks
     */
    private function seedHomeRepairsTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/repairs.jpg',
            'title' => 'General Home Repairs & Handyman Services',
            'status' => 'active',
            'tags' => ['repairs', 'handyman', 'maintenance', 'fixing', 'installation']
        ]);

        $subtasks = [
            [
                'name' => 'Door & Lock Repairs',
                'price' => 20000,
                'duration' => '1 hour',
                'description' => 'Repair and installation of doors, locks, handles, hinges, and door frames. Includes alignment and adjustment.',
                'status' => 'active'
            ],
            [
                'name' => 'Plumbing Repairs',
                'price' => 30000,
                'duration' => '1-2 hours',
                'description' => 'Leak repairs, faucet replacement, pipe fixing, toilet repairs, and general plumbing maintenance.',
                'status' => 'active'
            ],
            [
                'name' => 'Electrical Repairs & Installation',
                'price' => 35000,
                'duration' => '1-2 hours',
                'description' => 'Switch and socket repairs, light fixture installation, circuit troubleshooting, and electrical safety checks.',
                'status' => 'active'
            ],
            [
                'name' => 'Drywall & Ceiling Repair',
                'price' => 25000,
                'duration' => '1-2 hours',
                'description' => 'Repair of holes, cracks, water damage in walls and ceilings. Includes patching, sanding, and touch-up painting.',
                'status' => 'active'
            ],
            [
                'name' => 'Tile & Grout Repair',
                'price' => 28000,
                'duration' => '2 hours',
                'description' => 'Replacement of broken tiles, grout repair, re-sealing, and tile cleaning in bathrooms and kitchens.',
                'status' => 'active'
            ],
            [
                'name' => 'Window & Screen Repair',
                'price' => 22000,
                'duration' => '1 hour',
                'description' => 'Repair of broken windows, screen replacement, frame fixing, and window mechanism repair.',
                'status' => 'active'
            ],
            [
                'name' => 'Caulking & Sealing',
                'price' => 15000,
                'duration' => '1 hour',
                'description' => 'Caulking around bathtubs, sinks, windows, and doors. Prevents water damage and improves insulation.',
                'status' => 'active'
            ],
            [
                'name' => 'Appliance Installation',
                'price' => 32000,
                'duration' => '1-2 hours',
                'description' => 'Installation of washing machines, dryers, dishwashers, water heaters, and other household appliances.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Home Repairs task seeded with ' . count($subtasks) . ' subtasks');
    }

    /**
     * Seed Painting task and subtasks
     */
    private function seedPaintingTask(): void
    {
        $task = Task::create([
            'image' => 'tasks/painting.jpg',
            'title' => 'Professional Painting Services',
            'status' => 'active',
            'tags' => ['painting', 'interior', 'exterior', 'decoration', 'renovation']
        ]);

        $subtasks = [
            [
                'name' => 'Interior Wall Painting',
                'price' => 60000,
                'duration' => '1-2 days',
                'description' => 'Complete interior wall painting including preparation, priming, two coats of paint, and clean-up. Professional finish guaranteed.',
                'status' => 'active'
            ],
            [
                'name' => 'Exterior House Painting',
                'price' => 150000,
                'duration' => '3-5 days',
                'description' => 'Full exterior painting including surface preparation, weather-resistant paint application, and protective coating.',
                'status' => 'active'
            ],
            [
                'name' => 'Room Paint Touch-ups',
                'price' => 18000,
                'duration' => '2-3 hours',
                'description' => 'Quick touch-up painting for small areas, scuff marks, and minor wall blemishes. Perfect for refreshing rooms.',
                'status' => 'active'
            ],
            [
                'name' => 'Ceiling Painting',
                'price' => 35000,
                'duration' => '4-6 hours',
                'description' => 'Professional ceiling painting including stain coverage, smooth finish, and no drips or marks.',
                'status' => 'active'
            ],
            [
                'name' => 'Door & Window Frame Painting',
                'price' => 12000,
                'duration' => '2-3 hours',
                'description' => 'Detailed painting of door frames, window frames, and trim work. Includes sanding and priming.',
                'status' => 'active'
            ],
            [
                'name' => 'Cabinet & Furniture Painting',
                'price' => 40000,
                'duration' => '1-2 days',
                'description' => 'Professional refinishing and painting of kitchen cabinets, wardrobes, and furniture. Durable finish.',
                'status' => 'active'
            ],
            [
                'name' => 'Wallpaper Installation & Removal',
                'price' => 45000,
                'duration' => '4-6 hours',
                'description' => 'Expert wallpaper installation with perfect alignment, or complete wallpaper removal and surface preparation.',
                'status' => 'active'
            ],
            [
                'name' => 'Decorative & Accent Wall Painting',
                'price' => 50000,
                'duration' => '1 day',
                'description' => 'Creative accent walls, patterns, stripes, or decorative finishes. Includes design consultation.',
                'status' => 'active'
            ]
        ];

        foreach ($subtasks as $subtask) {
            SubTask::create(array_merge($subtask, ['task_id' => $task->id]));
        }

        $this->command->info('✓ Painting task seeded with ' . count($subtasks) . ' subtasks');
    }
}
