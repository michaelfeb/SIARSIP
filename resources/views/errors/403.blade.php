<!-- resources/views/errors/404.blade.php -->

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Permission Denied</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            flex-direction: column;
            color: #334155;
        }

        h1 {
            font-size: 6rem;
            margin: 0;
        }

        p {
            margin-top: 1rem;
            font-size: 1.25rem;
        }

        a {
            margin-top: 2rem;
            padding: 0.75rem 1.5rem;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        a:hover {
            background-color: #1e40af;
        }
    </style>
</head>

<body>
    <h1>404</h1>
    <p>Oops! Anda tidak diperbolehkan mengakses halaman ini.</p>
    <a href="{{ route('home') }}">Back to Home</a>
</body>

</html>