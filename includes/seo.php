<?php

if (!function_exists('render_seo_tags')) {
    function render_seo_tags(array $config = []): string
    {
        $siteName = 'DJM Apps Laboratory';
        $defaultTitle = 'DJM Apps Laboratory | Server Dashboard';
        $defaultDescription = 'DJM Apps Laboratory dashboard for projects, deployments, and infrastructure monitoring.';
        $baseUrl = rtrim((string)($config['base_url'] ?? 'https://djm-apps.com'), '/');
        $path = (string)($config['path'] ?? '/');

        $title = (string)($config['title'] ?? $defaultTitle);
        $description = (string)($config['description'] ?? $defaultDescription);
        $robots = (string)($config['robots'] ?? 'index,follow');
        $themeColor = (string)($config['theme_color'] ?? '#030712');
        $type = (string)($config['type'] ?? 'website');
        $canonical = (string)($config['canonical'] ?? ($baseUrl . $path));
        $image = (string)($config['image'] ?? '/assets/djm-apps.png');
        $imageUrl = preg_match('/^https?:\/\//', $image) ? $image : $baseUrl . $image;
        $imageAlt = (string)($config['image_alt'] ?? 'DJM Apps Laboratory preview image');

        $e = static function (string $value): string {
            return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        };

        $jsonLd = json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => $baseUrl,
            'description' => $description,
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => $baseUrl,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => $baseUrl . '/assets/djm-apps.png',
                ],
            ],
        ], JSON_UNESCAPED_SLASHES);

        return implode(PHP_EOL, [
            '    <meta charset="UTF-8">',
            '    <meta name="viewport" content="width=device-width, initial-scale=1.0">',
            '    <title>' . $e($title) . '</title>',
            '    <meta name="description" content="' . $e($description) . '">',
            '    <meta name="robots" content="' . $e($robots) . '">',
            '    <link rel="canonical" href="' . $e($canonical) . '">',
            '    <link rel="icon" href="/assets/favicon.ico" sizes="any">',
            '    <link rel="shortcut icon" href="/assets/favicon.ico">',
            '    <link rel="apple-touch-icon" href="/assets/djm-apps.png">',
            '    <meta name="theme-color" content="' . $e($themeColor) . '">',
            '    <meta property="og:locale" content="en_US">',
            '    <meta property="og:type" content="' . $e($type) . '">',
            '    <meta property="og:site_name" content="' . $e($siteName) . '">',
            '    <meta property="og:title" content="' . $e($title) . '">',
            '    <meta property="og:description" content="' . $e($description) . '">',
            '    <meta property="og:url" content="' . $e($canonical) . '">',
            '    <meta property="og:image" content="' . $e($imageUrl) . '">',
            '    <meta property="og:image:alt" content="' . $e($imageAlt) . '">',
            '    <meta name="twitter:card" content="summary_large_image">',
            '    <meta name="twitter:title" content="' . $e($title) . '">',
            '    <meta name="twitter:description" content="' . $e($description) . '">',
            '    <meta name="twitter:image" content="' . $e($imageUrl) . '">',
            '    <script type="application/ld+json">' . (string)$jsonLd . '</script>',
        ]) . PHP_EOL;
    }
}
