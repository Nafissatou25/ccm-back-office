<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            height: 100vh;
            background: linear-gradient(135deg, #0f172a, #1e293b);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: system-ui, -apple-system, sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .brand {
            text-align: center;
            margin-bottom: 25px;
        }

        .brand i {
            font-size: 40px;
            color: #2563eb;
        }

        .brand h2 {
            font-weight: 700;
            margin-top: 10px;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px;
        }

        .btn-primary {
            border-radius: 12px;
            padding: 12px;
            font-weight: 600;
        }

        .form-label {
            font-weight: 500;
        }

        .hint {
            font-size: 13px;
            color: #6b7280;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>

<body>

<div class="login-card">

    <div class="brand">
        <h2>Customer Complaint Management</h2>
        <p class="text-muted">Connexion à votre espace</p>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="mb-3">
            <label class="form-label">Matricule</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input
                       name="matricule"
                       class="form-control"
                       placeholder="Entrez votre matricule"
                       required>
            </div>
        </div>

        <!-- Password -->
        <div class="mb-3">
            <label class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Entrez votre mot de passe"
                       required>
            </div>
        </div>

        <!-- Remember -->
        <!-- <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember">
                <label class="form-check-label">Se souvenir de moi</label>
            </div>
        </div> -->

        <!-- Button -->
        <button class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-1"></i>
            Se connecter
        </button>

        <!-- Error -->
        @if ($errors->any())
            <div class="alert alert-danger mt-3">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="hint">
             
        </div>

    </form>

</div>

</body>
</html>