<?php
require_once 'config.php';

$view = $_GET['view'] ?? '';

if ($view === 'estoque') {
    header('Location: estoque.php');
    exit;
}

if ($view === 'receitas') {
    header('Location: receitas.php');
    exit;
}

header('Location: index.php');
exit;