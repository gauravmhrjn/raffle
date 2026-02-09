<?php

namespace Database\Factories;

use Illuminate\Support\Collection;

trait DisableCallbacks
{
    /**
     * set callbacks to empty
     *
     * @return $this
     */
    public function withoutCallbacks()
    {
        $this->withoutAfterCreating();
        $this->withoutAfterMaking();

        return $this;
    }

    /**
     * set afterCreating to empty
     *
     * @return $this
     */
    public function withoutAfterCreating()
    {
        $this->afterCreating = new Collection;

        return $this;
    }

    /**
     * set afterMaking to empty
     *
     * @return $this
     */
    public function withoutAfterMaking()
    {
        $this->afterMaking = new Collection;

        return $this;
    }
}
