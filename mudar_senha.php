<?php
session_start();
require_once 'conexao.php';

// Only process POST requests from authenticated users
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $current_password = isset($_POST['current-password']) ? $_POST['current-password'] : '';
    $new_password = isset($_POST['new-password']) ? $_POST['new-password'] : '';
    $confirm_password = isset($_POST['confirm-password']) ? $_POST['confirm-password'] : '';
    
    // Validate password length
    if (strlen($new_password) < 8) {
        $_SESSION['error_message'] = "A nova senha deve ter pelo menos 8 caracteres!";
        header('Location: profile.php#security');
        exit();
    }
    
    try {
        // Get user's current password hash
        $stmt = $pdo->prepare("SELECT senha FROM tb_usuario WHERE id = ?");
        $stmt->execute(array($user_id));
        $user = $stmt->fetch();
        
        if (!$user) {
            throw new Exception("Usuário não encontrado");
        }
        
        // Verify current password
        if (password_verify($current_password, $user['senha'])) {
            // Check if new passwords match
            if ($new_password === $confirm_password) {
                // Check if new password is different from current
                if (password_verify($new_password, $user['senha'])) {
                    $_SESSION['error_message'] = "A nova senha deve ser diferente da atual!";
                } else {
                    // Hash and update the new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE tb_usuario SET senha = ? WHERE id = ?");
                    $stmt->execute(array($hashed_password, $user_id));
                    
                    $_SESSION['success_message'] = "Senha alterada com sucesso!";
                    
                    // Optional: Send email notification
                    // sendPasswordChangeNotification($user_id);
                }
            } else {
                $_SESSION['error_message'] = "As novas senhas não coincidem!";
            }
        } else {
            $_SESSION['error_message'] = "Senha atual incorreta!";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Erro ao atualizar senha. Por favor, tente novamente.";
        error_log("Password change error for user $user_id: " . $e->getMessage());
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    header('Location: profile.php#security');
    exit();
}

// If accessed directly or without proper auth, redirect
header('Location: index.php');
exit();
?>