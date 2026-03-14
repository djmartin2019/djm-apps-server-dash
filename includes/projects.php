<?php

if (!function_exists('normalize_project_image_url')) {
    function normalize_project_image_url(string $candidate, string $baseUrl): string
    {
        if ($candidate === '') {
            return '';
        }

        if (preg_match('/^https?:\/\//i', $candidate)) {
            return $candidate;
        }

        $parsed = parse_url($baseUrl);
        if (!is_array($parsed) || empty($parsed['scheme']) || empty($parsed['host'])) {
            return $candidate;
        }

        $origin = $parsed['scheme'] . '://' . $parsed['host'];
        if (isset($parsed['port'])) {
            $origin .= ':' . $parsed['port'];
        }

        if (str_starts_with($candidate, '/')) {
            return $origin . $candidate;
        }

        $path = $parsed['path'] ?? '/';
        $dir = rtrim(str_replace('\\', '/', dirname($path)), '/');
        if ($dir === '.') {
            $dir = '';
        }

        return $origin . ($dir ? $dir . '/' : '/') . ltrim($candidate, '/');
    }
}

if (!function_exists('resolve_project_preview_image')) {
    function resolve_project_preview_image(string $projectUrl, string $fallbackPath = '/assets/djm-apps.png'): string
    {
        static $cache = [];
        if (isset($cache[$projectUrl])) {
            return $cache[$projectUrl];
        }

        $html = '';

        if (function_exists('curl_init')) {
            $ch = @curl_init();
            if ($ch !== false) {
                curl_setopt_array($ch, [
                    CURLOPT_URL => $projectUrl,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 3,
                    CURLOPT_CONNECTTIMEOUT => 2,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS => 3,
                    CURLOPT_USERAGENT => 'DJMAppsDashboard/1.0',
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                ]);
                $response = @curl_exec($ch);
                if (is_string($response)) {
                    $html = $response;
                }
                @curl_close($ch);
            }
        } else {
            $context = stream_context_create([
                'http' => [
                    'timeout' => 3,
                    'follow_location' => 1,
                    'user_agent' => 'DJMAppsDashboard/1.0',
                ],
            ]);
            $response = @file_get_contents($projectUrl, false, $context);
            if (is_string($response)) {
                $html = $response;
            }
        }

        if ($html !== '') {
            $patterns = [
                '/<meta[^>]+property=["\']og:image["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+property=["\']og:image["\']/i',
                '/<meta[^>]+name=["\']twitter:image["\'][^>]+content=["\']([^"\']+)["\']/i',
                '/<meta[^>]+content=["\']([^"\']+)["\'][^>]+name=["\']twitter:image["\']/i',
            ];

            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $html, $matches) === 1 && !empty($matches[1])) {
                    $resolved = normalize_project_image_url(trim($matches[1]), $projectUrl);
                    if ($resolved !== '') {
                        $cache[$projectUrl] = $resolved;
                        return $resolved;
                    }
                }
            }
        }

        $cache[$projectUrl] = $fallbackPath;
        return $cache[$projectUrl];
    }
}

if (!function_exists('get_deployed_projects')) {
    function get_deployed_projects(): array
    {
        $projects = [
            [
                'name' => 'PokeVote - Vote for Your Favorite Pokemon',
                'description' => 'Choose between two Pokemon, cast your vote, and help shape live rankings using PokeAPI data.',
                'url' => 'https://pokevote.djm-apps.com',
            ],
            [
                'name' => 'Davey Maps',
                'description' => 'Explore maps and locations with Davey Maps.',
                'url' => 'https://davey-maps.djm-apps.com',
            ],
        ];

        foreach ($projects as &$project) {
            $project['image'] = resolve_project_preview_image($project['url']);
        }
        unset($project);

        return $projects;
    }
}
