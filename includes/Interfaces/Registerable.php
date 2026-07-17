<?php

namespace DionnieWPToolkitWP\Core\Interfaces;

interface Registerable
{
    /**
     * Register actions and filters associated with the feature module.
     */
    public function register(): void;
}
