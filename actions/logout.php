<?php
session_start();

// Esvazia as variáveis e encerra a sessão ativa
session_unset();
session_destroy();

// Devolve o usuário para a raiz (tela de login)
header('Location: ../index.php');
exit;