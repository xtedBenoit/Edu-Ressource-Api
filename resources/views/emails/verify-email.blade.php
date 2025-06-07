<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Vérification de votre adresse email</title>
    <style>
        body {
            background-color: #f4f6f8;
            font-family: 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 30px 40px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .code-box {
            text-align: center;
            font-size: 28px;
            font-weight: bold;
            color: #2c3e50;
            background-color: #ecf0f1;
            padding: 15px 0;
            margin: 20px 0;
            border-radius: 6px;
            letter-spacing: 4px;
        }
        .footer {
            margin-top: 40px;
            font-size: 14px;
            text-align: center;
            color: #888;
        }
        .team-name {
            font-weight: bold;
            color: #34495e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Vérification de votre adresse email</h2>
        </div>

        <p>Bonjour {{ $firstname ?? 'utilisateur' }},</p>

        <p>Merci de vous être inscrit sur {{ config('app.name') }}. Pour finaliser votre inscription, veuillez utiliser le code ci-dessous :</p>

        <div class="code-box">{{ $code }}</div>

        <p>Ce code est <strong>valide pendant 15 minutes</strong>. Si vous n’avez pas créé de compte, vous pouvez ignorer cet e-mail.</p>

        <p>Merci,<br> <span class="team-name">L'équipe {{ config('app.name') }}</span></p>

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. Tous droits réservés.
        </div>
    </div>
</body>
</html>
