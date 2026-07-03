<?php
require_once 'config.php';
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare(
    "SELECT c.*, COUNT(p.id) AS total_pedidos
     FROM clientes c
     LEFT JOIN pedidos p ON p.cliente_id = c.id AND p.user_id = c.user_id
     WHERE c.user_id = ?
     GROUP BY c.id
     ORDER BY c.nome ASC"
);
$stmt->execute([$user_id]);
$clientes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ff007f">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Doce Controle">
    <link rel="manifest" href="manifest.json">
    <title>Doce Controle - Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/watermark.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand fw-bold"><i class="bi bi-people-fill"></i> Doce Controle - Clientes</span>
        <div class="d-flex gap-2">
            <a href="index.php" class="btn btn-outline-light btn-sm">Voltar</a>
        </div>
    </div>
</nav>

<div class="container">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between flex-column flex-md-row gap-2">
            <button class="btn btn-primary btn-lg" data-bs-toggle="modal" data-bs-target="#modalCliente">
                <i class="bi bi-person-plus"></i> Novo Cliente
            </button>
            <div class="text-muted align-self-center">Total de clientes: <strong><?= count($clientes) ?></strong></div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0 overflow-auto">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>WhatsApp</th>
                        <th class="text-center">Pedidos</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clientes) > 0): ?>
                        <?php foreach ($clientes as $c): ?>
                        <tr>
                            <td class="align-middle"><?= htmlspecialchars($c['nome']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($c['email']) ?></td>
                            <td class="align-middle"><?= htmlspecialchars($c['whatsapp']) ?></td>
                            <td class="align-middle text-center"><?= $c['total_pedidos'] ?></td>
                            <td class="text-center align-middle">
                                <div class="btn-group" role="group">
                                    <a href="pedidos.php?cliente_id=<?= $c['id'] ?>" class="btn btn-outline-secondary btn-sm" title="Ver histórico de compras">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <a href="excluir_cliente.php?id=<?= $c['id'] ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Excluir este cliente e seus dados?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center p-4 text-muted">Nenhum cliente cadastrado.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalCliente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="salvar_cliente.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Cadastrar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control form-control-lg" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">WhatsApp <span class="text-danger">*</span></label>
                        <input type="tel" name="whatsapp" class="form-control form-control-lg" placeholder="(00) 00000-0000" required>
                        <div class="form-text">O WhatsApp é obrigatório para este cliente.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <input type="email" name="email" class="form-control form-control-lg" placeholder="nome@exemplo.com">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary btn-lg w-100">Salvar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/pwa.js"></script>
</body>
</html>
