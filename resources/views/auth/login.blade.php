<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Connexion - ENEO CCM</title>

  <link rel="stylesheet" href="{{ asset('vendors/feather/feather.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/mdi/css/materialdesignicons.min.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/ti-icons/css/themify-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/typicons/typicons.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/simple-line-icons/css/simple-line-icons.css') }}">
  <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">

  <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
</head>

<body>

<div class="container-scroller">

  <div class="container-fluid page-body-wrapper full-page-wrapper">

    <div class="content-wrapper d-flex align-items-center auth px-0">

      <div class="row w-100 mx-0">

        <div class="col-lg-4 mx-auto">

          <div class="auth-form-light text-left py-5 px-4 px-sm-5">

            <div class="brand-logo text-center">
              <h3>ENEO-CCM</h3>
            </div>

            <h4>Connexion</h4>

            <!-- <h6 class="fw-light mb-4">
              Connectez-vous pour continuer
            </h6> -->

            @if ($errors->any())
              <div class="alert alert-danger">
                {{ $errors->first() }}
              </div>
            @endif

            <form class="pt-3"
                  method="POST"
                  action="{{ route('login.submit') }}">

              @csrf

              <div class="form-group">

                <input
                    type="email"
                    name="email"
                    class="form-control form-control-lg"
                    placeholder="Email"
                    required>

              </div>

              <div class="form-group">

                <input
                    type="password"
                    name="password"
                    class="form-control form-control-lg"
                    placeholder="Mot de passe"
                    required>

              </div>

              <div class="mt-3">

                <button
                    type="submit"
                    class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">

                    SE CONNECTER

                </button>

              </div>

            </form>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>

<script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>

</body>
</html>