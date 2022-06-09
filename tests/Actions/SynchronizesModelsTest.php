<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Tests\Actions;

use BadMethodCallException;
use Dwarf\MeiliTools\Contracts\Actions\DetailsModel;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModels;
use Dwarf\MeiliTools\Helpers;
use Dwarf\MeiliTools\Tests\Models\MeiliMovie;
use Dwarf\MeiliTools\Tests\Models\Movie;
use Dwarf\MeiliTools\Tests\TestCase;

/**
 * @internal
 */
class SynchronizesModelsTest extends TestCase
{
    /**
     * Test SynchronizesModels::__invoke() method with advanced settings.
     *
     * @return void
     */
    public function testWithAdvancedSettings(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = MeiliMovie::meiliSettings();
            $expected = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;
            $exception = new BadMethodCallException('Call to undefined method ' . Movie::class . '::meiliSettings()');

            $classes = [
                Movie::class      => $exception,
                MeiliMovie::class => $expected,
            ];

            $details = $this->app->make(DetailsModel::class)(Movie::class);
            $this->assertSame($defaults, $details);
            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $action = $this->app->make(SynchronizesModels::class);
            $action(array_keys($classes), function ($class, $result) use ($classes) {
                if (\is_array($result)) {
                    $this->assertSame($classes[$class], $result);
                } else {
                    $this->assertTrue($classes[$class] instanceof $result);
                    $this->assertSame($classes[$class]->getMessage(), $result->getMessage());
                }
            });

            $details = $this->app->make(DetailsModel::class)(Movie::class);
            $this->assertSame($defaults, $details);
            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($settings, $details);
        } finally {
            $this->deleteIndex((new Movie())->searchableAs());
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }

    /**
     * Test SynchronizesModels::__invoke() method with dry-run option.
     *
     * @return void
     */
    public function testWithDryRun(): void
    {
        try {
            $defaults = Helpers::defaultSettings($this->engineVersion());
            $settings = MeiliMovie::meiliSettings();
            $expected = collect($settings)
                ->mapWithKeys(function ($value, $key) use ($defaults) {
                    $old = $defaults[$key];
                    $new = $value;

                    return [$key => $old === $new ? false : compact('old', 'new')];
                })
                ->filter()
                ->all()
            ;

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);

            $action = $this->app->make(SynchronizesModels::class);
            $action([MeiliMovie::class], function ($class, $result) use ($expected) {
                $this->assertSame($expected, $result);
            }, true);

            $details = $this->app->make(DetailsModel::class)(MeiliMovie::class);
            $this->assertSame($defaults, $details);
        } finally {
            $this->deleteIndex((new MeiliMovie())->searchableAs());
        }
    }
}
