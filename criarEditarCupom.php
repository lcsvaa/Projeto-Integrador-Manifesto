<?php
header('Content-Type: application/json');

require_once 'conexao.php'; // caminho relativo, ajuste se necessário

// Função para enviar resposta JSON e sair
function resposta($status, $mensagem) {
    echo json_encode(array('status' => $status, 'message' => $mensagem));
    exit;
}

// Receber dados do POST e sanitizar
$id = isset($_POST['id']) && $_POST['id'] !== '' ? intval($_POST['id']) : null;
$codigo = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';
$porcentagemDesconto = isset($_POST['porcentagemDesconto']) ? floatval($_POST['porcentagemDesconto']) : 0;
$tipoDesconto = isset($_POST['tipoDesconto']) ? $_POST['tipoDesconto'] : 'porcentagem'; // 'porcentagem' ou 'valor'
$dataValidade = isset($_POST['dataValidade']) ? $_POST['dataValidade'] : null;
$quantidadeUso = isset($_POST['quantidadeUso']) && $_POST['quantidadeUso'] !== '' ? intval($_POST['quantidadeUso']) : null;
$valorCompraMin = isset($_POST['valorCompraMin']) && $_POST['valorCompraMin'] !== '' ? floatval($_POST['valorCompraMin']) : 0.0;
$descricao = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';

// Validações básicas
if ($codigo === '') resposta('error', 'O código do cupom é obrigatório.');
if ($porcentagemDesconto <= 0) resposta('error', 'O valor do desconto deve ser maior que zero.');
if (!in_array($tipoDesconto, array('porcentagem', 'valor'))) resposta('error', 'Tipo de desconto inválido.');
if ($dataValidade && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dataValidade)) resposta('error', 'Data de validade inválida. Use o formato YYYY-MM-DD.');

// Se quantidadeUso for nulo, define como NULL no banco para ilimitado
$quantidadeUsoBanco = $quantidadeUso === null ? null : $quantidadeUso;

try {
    if ($id) {
        // Atualizar cupom existente
        $sql = "UPDATE tb_cupom SET 
                    codigo = :codigo,
                    porcentagemDesconto = :porcentagemDesconto,
                    tipoCupom = :tipoDesconto,
                    dataValidade = :dataValidade,
                    quantidadeUso = :quantidadeUso,
                    valorCompraMin = :valorCompraMin,
                    descricaoCupom = :descricao
                WHERE idCupom = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    } else {
        // Inserir novo cupom
        $sql = "INSERT INTO tb_cupom (
                    codigo, porcentagemDesconto, tipoCupom, dataValidade, quantidadeUso, valorCompraMin, descricaoCupom, statusCupom, utilizados
                ) VALUES (
                    :codigo, :porcentagemDesconto, :tipoDesconto, :dataValidade, :quantidadeUso, :valorCompraMin, :descricao, 'ativo', 0
                )";
        $stmt = $pdo->prepare($sql);
    }

    // Bind dos parâmetros comuns
    $stmt->bindValue(':codigo', $codigo, PDO::PARAM_STR);
    $stmt->bindValue(':porcentagemDesconto', $porcentagemDesconto);
    $stmt->bindValue(':tipoDesconto', $tipoDesconto, PDO::PARAM_STR);
    $stmt->bindValue(':dataValidade', $dataValidade ? $dataValidade : null, PDO::PARAM_STR);
    if ($quantidadeUsoBanco === null) {
        $stmt->bindValue(':quantidadeUso', null, PDO::PARAM_NULL);
    } else {
        $stmt->bindValue(':quantidadeUso', $quantidadeUsoBanco, PDO::PARAM_INT);
    }
    $stmt->bindValue(':valorCompraMin', $valorCompraMin);
    $stmt->bindValue(':descricao', $descricao, PDO::PARAM_STR);

    $stmt->execute();

    if ($id) {
        resposta('success', 'Cupom atualizado com sucesso!');
    } else {
        resposta('success', 'Cupom criado com sucesso!');
    }
} catch (PDOException $e) {

    if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
        resposta('error', 'Código do cupom já existe.');
    } else {
        resposta('error', 'Erro no banco de dados: ' . $e->getMessage());
    }
}