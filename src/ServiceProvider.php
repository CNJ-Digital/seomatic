<?php

namespace Cnj\Seotamic;

use Statamic\Facades\Addon;
use Statamic\Facades\CP\Nav;
use Statamic\Facades\GraphQL;
use Statamic\Facades\Permission;
use Illuminate\Support\Facades\Event;
use Statamic\GraphQL\Types\EntryInterface;
use Statamic\Providers\AddonServiceProvider;

use Cnj\Seotamic\Commands\MigrateCommand;
use Cnj\Seotamic\GraphQL\SeotamicMetaType;
use Cnj\Seotamic\GraphQL\SeotamicMetaField;
use Cnj\Seotamic\GraphQL\SeotamicSocialType;
use Cnj\Seotamic\GraphQL\SeotamicSocialField;

class ServiceProvider extends AddonServiceProvider
{
    protected $routes = [
        'cp' => __DIR__ . '/routes/cp.php',
        'web' => __DIR__ . '/routes/web.php',
    ];

    protected $fieldtypes = [
        FieldTypes\SeotamicMeta::class,
        FieldTypes\SeotamicSocial::class,
        FieldTypes\SeotamicSearchPreview::class,
        FieldTypes\SeotamicSocialPreview::class,
    ];

    protected $tags = [
        \Cnj\Seotamic\Tags\SeotamicTags::class,
    ];

    protected $scripts = [
        __DIR__ . '/../resources/dist/js/cp.js',
    ];

    protected $stylesheets = [
        __DIR__ . '/../resources/dist/css/cp.css',
    ];

    public function boot()
    {
        parent::boot();

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'seotamic');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'seotamic');

        Nav::extend(function ($nav) {
            $nav->content('SEOtamic')
                ->section('Tools')
                ->route('cnj.seotamic.index')
                ->can('view seotamic tool')
                ->icon('seo-search-graph');
        });

        $this->publishes([
            __DIR__ . '/../config/seotamic.php' => config_path('seotamic.php')
        ], 'config');

        Permission::register('view seotamic tool')
            ->label('View global SEOtamic settings');

        Event::subscribe(Subscriber::class);

        $addon = Addon::get('cnj/seotamic');
        $edition = $addon ? $addon->edition() : 'lite';

        // GraphQL support for Pro edition
        if (config('statamic.graphql.enabled') && $edition === 'pro') {
            GraphQL::addType(SeotamicMetaType::class);
            GraphQL::addType(SeotamicSocialType::class);

            GraphQL::addField(EntryInterface::NAME, 'seotamic_meta', fn () => (new SeotamicMetaField())->toArray());
            GraphQL::addField(EntryInterface::NAME, 'seotamic_social', fn () => (new SeotamicSocialField())->toArray());
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                MigrateCommand::class,
            ]);
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/seotamic.php', 'seotamic');
    }
}
