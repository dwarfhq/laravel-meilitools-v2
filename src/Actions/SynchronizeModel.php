<?php

declare(strict_types=1);

namespace Dwarf\MeiliTools\Actions;

use Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex;
use Dwarf\MeiliTools\Contracts\Actions\SynchronizesModel;

/**
 * Synchronize model index.
 */
class SynchronizeModel implements SynchronizesModel
{
    /**
     * Synchronizes index action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex
     */
    private SynchronizesIndex $synchronizeIndex;

    /**
     * Ensures index exists action.
     *
     * @var \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     */
    private EnsuresIndexExists $ensureIndexExists;

    /**
     * Constructor.
     *
     * @param \Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex  $synchronizeIndex  Synchronize action.
     * @param \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists $ensureIndexExists Action ensuring index exists.
     */
    public function __construct(SynchronizesIndex $synchronizeIndex, EnsuresIndexExists $ensureIndexExists)
    {
        $this->synchronizeIndex = $synchronizeIndex;
        $this->ensureIndexExists = $ensureIndexExists;
    }

    /**
     * {@inheritDoc}
     *
     * @param bool $pretending Whether to pretend running the action.
     *
     * @uses \Dwarf\MeiliTools\Contracts\Actions\SynchronizesIndex
     * @uses \Dwarf\MeiliTools\Contracts\Actions\EnsuresIndexExists
     *
     * @throws \Illuminate\Validation\ValidationException       On validation failure.
     * @throws \Dwarf\MeiliTools\Exceptions\MeiliToolsException When not using the MeiliSearch Scout driver.
     * @throws \MeiliSearch\Exceptions\CommunicationException   When connection to MeiliSearch fails.
     */
    public function __invoke(string $class, bool $pretending = false): array
    {
        $model = app($class);
        $index = $model->searchableAs();
        $primaryKey = $model->getKeyName();
        $settings = $model->meiliSettings();

        ($this->ensureIndexExists)($index, compact('primaryKey'));

        return ($this->synchronizeIndex)($index, $settings, $pretending);
    }
}
