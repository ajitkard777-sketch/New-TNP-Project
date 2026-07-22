<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #0f172a, #1e1b4b); min-height: 100vh; display: flex; align-items: center; justify-content: center; color: #fff; }
        .error-container { text-align: center; padding: 40px; }
        .error-code { font-size: 8rem; font-weight: 800; background: linear-gradient(135deg, #f43f5e, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; line-height: 1; }
        .error-title { font-size: 1.5rem; font-weight: 600; margin: 16px 0 8px; }
        .error-text { color: rgba(255,255,255,0.6); margin-bottom: 32px; }
        .btn-home { display: inline-flex; align-items: center; gap: 8px; padding: 12px 28px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: #fff; text-decoration: none; border-radius: 10px; font-weight: 600; transition: transform 0.2s, box-shadow 0.2s; }
        .btn-home:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(99, 102, 241, 0.4); color: #fff; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h1 class="error-title">Access Denied</h1>
        <p class="error-text">You don't have permission to access this page.</p>
        <a href="<?= BASE_URL ?>/" class="btn-home"><i class="fas fa-home"></i> Go Home</a>
    </div>
</body>
</html>
