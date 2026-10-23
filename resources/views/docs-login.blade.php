<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Documentation - Access Required</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            max-width: 450px;
            width: 100%;
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo h1 {
            color: #333;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .logo p {
            color: #666;
            font-size: 14px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }

        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
        }

        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .error.show {
            display: block;
        }

        .info {
            background: #f0f4ff;
            color: #4a5568;
            padding: 12px;
            border-radius: 6px;
            margin-top: 20px;
            font-size: 13px;
            line-height: 1.6;
        }

        .info strong {
            color: #667eea;
        }

        @media (max-width: 500px) {
            .container {
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>🔒 API Documentation</h1>
            <p>Marketplace Ethiopia</p>
        </div>

        @if(isset($message))
        <div class="error show">
            {{ $message }}
        </div>
        @endif

        <form method="GET" action="{{ $docs_url ?? '/api/docs' }}" id="accessForm">
            <div class="form-group">
                <label for="token">Access Token</label>
                <input
                    type="password"
                    id="token"
                    name="token"
                    placeholder="Enter your access token"
                    required
                    autocomplete="off"
                >
            </div>

            <button type="submit">Access Documentation</button>
        </form>

        <div class="info">
            <strong>Need access?</strong><br>
            Contact the development team to get your access token.
        </div>
    </div>

    <script>
        // Optional: Client-side validation
        document.getElementById('accessForm').addEventListener('submit', function(e) {
            const token = document.getElementById('token').value;
            if (token.length < 8) {
                e.preventDefault();
                alert('Please enter a valid access token');
            }
        });
    </script>
</body>
</html>
