<?php

namespace App\Console\Commands;

use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

#[Signature('sitemap:generate')]
#[Description('Generate the sitemap.')]
class GenerateSitemap extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $sitemap = Sitemap::create()
            ->add(Url::create(route('welcome.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(1.0))
            ->add(Url::create(route('brandbook.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)->setPriority(0.5))
            ->add(Url::create(route('contact.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)->setPriority(0.7))
            ->add(Url::create(route('terms.terms'))->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3))
            ->add(Url::create(route('terms.privacy'))->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3))
            ->add(Url::create(route('terms.cookie'))->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)->setPriority(0.3))
            ->add(Url::create(route('posts.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.8))
            ->add(Url::create(route('products.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)->setPriority(0.8))
            ->add(Url::create(route('projects.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)->setPriority(0.8))
            ->add(Url::create(route('pages.index'))->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)->setPriority(0.8));

        Page::isPublicPublished()->each(function (Page $page) use ($sitemap): void {
            $sitemap->add(
                Url::create(route('pages.show', $page))
                    ->setLastModificationDate($page->updated_at ?? $page->published_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.8),
            );
        });

        Post::isPublicPublished()->each(function (Post $post) use ($sitemap): void {
            $sitemap->add(
                Url::create(route('posts.show', $post))
                    ->setLastModificationDate($post->updated_at ?? $post->published_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    ->setPriority(0.7),
            );
        });

        Product::isPublicPublished()->each(function (Product $product) use ($sitemap): void {
            $sitemap->add(
                Url::create(route('products.show', $product))
                    ->setLastModificationDate($product->updated_at ?? $product->published_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                    ->setPriority(0.9),
            );
        });

        Project::isPublicPublished()->each(function (Project $project) use ($sitemap): void {
            $sitemap->add(
                Url::create(route('projects.show', $project))
                    ->setLastModificationDate($project->updated_at ?? $project->published_at ?? now())
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                    ->setPriority(0.7),
            );
        });

        $sitemap->writeToDisk('public', 'sitemap.xml', true);

        $this->info('Sitemap generated!');
    }
}
