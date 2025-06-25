<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\UserPostView;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(250)->create();
        $this->command->info('Creating posts foreach user...');

        foreach ($users as $user) {
            $postsCount = rand(50, 200);

            $trendPostsCount = (int)($postsCount * 0.2);
            $coldPostsCount = (int)($postsCount * 0.15);
            $overViewedCount = (int)($postsCount * 0.1);
            $normalPostsCount = $postsCount - $trendPostsCount - $coldPostsCount - $overViewedCount;

            if($trendPostsCount > 0) {
                Post::factory()
                    ->trend()
                    ->forUser($user->id)
                    ->count($trendPostsCount)
                    ->create();
            }

            if($coldPostsCount > 0) {
                Post::factory()
                    ->cold()
                    ->forUser($user->id)
                    ->count($coldPostsCount)
                    ->create();
            }

            if($overViewedCount > 0) {
                Post::factory()
                    ->overViewed()
                    ->forUser($user->id)
                    ->count($overViewedCount)
                    ->create();
            }

            if($normalPostsCount > 0) {
                Post::factory()
                    ->forUser($user->id)
                    ->count($normalPostsCount)
                    ->create();
            }

            $this->command->info("User {$user->name} has " . $user->posts()->count() . " posts");
        }

        $this->command->info('Creating views...');

        $allPosts = Post::limit(500)->get();

        foreach ($users->take(80) as $viewer) {
            $viewedPosts = $allPosts
                ->where('user_id', '!=', $viewer->id)
                ->random(rand(30, 100));

            foreach ($viewedPosts as $post) {
                UserPostView::create([
                    'user_id' => (string)$viewer->id,
                    'post_id' => $post->id,
                    'viewed_at' => now()->subDays(rand(1, 30)),
                ]);
            }

            $this->command->info("User {$viewer->name} viewed " . $viewer->viewedPosts()->count() . " posts");
        }

        $this->command->info('Database seeded successfully!');
        $this->command->info('Statistics:');
        $this->command->info('- Total users: ' . User::count());
        $this->command->info('- Total posts: ' . Post::count());
        $this->command->info('- Posts available for feed (view_count < 1000): ' . Post::where('view_count', '<', 1000)->count());
        $this->command->info('- Posts with high hotness (>70): ' . Post::where('hotness', '>', 70)->count());
        $this->command->info('- Total user views: ' . UserPostView::count());
    }
}
