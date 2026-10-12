<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container" style="max-width: 380px">
  <div class="card mt-5">
    <div class="card-body">
      <h1 class="h4 mb-3">Instituto · Calendário</h1>
      <?php if (session()->getFlashdata('erro')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('erro')) ?></div>
      <?php endif; ?>
      <form method="post" action="<?= site_url('login') ?>">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="email">E-mail</label>
          <input class="form-control" type="email" id="email" name="email" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label" for="senha">Senha</label>
          <input class="form-control" type="password" id="senha" name="senha" required>
        </div>
        <button class="btn btn-primary w-100">Entrar</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
