<?php
require_once 'conexao.php';

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';
$response = array('success' => false, 'message' => 'Ação não especificada');

try {
  if ($action === 'add') {
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    if (empty($nome)) {
      throw new Exception("Nome da coleção não pode estar vazio");
    }
    
    $stmt = $pdo->prepare("INSERT INTO tb_colecao (colecaoNome) VALUES (?)");
    $stmt->execute(array($nome));
    
    $response = array('success' => true, 'message' => 'Coleção adicionada com sucesso');
    
  } elseif ($action === 'update') {
    $id = isset($_POST['id']) ? $_POST['id'] : 0;
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    
    $stmt = $pdo->prepare("UPDATE tb_colecao SET colecaoNome = ? WHERE id = ?");
    $stmt->execute(array($nome, $id));
    
    $response = array('success' => true, 'message' => 'Coleção atualizada com sucesso');
    
  } elseif ($action === 'remove') {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    
    // Verificar se há produtos usando esta coleção
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_produto WHERE idColecao = ?");
    $stmt->execute(array($id));
    $count = $stmt->fetchColumn();
    
    if ($count > 0) {
      throw new Exception("Não é possível remover - existem produtos vinculados a esta coleção");
    }
    
    $stmt = $pdo->prepare("DELETE FROM tb_colecao WHERE id = ?");
    $stmt->execute(array($id));
    
    $response = array('success' => true, 'message' => 'Coleção removida com sucesso');
  }
} catch (PDOException $e) {
  $response = array('success' => false, 'message' => 'Erro no banco de dados: ' . $e->getMessage());
} catch (Exception $e) {
  $response = array('success' => false, 'message' => $e->getMessage());
}

echo json_encode($response);