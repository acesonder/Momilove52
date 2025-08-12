<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error - Momilove52 Care Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 500px;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">⚠️</div>
        <h1 class="mb-3">Oops! Something went wrong</h1>
        <p class="text-muted mb-4">
            We're sorry, but something unexpected happened. Our team has been notified and is working to fix the issue.
        </p>
        <div class="d-grid gap-2">
            <a href="index.php" class="btn btn-primary">Return to Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-outline-secondary">Go Back</a>
        </div>
        <hr class="my-4">
        <small class="text-muted">
            If this problem persists, please contact support.<br>
            Error ID: <?php echo uniqid(); ?>
        </small>
    </div>
</body>
</html>