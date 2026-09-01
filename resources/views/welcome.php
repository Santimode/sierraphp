<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($name ?? 'Sierra') ?> - sierraPHP</title>
    <style>
        body { font-family: system-ui; display:flex; align-items:center; justify-content:center; height:100vh; background:#0f0f0f; color:#fff; margin:0; }
        .card { background:#1a1a1a; padding:40px; border-radius:16px; border:1px solid #333; text-align:center; }
        h1 { margin:0 0 10px; font-size:2.5rem; }
        p { color:#888; }
        code { background:#222; padding:2px 6px; border-radius:4px; }
        a { color:#7dd3fc; text-decoration:none; }
    </style>
</head>
<body>
    <div class="card">
        <h1>⛰️ sierraPHP</h1>
        <p>Light as the Sierra Madre.</p>
        <p>Hello, <strong><?= htmlspecialchars($name ?? 'World') ?></strong>!</p>
        <p><code>Version: 2.1.0</code> | <code>Repo: santimode/sierraphp</code></p>
        <p><a href="/api/health">/api/health</a> · <a href="/api/hello/Santi">/api/hello/{name}</a></p>
    </div>
</body>
</html>
