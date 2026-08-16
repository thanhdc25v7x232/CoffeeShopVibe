<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?? "Coffee Shop Vibe" ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="/css/style.css" rel="stylesheet">
    
</head>
<body>
    
    <?php include 'header.php'; ?>
    
    <div class="main-content" style="min-height: 600px;">
        <?= $content ?? '<p class="text-center">Chưa có nội dung</p>' ?> 
    </div>

    <?php include 'footer.php'; ?>

    <script>
        const CSRF_TOKEN = '<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>';
    </script>
</body>
</html>