<?php
/**
 * GymFlow - Modular SEO & Head Component
 * Configured with dynamic SEO meta tags, OpenGraph, JSON-LD Schema & Tailwind CSS
 */
if (!defined('APP_INIT')) {
    require_once __DIR__ . '/../config/app.php';
}

$pageTitle = $pageTitle ?? (APP_NAME . ' - Elevate Your Strength & Peak Performance');
$pageDescription = $pageDescription ?? 'Gym Flow is a premium fitness club and gym management platform equipped with state-of-the-art equipment, certified coaches, and tailored training programs.';
$pageKeywords = $pageKeywords ?? 'gym, fitness club, bodybuilding, personal trainer, workout classes, HIIT, crossfit, strength training, Gym Flow';
$canonicalUrl = $canonicalUrl ?? (BASE_URL . '/' . basename($_SERVER['SCRIPT_NAME']));
$ogImage = $ogImage ?? asset('images/0d931a74f61693ae690eeeac95436444.jpg');
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <!-- Primary SEO & Theme Meta Tags -->
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <meta name="title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="keywords" content="<?= htmlspecialchars($pageKeywords) ?>">
    <meta name="author" content="Gym Flow Inc.">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta name="theme-color" content="#050507">

    <!-- Progressive Web App (PWA) Manifest -->
    <link rel="manifest" href="<?= url('manifest.json') ?>">

    <!-- iOS Apple Web App Configuration -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="GymFlow">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= asset('images/icons/apple-touch-icon.png') ?>">

    <!-- Android & Windows Tile Configuration -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="application-name" content="GymFlow">
    <meta name="msapplication-TileColor" content="#050507">
    <meta name="msapplication-TileImage" content="<?= asset('images/icons/icon-144x144.png') ?>">

    <!-- Multi-size Favicons & App Icons -->
    <link rel="icon" type="image/png" sizes="64x64" href="<?= asset('images/favicon.png') ?>">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= asset('images/icons/icon-192x192.png') ?>">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= asset('images/icons/icon-512x512.png') ?>">
    <link rel="icon" type="image/svg+xml" href="<?= asset('images/favicon.svg') ?>">
    <link rel="shortcut icon" href="<?= url('favicon.ico') ?>">

    <!-- PWA Auto-Update & Client Engine -->
    <script src="<?= asset('js/pwa.js') ?>?v=<?= APP_VERSION ?>" defer></script>

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="<?= htmlspecialchars($canonicalUrl) ?>">
    <meta property="twitter:title" content="<?= htmlspecialchars($pageTitle) ?>">
    <meta property="twitter:description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta property="twitter:image" content="<?= htmlspecialchars($ogImage) ?>">

    <!-- Google Fonts: Teko & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=Teko:wght@500;600;700&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- Tailwind CSS with Custom Dark Theme Configuration -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            red: '#ff2a2a',
                            'red-hover': '#e01f1f',
                            'red-dark': '#991111',
                            glow: 'rgba(255, 42, 42, 0.35)'
                        },
                        dark: {
                            950: '#000000',
                            900: '#070708',
                            850: '#0f1013',
                            800: '#15161b',
                            700: '#22252c',
                            600: '#343844'
                        }
                    },
                    fontFamily: {
                        heading: ['Teko', 'sans-serif'],
                        sans: ['Plus Jakarta Sans', 'sans-serif']
                    }
                }
            }
        }
    </script>

    <!-- Global Custom Styles -->
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">

    <!-- Structured JSON-LD Schema for Local Gym Business -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "ExerciseGym",
      "name": "Gym Flow",
      "image": "<?= htmlspecialchars($ogImage) ?>",
      "description": "<?= htmlspecialchars($pageDescription) ?>",
      "telephone": "+923001234567",
      "address": {
        "@type": "PostalAddress",
        "streetAddress": "742 Evergreen Fitness Blvd",
        "addressLocality": "Metropolis",
        "addressRegion": "NY",
        "postalCode": "10001",
        "addressCountry": "US"
      },
      "openingHoursSpecification": [
        {
          "@type": "OpeningHoursSpecification",
          "dayOfWeek": ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
          "opens": "05:00",
          "closes": "23:00"
        }
      ],
      "priceRange": "PKR 3000 - PKR 15000"
    }
    </script>
</head>
<body class="bg-[#000000] text-zinc-100 min-h-screen flex flex-col selection:bg-red-600 selection:text-white antialiased">
