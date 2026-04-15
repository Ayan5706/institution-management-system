<?php
/**
 * 403 Forbidden Page
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Access Forbidden</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
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
            color: #f39c12;
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
            background: #f39c12;
            color: white;
        }

        .btn-primary:hover {
            background: #e67e22;
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
            background: #f9f9f9;
            border-left: 4px solid #f39c12;
            padding: 20px;
            text-align: left;
            margin-top: 30px;
            border-radius: 5px;
        }

        .error-details p {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
        }

        .error-details strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h1 class="error-title">Access Forbidden</h1>
        <p class="error-description">
            You don't have permission to access this resource. 
            Please contact your administrator if you believe this is an error.
        </p>

        <div class="error-actions">
            <a href="<?php echo url('/'); ?>" class="btn btn-primary">Go to Homepage</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Go Back</a>
        </div>

        <div class="error-details">
            <p><strong>🔐 Access Denied</strong></p>
            <p>Your account doesn't have the required permissions for this action.</p>
            <p style="margin-top: 15px;"><strong>What you can do:</strong></p>
            <p>• Return to the homepage</p>
            <p>• Check your account permissions</p>
            <p>• Contact your administrator for access</p>
            <p>• Request elevated permissions if needed</p>
        </div>
    </div>
</body>
</html>
