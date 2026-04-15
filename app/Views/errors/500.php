<?php
/**
 * 500 Internal Server Error Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .error-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 60px 40px;
            text-align: center;
            max-width: 600px;
            width: 100%;
        }

        .error-code {
            font-size: 120px;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 20px;
            line-height: 1;
        }

        .error-title {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .error-description {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: #e74c3c;
            color: white;
        }

        .btn-primary:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #e0e0e0;
            transform: translateY(-2px);
        }

        .error-details {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            text-align: left;
            margin-top: 30px;
            border-radius: 5px;
        }

        .error-details p {
            font-size: 13px;
            color: #856404;
            margin-bottom: 8px;
        }

        .error-details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">500</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-description">
            Something went wrong on our end. Our team has been notified 
            and is working to fix the issue.
        </p>

        <div class="error-actions">
            <a href="<?php echo url('/'); ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="javascript:location.reload()" class="btn btn-secondary">Retry</a>
        </div>

        <div class="error-details">
            <p><strong>⚠️ Internal Server Error</strong></p>
            <p>An unexpected error occurred while processing your request.</p>
            <p style="margin-top: 15px;"><strong>What will happen next?</strong></p>
            <p>• Our system administrators have been alerted</p>
            <p>• We're investigating the issue</p>
            <p>• Please try again in a few moments</p>
            <p>• Contact support if the problem persists</p>
        </div>
    </div>
</body>
</html>
