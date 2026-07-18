<?php

namespace DionnieBoilerplatePlugin;

interface Registerable
{
    /**
     * Register actions and filters associated with the feature module.
     */
    public function register(): void;
}
