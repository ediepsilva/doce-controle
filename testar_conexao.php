<?php
require_once 'config.php';

try {
    // Consulta o MySQL para ver quais tabelas existem no banco doce_controle
    $query = $pdo->query("SHOW TABLES");
    $tabelas = $query->fetchAll(PDO::FETCH_COLUMN);

    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>✅ Conexão com o Banco de Dados OK!</h2>";
    echo "<h3>Tabelas encontradas no sistema:</h3>";
    echo "<ul style='display: inline-block; text-align: left; font-size: 18px; color: #333;'>";
    foreach ($tabelas as $tabela) {
        echo "<li>📦 <b>" . $tabela . "</b></li>";
    }
    echo "</ul>";
    echo "<p style='margin-top: 20px; color: gray;'>O banco está pronto para receber as telas!</p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h2 style='color: red; text-align: center;'>❌ Erro: " . $e->getMessage() . "</h2>";
}