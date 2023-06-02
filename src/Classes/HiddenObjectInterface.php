<?php

/** @noinspection SpellCheckingInspection */
/** @noinspection PhpMissingFieldTypeInspection */

/**
 * 2016 Adilis
 *
 * Make your shop interactive for Easter: hide objects and ask your customers to find them in order to win a
 * discount coupon. Make your brand stand out by offering an original game: a treasure hunt throughout your products.
 *
 *  @author    Adilis <support@adilis.fr>
 *  @copyright 2016 SAS Adilis
 *  @license   http://www.adilis.fr
 */

namespace Adilis\HiddenObjects\Classes;

interface HiddenObjectInterface
{
    public function getPrefix(): string;

    public function getModuleName(): string;

    public function getTable(): string;
}
