<?php

namespace App\Services;

class LocationResolver
{
    /** @var array<int, array{key: string, name: string, lat: float, lng: float}> */
    private const CITIES = [
        ['key' => 'new-york', 'name' => 'New York, US', 'lat' => 40.7128, 'lng' => -74.0060],
        ['key' => 'los-angeles', 'name' => 'Los Angeles, US', 'lat' => 34.0522, 'lng' => -118.2437],
        ['key' => 'chicago', 'name' => 'Chicago, US', 'lat' => 41.8781, 'lng' => -87.6298],
        ['key' => 'london', 'name' => 'London, UK', 'lat' => 51.5074, 'lng' => -0.1278],
        ['key' => 'paris', 'name' => 'Paris, FR', 'lat' => 48.8566, 'lng' => 2.3522],
        ['key' => 'berlin', 'name' => 'Berlin, DE', 'lat' => 52.5200, 'lng' => 13.4050],
        ['key' => 'madrid', 'name' => 'Madrid, ES', 'lat' => 40.4168, 'lng' => -3.7038],
        ['key' => 'rome', 'name' => 'Rome, IT', 'lat' => 41.9028, 'lng' => 12.4964],
        ['key' => 'amsterdam', 'name' => 'Amsterdam, NL', 'lat' => 52.3676, 'lng' => 4.9041],
        ['key' => 'barcelona', 'name' => 'Barcelona, ES', 'lat' => 41.3851, 'lng' => 2.1734],
        ['key' => 'toronto', 'name' => 'Toronto, CA', 'lat' => 43.6532, 'lng' => -79.3832],
        ['key' => 'mexico-city', 'name' => 'Mexico City, MX', 'lat' => 19.4326, 'lng' => -99.1332],
        ['key' => 'tokyo', 'name' => 'Tokyo, JP', 'lat' => 35.6762, 'lng' => 139.6503],
        ['key' => 'sydney', 'name' => 'Sydney, AU', 'lat' => -33.8688, 'lng' => 151.2093],
        ['key' => 'dubai', 'name' => 'Dubai, AE', 'lat' => 25.2048, 'lng' => 55.2708],
    ];

    private const BOX_DELTA = 0.45;

    /** @return list<array{key: string, name: string}> */
    public static function cities(): array
    {
        return array_map(
            fn (array $city) => ['key' => $city['key'], 'name' => $city['name']],
            self::CITIES,
        );
    }

    public static function findCity(?string $key): ?array
    {
        if ($key === null || $key === '') {
            return null;
        }

        foreach (self::CITIES as $city) {
            if ($city['key'] === $key) {
                return $city;
            }
        }

        return null;
    }

    /**
     * @return array{label: string, city: string, lat: float|null, lng: float|null}
     */
    public static function resolve(?float $latitude, ?float $longitude, ?string $venue = null): array
    {
        $city = self::nearestCity($latitude, $longitude);
        $cityName = $city['name'] ?? 'Unknown location';
        $venue = $venue ?: 'Venue TBA';
        $label = str_contains($venue, $cityName) ? $venue : "{$venue}, {$cityName}";

        return [
            'label' => $label,
            'city' => $cityName,
            'lat' => $latitude,
            'lng' => $longitude,
        ];
    }

    /** @return array{key: string, name: string, lat: float, lng: float}|null */
    private static function nearestCity(?float $latitude, ?float $longitude): ?array
    {
        if ($latitude === null || $longitude === null) {
            return null;
        }

        $nearest = null;
        $bestDistance = PHP_FLOAT_MAX;

        foreach (self::CITIES as $city) {
            $distance = self::distance($latitude, $longitude, $city['lat'], $city['lng']);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $nearest = $city;
            }
        }

        return $nearest;
    }

    public static function applyCityFilter($query, string $cityKey): void
    {
        $city = self::findCity($cityKey);
        if ($city === null) {
            return;
        }

        $query->whereBetween('latitude', [$city['lat'] - self::BOX_DELTA, $city['lat'] + self::BOX_DELTA])
            ->whereBetween('longitude', [$city['lng'] - self::BOX_DELTA, $city['lng'] + self::BOX_DELTA]);
    }

    private static function distance(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        return (($lat1 - $lat2) ** 2) + (($lng1 - $lng2) ** 2);
    }
}
