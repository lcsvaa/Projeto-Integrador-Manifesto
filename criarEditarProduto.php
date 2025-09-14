<?php
require_once 'conexao.php';
header('Content-Type: application/json');

try {
    $idProduto = isset($_POST['id']) ? intval($_POST['id']) : null;
    $nomeItem = isset($_POST['nomeItem']) ? $_POST['nomeItem'] : null;
    $descItem = isset($_POST['descItem']) ? $_POST['descItem'] : null;
    $valorItem = isset($_POST['valorItem']) ? $_POST['valorItem'] : null;
    $estoqueItem = isset($_POST['estoqueItem']) ? $_POST['estoqueItem'] : null;
    $idCategoria = isset($_POST['idCategoria']) ? $_POST['idCategoria'] : null;
    $idColecao = isset($_POST['idColecao']) ? $_POST['idColecao'] : null;
    $tamanhoUnico = !empty($_POST['tamanhoUnico']) ? 1 : 0;

    $pastaDestino = 'uploads/produtos/';
    $extPermitidas = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    $mimePermitidos = array('image/jpeg', 'image/png', 'image/gif', 'image/webp');
    $maxTamanho = 10 * 1024 * 1024; // 10MB

    $nomeSanitizado = preg_replace('/[^a-z0-9-_]/i', '_', strtolower($nomeItem));

    // Validação básica dos dados obrigatórios
    if (!$nomeItem || !$descItem || !$valorItem || $estoqueItem === null || !$idCategoria || !$idColecao) {
        throw new Exception("Campos obrigatórios faltando.");
    }
    if (!$idProduto && !isset($_FILES['imagemPrincipal'])) {
        throw new Exception("Imagem principal é obrigatória para novo produto.");
    }

    if ($idProduto) {
        // EDIÇÃO
        $stmt = $pdo->prepare("
            UPDATE tb_produto SET 
                `nomeItem` = ?, `descItem` = ?, `valorItem` = ?, `estoqueItem` = ?, 
                `idCategoria` = ?, `idColecao` = ?
            WHERE `id` = ?
        ");
        $stmt->execute(array($nomeItem, $descItem, $valorItem, $estoqueItem, $idCategoria, $idColecao, $idProduto));

        // Atualiza tamanhos
        $stmtDel = $pdo->prepare("DELETE FROM tb_produto_tamanho WHERE idProduto = ?");
        $stmtDel->execute(array($idProduto));

        if (!$tamanhoUnico) {
            $estoqueP = isset($_POST['estoqueP']) ? intval($_POST['estoqueP']) : 0;
            $estoqueM = isset($_POST['estoqueM']) ? intval($_POST['estoqueM']) : 0;
            $estoqueG = isset($_POST['estoqueG']) ? intval($_POST['estoqueG']) : 0;

            $stmtTamanho = $pdo->prepare("
                INSERT INTO tb_produto_tamanho (idProduto, tamanho, estoque)
                VALUES (?, ?, ?)
            ");

            if ($estoqueP > 0) $stmtTamanho->execute(array($idProduto, 'P', $estoqueP));
            if ($estoqueM > 0) $stmtTamanho->execute(array($idProduto, 'M', $estoqueM));
            if ($estoqueG > 0) $stmtTamanho->execute(array($idProduto, 'G', $estoqueG));
        }

        // Atualizar imagem principal
        if (isset($_FILES['imagemPrincipal']) && $_FILES['imagemPrincipal']['error'] === 0) {
            // Validações de extensão, mime e tamanho
            $imagem = $_FILES['imagemPrincipal'];
            $fileInfo = pathinfo($imagem['name']);
            $extensao = strtolower(isset($fileInfo['extension']) ? $fileInfo['extension'] : '');

            if (!in_array($extensao, $extPermitidas)) {
                throw new Exception("Formato de imagem principal não permitido. Apenas JPG, JPEG, PNG, GIF e WEBP.");
            }

            if ($imagem['size'] > $maxTamanho) {
                throw new Exception("Imagem principal excede o tamanho máximo de 10MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $imagem['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $mimePermitidos)) {
                throw new Exception("Tipo MIME da imagem principal inválido.");
            }

            $stmtImgOld = $pdo->prepare("SELECT nomeImagem FROM tb_imagemProduto WHERE idProduto = ? AND statusImagem = 'principal'");
            $stmtImgOld->execute(array($idProduto));
            $imagemAntiga = $stmtImgOld->fetchColumn();

            if ($imagemAntiga && file_exists($pastaDestino . $imagemAntiga)) {
                unlink($pastaDestino . $imagemAntiga);
            }

            $stmtDelImg = $pdo->prepare("DELETE FROM tb_imagemProduto WHERE idProduto = ? AND statusImagem = 'principal'");
            $stmtDelImg->execute(array($idProduto));

            $nomeImagem = $nomeSanitizado . '_principal_' . uniqid() . '.' . $extensao;

            if (!move_uploaded_file($imagem['tmp_name'], $pastaDestino . $nomeImagem)) {
                throw new Exception("Falha ao mover a imagem principal.");
            }

            $stmtImg = $pdo->prepare("
                INSERT INTO tb_imagemProduto (nomeImagem, statusImagem, idProduto)
                VALUES (?, 'principal', ?)
            ");
            $stmtImg->execute(array($nomeImagem, $idProduto));
        }

        // Atualizar outras imagens
        if (isset($_FILES['outrasImagens']) && is_array($_FILES['outrasImagens']['name'])) {
            $total = count($_FILES['outrasImagens']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['outrasImagens']['error'][$i] === 0) {
                    $fileInfo = pathinfo($_FILES['outrasImagens']['name'][$i]);
                    $ext = strtolower(isset($fileInfo['extension']) ? $fileInfo['extension'] : '');
                    $size = $_FILES['outrasImagens']['size'][$i];
                    $tmpName = $_FILES['outrasImagens']['tmp_name'][$i];

                    if (!in_array($ext, $extPermitidas)) {
                        throw new Exception("Formato da imagem extra #" . ($i+1) . " não permitido.");
                    }

                    if ($size > $maxTamanho) {
                        throw new Exception("Imagem extra #" . ($i+1) . " excede o tamanho máximo de 10MB.");
                    }

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (!in_array($mime, $mimePermitidos)) {
                        throw new Exception("Tipo MIME da imagem extra #" . ($i+1) . " inválido.");
                    }

                    $nomeImagemExtra = $nomeSanitizado . '_extra_' . ($i + 1) . '_' . uniqid() . '.' . $ext;
                    if (!move_uploaded_file($tmpName, $pastaDestino . $nomeImagemExtra)) {
                        throw new Exception("Falha ao mover a imagem extra #" . ($i+1));
                    }

                    $stmtExtra = $pdo->prepare("
                        INSERT INTO tb_imagemProduto (nomeImagem, statusImagem, idProduto)
                        VALUES (?, 'ativa', ?)
                    ");
                    $stmtExtra->execute(array($nomeImagemExtra, $idProduto));
                }
            }
        }

        $mensagem = 'Produto atualizado com sucesso!';
    } else {
        // INSERÇÃO
        $stmt = $pdo->prepare("
            INSERT INTO tb_produto 
            (`nomeItem`, `descItem`, `valorItem`, `estoqueItem`, `idCategoria`, `idColecao`)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(array($nomeItem, $descItem, $valorItem, $estoqueItem, $idCategoria, $idColecao));
        $idProduto = $pdo->lastInsertId();

            if ($tamanhoUnico) {
                // Garante que não ficou nenhum resquício de estoque P/M/G
                $stmtDel = $pdo->prepare("DELETE FROM tb_produto_tamanho WHERE idProduto = ?");
                $stmtDel->execute([$idProduto]);
            } else {
                $estoqueP = isset($_POST['estoqueP']) ? intval($_POST['estoqueP']) : 0;
                $estoqueM = isset($_POST['estoqueM']) ? intval($_POST['estoqueM']) : 0;
                $estoqueG = isset($_POST['estoqueG']) ? intval($_POST['estoqueG']) : 0;

                $stmtTamanho = $pdo->prepare("
                    INSERT INTO tb_produto_tamanho (idProduto, tamanho, estoque)
                    VALUES (?, ?, ?)
                ");

                if ($estoqueP > 0) $stmtTamanho->execute([$idProduto, 'P', $estoqueP]);
                if ($estoqueM > 0) $stmtTamanho->execute([$idProduto, 'M', $estoqueM]);
                if ($estoqueG > 0) $stmtTamanho->execute([$idProduto, 'G', $estoqueG]);
            }

        // Upload imagem principal
        if (isset($_FILES['imagemPrincipal']) && $_FILES['imagemPrincipal']['error'] === 0) {
            $imagem = $_FILES['imagemPrincipal'];
            $fileInfo = pathinfo($imagem['name']);
            $extensao = strtolower(isset($fileInfo['extension']) ? $fileInfo['extension'] : '');

            if (!in_array($extensao, $extPermitidas)) {
                throw new Exception("Formato de imagem principal não permitido. Apenas JPG, JPEG, PNG, GIF e WEBP.");
            }

            if ($imagem['size'] > $maxTamanho) {
                throw new Exception("Imagem principal excede o tamanho máximo de 10MB.");
            }

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $imagem['tmp_name']);
            finfo_close($finfo);

            if (!in_array($mime, $mimePermitidos)) {
                throw new Exception("Tipo MIME da imagem principal inválido.");
            }

            $nomeImagem = $nomeSanitizado . '_principal_' . uniqid() . '.' . $extensao;
            if (!move_uploaded_file($imagem['tmp_name'], $pastaDestino . $nomeImagem)) {
                throw new Exception("Falha ao mover a imagem principal.");
            }

            $stmtImg = $pdo->prepare("
                INSERT INTO tb_imagemProduto (nomeImagem, statusImagem, idProduto)
                VALUES (?, 'principal', ?)
            ");
            $stmtImg->execute(array($nomeImagem, $idProduto));
        }

        // Upload outras imagens
        if (isset($_FILES['outrasImagens']) && is_array($_FILES['outrasImagens']['name'])) {
            $total = count($_FILES['outrasImagens']['name']);
            for ($i = 0; $i < $total; $i++) {
                if ($_FILES['outrasImagens']['error'][$i] === 0) {
                    $fileInfo = pathinfo($_FILES['outrasImagens']['name'][$i]);
                    $ext = strtolower(isset($fileInfo['extension']) ? $fileInfo['extension'] : '');
                    $size = $_FILES['outrasImagens']['size'][$i];
                    $tmpName = $_FILES['outrasImagens']['tmp_name'][$i];

                    if (!in_array($ext, $extPermitidas)) {
                        throw new Exception("Formato da imagem extra #" . ($i+1) . " não permitido.");
                    }

                    if ($size > $maxTamanho) {
                        throw new Exception("Imagem extra #" . ($i+1) . " excede o tamanho máximo de 10MB.");
                    }

                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $tmpName);
                    finfo_close($finfo);

                    if (!in_array($mime, $mimePermitidos)) {
                        throw new Exception("Tipo MIME da imagem extra #" . ($i+1) . " inválido.");
                    }

                    $nomeImagemExtra = $nomeSanitizado . '_extra_' . ($i + 1) . '_' . uniqid() . '.' . $ext;
                    if (!move_uploaded_file($tmpName, $pastaDestino . $nomeImagemExtra)) {
                        throw new Exception("Falha ao mover a imagem extra #" . ($i+1));
                    }

                    $stmtExtra = $pdo->prepare("
                        INSERT INTO tb_imagemProduto (nomeImagem, statusImagem, idProduto)
                        VALUES (?, 'ativa', ?)
                    ");
                    $stmtExtra->execute(array($nomeImagemExtra, $idProduto));
                }
            }
        }

        $mensagem = 'Produto inserido com sucesso!';
    }

    echo json_encode(array(
        'status' => 'success',
        'message' => $mensagem,
        'idProduto' => $idProduto
    ));
} catch (Exception $e) {
    echo json_encode(array(
        'status' => 'error',
        'message' => 'Erro ao salvar produto: ' . $e->getMessage()
    ));
}
