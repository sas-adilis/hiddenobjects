<?php

namespace Adilis\HiddenObjects\Classes;

class HOTools
{
    public static function isInMaintenance($prefix): bool
    {
        return (bool) \Configuration::get($prefix . 'HIDDENOBJECTS_TEST');
    }

    public static function buildOrWhere($orWhere): string
    {
        if (!count($orWhere)) {
            return '';
        }

        return implode(' OR ', $orWhere);
    }

    public static function isShopContext(): bool
    {
        return !(\Shop::isFeatureActive() && \Shop::getContext() != \ShopCore::CONTEXT_SHOP);
    }
}
