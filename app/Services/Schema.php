<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;

/**
 * Builds schema.org JSON-LD arrays from database rows, for Seo::addJsonLd().
 * Keys that would be empty are dropped so Google never sees blank fields.
 */
final class Schema
{
    private const CONTEXT = 'https://schema.org';

    /** @return array<string,mixed> */
    public static function organization(): array
    {
        return self::clean([
            '@context' => self::CONTEXT,
            '@type' => 'Organization',
            'name' => Config::get('app.name'),
            'url' => url('/'),
            'logo' => url('/assets/img/logo-tbr.svg'),
            'email' => Config::get('site.email'),
            'telephone' => Config::get('site.phone'),
            'parentOrganization' => ['@type' => 'Organization', 'name' => Config::get('site.company')],
        ]);
    }

    /** @return array<string,mixed> */
    public static function website(): array
    {
        return [
            '@context' => self::CONTEXT,
            '@type' => 'WebSite',
            'name' => Config::get('app.name'),
            'url' => url('/'),
            'inLanguage' => Config::get('app.locale'),
        ];
    }

    /**
     * @param list<array{0:string,1:string}> $trail [label, path] pairs, home first
     * @return array<string,mixed>
     */
    public static function breadcrumbs(array $trail): array
    {
        $items = [];

        foreach (array_values($trail) as $i => [$label, $path]) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $label,
                'item' => url($path),
            ];
        }

        return ['@context' => self::CONTEXT, '@type' => 'BreadcrumbList', 'itemListElement' => $items];
    }

    /** @param array<string,mixed> $event a pitstop_events row @return array<string,mixed> */
    public static function event(array $event): array
    {
        $states = Config::get('site.states', []);

        return self::clean([
            '@context' => self::CONTEXT,
            '@type' => 'Event',
            'name' => $event['title'],
            'description' => $event['description'] ?? null,
            'url' => url('/pit-stop/' . $event['slug']),
            'image' => self::image($event['banner_image'] ?? null),
            'startDate' => self::isoDate($event['starts_at'] ?? null),
            'endDate' => self::isoDate($event['ends_at'] ?? null),
            'eventStatus' => 'https://schema.org/EventScheduled',
            'eventAttendanceMode' => 'https://schema.org/OfflineEventAttendanceMode',
            'location' => self::clean([
                '@type' => 'Place',
                'name' => $event['location_name'],
                'address' => self::clean([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $event['address'] ?? null,
                    'addressRegion' => $states[$event['state']] ?? $event['state'],
                    'addressCountry' => 'MY',
                ]),
            ]),
            'organizer' => ['@type' => 'Organization', 'name' => Config::get('app.name'), 'url' => url('/')],
        ]);
    }

    /** @param array<string,mixed> $place a port_riders row @return array<string,mixed> */
    public static function localBusiness(array $place): array
    {
        $states = Config::get('site.states', []);
        $type = match ($place['category'] ?? 'other') {
            'bike_shop' => 'MotorcycleDealer',
            'food' => 'Restaurant',
            'fuel' => 'GasStation',
            default => 'LocalBusiness',
        };

        $geo = null;
        if (!empty($place['latitude']) && !empty($place['longitude'])) {
            $geo = ['@type' => 'GeoCoordinates', 'latitude' => $place['latitude'], 'longitude' => $place['longitude']];
        }

        return self::clean([
            '@context' => self::CONTEXT,
            '@type' => $type,
            // The design lists businesses on one page with no detail view, so
            // each listing is identified by its anchor on the directory page.
            '@id' => url('/port-rider#' . $place['slug']),
            'name' => $place['name'],
            'image' => self::image($place['image'] ?? null),
            'telephone' => $place['phone'] ?? null,
            'hasMap' => $place['maps_url'] ?? null,
            'geo' => $geo,
            'address' => self::clean([
                '@type' => 'PostalAddress',
                'streetAddress' => $place['address'] ?? null,
                'addressLocality' => $place['city'] ?? null,
                'postalCode' => $place['postcode'] ?? null,
                'addressRegion' => $states[$place['state']] ?? $place['state'],
                'addressCountry' => 'MY',
            ]),
        ]);
    }

    /** @param array<string,mixed> $video a videos row @return array<string,mixed> */
    public static function video(array $video): array
    {
        $embedUrl = match ($video['provider'] ?? null) {
            'youtube' => empty($video['video_id']) ? null : 'https://www.youtube.com/embed/' . $video['video_id'],
            default => $video['video_url'] ?? null,
        };

        return self::clean([
            '@context' => self::CONTEXT,
            '@type' => 'VideoObject',
            'name' => $video['title'],
            'description' => $video['description'] ?? $video['title'],
            'thumbnailUrl' => self::image($video['thumbnail'] ?? null),
            'uploadDate' => self::isoDate($video['published_at'] ?? null),
            'duration' => empty($video['duration_seconds']) ? null : 'PT' . (int) $video['duration_seconds'] . 'S',
            'embedUrl' => $embedUrl,
        ]);
    }

    /** @param array<string,mixed> $profile a hof_profiles row @return array<string,mixed> */
    public static function person(array $profile): array
    {
        $sameAs = array_values(array_filter([
            $profile['facebook_url'] ?? null,
            $profile['instagram_url'] ?? null,
            $profile['tiktok_url'] ?? null,
        ]));

        return self::clean([
            '@context' => self::CONTEXT,
            '@type' => 'Person',
            'name' => $profile['name'],
            'alternateName' => $profile['headline'] ?? null,
            'description' => $profile['summary'] ?? null,
            'image' => self::image($profile['avatar'] ?? null),
            'url' => url('/hall-of-fame/' . $profile['slug']),
            'sameAs' => $sameAs === [] ? null : $sameAs,
        ]);
    }

    /** @param list<string> $paths listing page items, in display order @return array<string,mixed> */
    public static function itemList(array $paths): array
    {
        $items = [];

        foreach (array_values($paths) as $i => $path) {
            $items[] = ['@type' => 'ListItem', 'position' => $i + 1, 'url' => url($path)];
        }

        return ['@context' => self::CONTEXT, '@type' => 'ItemList', 'itemListElement' => $items];
    }

    private static function image(?string $upload): ?string
    {
        return $upload === null || $upload === '' ? null : url(uploaded($upload));
    }

    private static function isoDate(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $timestamp = strtotime($value);

        return $timestamp === false ? null : date('c', $timestamp);
    }

    /** @param array<string,mixed> $data @return array<string,mixed> */
    private static function clean(array $data): array
    {
        return array_filter($data, static fn (mixed $v): bool => $v !== null && $v !== '' && $v !== []);
    }
}
