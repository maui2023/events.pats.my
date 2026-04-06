<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call(CountrySeeder::class);
        $this->call(StateSeeder::class);

        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ]
        );

        $organizerId = \App\Models\User::where('email', 'admin@example.com')->value('id');
        $organizer = \App\Models\User::find($organizerId);

        if ($organizer) {
            Profile::query()->updateOrCreate(
                ['user_id' => $organizer->id],
                ['tier' => 'PRO']
            );
        }

        $org = null;
        if ($organizer) {
            $org = Organization::query()->updateOrCreate(
                ['name' => 'BeSpoke Community'],
                [
                    'status' => 'approved',
                    'is_public' => true,
                    'created_by' => $organizer->id,
                    'approved_at' => Carbon::now(),
                ]
            );
            $org->users()->syncWithoutDetaching([
                $organizer->id => ['role' => 'owner', 'status' => 'approved'],
            ]);
        }

        \App\Models\Event::query()->updateOrCreate(
            ['slug' => 'bespoke-tech-meetup'],
            [
                'title' => 'BeSpoke Tech Meetup',
                'description' => 'Monthly meetup for developers and tech enthusiasts.',
                'start_at' => now()->addDays(10)->setTime(19, 0),
                'end_at' => now()->addDays(10)->setTime(22, 0),
                'location' => 'BeSpoke HQ, Kuala Lumpur',
                'banner_path' => 'https://images.unsplash.com/photo-1469474968028-56623f02e42e?q=80&w=1200&auto=format&fit=crop',
                'is_published' => true,
                'organizer_id' => $organizerId,
                'organization_id' => $org?->id,
            ]
        );

        \App\Models\Event::query()->updateOrCreate(
            ['slug' => 'product-design-workshop'],
            [
                'title' => 'Product Design Workshop',
                'description' => 'Hands-on workshop on modern product design.',
                'start_at' => now()->addDays(25)->setTime(10, 0),
                'end_at' => now()->addDays(25)->setTime(16, 0),
                'location' => 'Penang Science Park',
                'banner_path' => 'https://images.unsplash.com/photo-1508264165352-258a6ee0a73b?q=80&w=1200&auto=format&fit=crop',
                'is_published' => true,
                'organizer_id' => $organizerId,
                'organization_id' => $org?->id,
            ]
        );
    }
}
