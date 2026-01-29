<?php

namespace App\Services\WorkshopApi;

use App\Models\Workshop;
use InvalidArgumentException;

/**
 * Factory để tạo adapter phù hợp dựa trên api_type của workshop
 */
class WorkshopApiAdapterFactory
{
    /**
     * Mapping giữa api_type và adapter class
     * Thêm adapter mới vào đây khi có xưởng mới
     */
    protected static array $adapters = [
        'rest' => GenericRestAdapter::class,
        'generic_rest' => GenericRestAdapter::class,
        'custom' => CustomWorkshopAdapter::class,
        'twofifteen' => TwofifteenAdapter::class,
        // Thêm các adapter khác ở đây:
        // 'workshop_a' => WorkshopAAdapter::class,
        // 'workshop_b' => WorkshopBAdapter::class,
    ];

    /**
     * Tạo adapter dựa trên api_type của workshop
     *
     * @param Workshop $workshop
     * @return WorkshopApiAdapterInterface
     * @throws InvalidArgumentException
     */
    public static function create(Workshop $workshop): WorkshopApiAdapterInterface
    {
        $apiType = $workshop->api_type ?? 'rest';

        if (!isset(self::$adapters[$apiType])) {
            // Nếu không tìm thấy adapter cụ thể, sử dụng generic rest adapter
            $apiType = 'rest';
        }

        $adapterClass = self::$adapters[$apiType];

        if (!class_exists($adapterClass)) {
            throw new InvalidArgumentException(
                "Adapter class '{$adapterClass}' not found for API type '{$apiType}'"
            );
        }

        return new $adapterClass();
    }

    /**
     * Đăng ký adapter mới
     *
     * @param string $apiType
     * @param string $adapterClass
     * @return void
     */
    public static function register(string $apiType, string $adapterClass): void
    {
        if (!is_subclass_of($adapterClass, WorkshopApiAdapterInterface::class)) {
            throw new InvalidArgumentException(
                "Adapter class must implement WorkshopApiAdapterInterface"
            );
        }

        self::$adapters[$apiType] = $adapterClass;
    }

    /**
     * Lấy danh sách các adapter đã đăng ký
     *
     * @return array
     */
    public static function getRegisteredAdapters(): array
    {
        return self::$adapters;
    }
}
