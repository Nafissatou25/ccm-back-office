<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCM - Connexion</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background: #f2f3f7;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 1.5rem;
            margin: 0;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #ffffff;
            border-radius: 24px;
            padding: 2.5rem 2rem 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s ease;
        }

        .login-card:hover {
            transform: scale(1.005);
        }

        /* Logo et marque */
        .brand {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand .icon {
            font-size: 3.5rem;
            color: #2D2961;
            display: block;
            margin-bottom: 0.5rem;
        }

        .brand h2 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #2D2961;
            margin: 0;
            letter-spacing: -0.3px;
        }

        .brand .subtitle {
            font-size: 0.9rem;
            color: #6b6b7b;
            margin-top: 0.2rem;
        }

        /* Champs de formulaire */
        .form-control {
            border-radius: 12px;
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #2D2961;
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(45, 41, 97, 0.10);
        }

        .input-group-text {
            background: transparent;
            border: 1px solid #e2e8f0;
            border-right: none;
            border-radius: 12px 0 0 12px;
            color: #94a3b8;
            padding: 0.75rem 0.9rem;
        }

        .input-group .form-control {
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
            color: #1e293b;
            margin-bottom: 0.4rem;
        }

        /* Bouton */
        .btn-primary {
            background: #2D2961;
            border: none;
            border-radius: 12px;
            padding: 0.8rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s ease;
            box-shadow: 0 8px 18px rgba(45, 41, 97, 0.20);
        }

        .btn-primary:hover {
            background: #1e1b4a;
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(45, 41, 97, 0.30);
        }

        .btn-primary:active {
            transform: translateY(0);
        }

        /* Messages d'erreur */
        .alert-danger {
            border-radius: 12px;
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            font-size: 0.9rem;
            padding: 0.75rem 1rem;
            margin-top: 1.5rem;
        }

        .alert-danger ul {
            margin: 0;
            padding-left: 1.2rem;
        }

        .hint {
            text-align: center;
            font-size: 0.8rem;
            color: #9e9e9e;
            margin-top: 1.8rem;
            border-top: 1px solid #e9edf2;
            padding-top: 1.5rem;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-card {
                padding: 1.8rem 1.2rem;
                border-radius: 20px;
            }
            .brand .icon {
                font-size: 2.8rem;
            }
            .brand h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>

<body>

<div class="login-card">

    {{-- En-tête avec logo --}}
    <div class="brand">
        <i class="bi bi-lightning-charge icon"></i>
        <h2>CCM-SOCAD'EL</h2>
        <p class="subtitle">Gestion des réclamations clients</p>
    </div>

    {{-- Formulaire --}}
    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="mb-3">
            <label for="matricule" class="form-label">Matricule</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                <input id="matricule"
                       type="text"
                       name="matricule"
                       class="form-control"
                       placeholder="Votre matricule"
                       value="{{ old('matricule') }}"
                       required
                       autofocus>
            </div>
        </div>

        <div class="mb-4">
            <label for="password" class="form-label">Mot de passe</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input id="password"
                       type="password"
                       name="password"
                       class="form-control"
                       placeholder="Votre mot de passe"
                       required>
            </div>
        </div>

        {{-- Bouton de connexion --}}
        <button type="submit" class="btn btn-primary w-100">
            <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
        </button>

        {{-- Erreurs --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                @if ($errors->has('matricule') || $errors->has('password'))
                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                    Identifiants incorrects. Veuillez réessayer.
                @else
                    {{ $errors->first() }}
                @endif
            </div>
        @endif

        <div class="hint">
            <span>© SOCADEL {{ date('Y') }}</span>
        </div>

    </form>

</div>

<!-- Bootstrap JS (facultatif) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>