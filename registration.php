<?php
/**
 * Magenizr Faker
 *
 * @category    Magenizr
 * @copyright   Copyright (c) 2021 Magenizr (http://www.magenizr.com)
 * @license     https://www.magenizr.com/license Magenizr EULA
 */

\Magento\Framework\Component\ComponentRegistrar::register(
    \Magento\Framework\Component\ComponentRegistrar::MODULE,
    'Magenizr_Faker',
    isset($file) ? dirname($file) : __DIR__ // phpcs:ignore Magento2.Functions.DiscouragedFunction
);
