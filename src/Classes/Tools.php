<?php

namespace Adilis\HiddenObjects\Classes;

use ShopCore;

class HOTools
{

    public static function isInMaintenance($prefix): bool
    {
        $maintenance_ips = \Configuration::get($prefix . 'HIDDENOBJECTS_IPS');
        $is_in_maintenance = false;
        if (
            \Configuration::get($prefix . 'HIDDENOBJECTS_TEST')
            && (
                (!empty($maintenance_ips) && in_array(\Tools::getRemoteAddr(), explode(',', $maintenance_ips)))
                || empty($maintenance_ips)
            )
        ) {
            $is_in_maintenance = true;
        }
        return $is_in_maintenance;
    }

    public static function formatFileSize($bytes): string
    {
        if ($bytes >= 1000000000) {
            return \Tools::ps_round($bytes / 1000000000, 2) . ' GB';
        }

        if ($bytes >= 1000000) {
            return \Tools::ps_round($bytes / 1000000, 2) . ' MB';
        }

        return \Tools::ps_round($bytes / 1000, 2) . ' KB';
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
          return !(\Shop::isFeatureActive() && \Shop::getContext() != ShopCore::CONTEXT_SHOP);
    }
}