<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Build Mate - Authentication' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" onerror="this.onerror=null; this.href='<?= \App\View::asset('assets/css/bootstrap-icons-fallback.css') ?>';">
    <noscript>
        <link rel="stylesheet" href="<?= \App\View::asset('assets/css/bootstrap-icons-fallback.css') ?>">
    </noscript>
    <link rel="stylesheet" href="<?= \App\View::asset('assets/css/main.css') ?>">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
    </style>
</head>
<body>
    <?= $content ?? '' ?>
</body>
</html>
