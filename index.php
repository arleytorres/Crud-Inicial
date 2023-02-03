<?php
    // DEBUG
    //ini_set('display_errors', 1);
    //ini_set('display_startup_errors', 1);
    //error_reporting(E_ALL);
    //print_r($_POST);

    $mysql = mysqli_connect('localhost', 'root', 'usbw', 'loja');
    function seguroString($texto) {
        $texto = addslashes(trim($texto));
        return $texto;
    }

    if (isset($_POST['nome']) && isset($_POST['senha'])) {
        $variaveis['Nome'] = seguroString($_POST['nome']);
        $variaveis['Senha'] = seguroString($_POST['senha']);

        $sql = $mysql->prepare("SELECT Senha, Permissao FROM usuarios WHERE User = ?");
        $sql->bind_param('s', $variaveis['Nome']);
        $sql->execute();
        $resultado = $sql->get_result();

        while ($coluna = $resultado->fetch_assoc()) {
            $db['Senha'] = $coluna['Senha'];
            $db['Permissao'] = $coluna['Permissao'];
        }

        if ($variaveis['Senha'] == $db['Senha']) {
            setcookie('Logado', true, (time() + 300));
            setcookie('Permissao', $db['Permissao'], (time() + 300));
            $_COOKIE['Logado'] = true;
            $_COOKIE['Permissao'] = $db['Permissao'];
        }
        else
        {
            echo '<script>alert("Usuário/Senha incorretos")</script>';
        }
    }

    if (isset($_POST['btnup'])) {
        $variaveis['ID'] = seguroString($_POST['itemid']);
        $variaveis['Nome'] = seguroString($_POST['nome']);
        $variaveis['Preco'] = seguroString($_POST['preco']);
        $variaveis['Estoque'] = seguroString($_POST['estoque']);

        $sql = $mysql->prepare("UPDATE produtos SET Nome = ?, Preco = ?, Estoque = ? WHERE ID = ?");
        $sql->bind_param('ssss', $variaveis['Nome'], $variaveis['Preco'], $variaveis['Estoque'], $variaveis['ID']);
        
        if ($sql->execute() == 1){
            echo '<script>alert("Item atualizado com sucesso!")</script>';
        }
        else{
            echo '<script>alert("Falha ao atualizar o item...")</script>';
        }
    }

    else if (isset($_POST['btndel'])) {
        $variaveis['ID'] = seguroString($_POST['itemid']);

        $sql = $mysql->prepare("DELETE FROM produtos WHERE ID = ?");
        $sql->bind_param('s', $variaveis['ID']);
        $sql->execute();
    }

    else if (isset($_POST['btnadd'])) {
        $variaveis['Nome'] = seguroString($_POST['nome']);
        $variaveis['Preco'] = seguroString($_POST['preco']);
        $variaveis['Estoque'] = seguroString($_POST['estoque']);

        $sql = $mysql->prepare("INSERT INTO produtos (Nome, Preco, Estoque) VALUES (?, ?, ?)");
        $sql->bind_param('sss', $variaveis['Nome'], $variaveis['Preco'], $variaveis['Estoque']);

        if ($sql->execute()){
            echo '<script>alert("Item adicionado com sucesso!")</script>';
        }
        else
        {
            echo '<script>alert("Falha ao adicionar o item...")</script>';
        }
    }

    else if (isset($_POST['btnlogout']) && $_COOKIE['Logado']) {
        unset($_COOKIE['Logado']);
        unset($_COOKIE['Permissao']);
        setcookie('Logado', false, 1);
        setcookie('Permissao', 0, 1);
    }
?>

<!DOCTYPE HTML>

<html>
    <head>
        <title>LOGIN | Controle de Produtos</title>
        <meta charset="UTF-8">
        <link rel="stylesheet" type="text/css" href="css/stylo.css">
    </head>
    <body>

        <?php
            if ($_COOKIE['Logado']) { goto loja; }
        ?>
        
        <?php login: ?>
        <h1>LOGIN</h1>
        <form method="POST" action="index.php">
            <input type="text" id="nome" name="nome" placeholder="Nome de usuário">
            <input type="text" id="senha" name="senha" placeholder="Senha de acesso">
            <input type="submit" value="Entrar">
        </form>
        <?php goto end ?>

        <?php loja: 

            $sql = $mysql->prepare('SELECT * FROM produtos');
            $sql->execute();
            $resultado = $sql->get_result();

            echo '<table>
                    <tr>
                        <th>ID</th>
                        <th>Item</th>
                        <th>Preço</th>
                        <th>Estoque</th>';
            if ($_COOKIE['Permissao'] == 1){
                echo '<th>Ações</th>';
            }
            echo '</tr>';

            while ($coluna = $resultado->fetch_assoc()) {
                echo '<tr>
                        <form method="post" action"index.php">
                            <td><input type="text"   name="id"      value="'.$coluna['ID'].'" disabled></td>
                            <td><input type="text"   name="nome"    value="'.$coluna['Nome'].'"></td>
                            <td><input type="number"   name="preco"   value="'.$coluna['Preco'].'"></td>
                            <td><input type="number"   name="estoque" value="'.$coluna['Estoque'].'"></td>';
                if ($_COOKIE['Permissao'] == 1){
                    echo '<td><input type="hidden" name="itemid"  value="'.$coluna['ID'].'"><input type="submit" name="btnup" value="Atualizar">  <input type="submit" name="btndel" value="Apagar"></td>';
                }
                echo '</form>
                    </tr>
                ';
                $variavel['LID'] = $coluna['ID'];
            }

            $sql = $mysql->prepare("SELECT auto_increment FROM INFORMATION_SCHEMA.TABLES WHERE table_name = 'produtos'");
            $sql->execute();
            $resultado = $sql->get_result();
            while ($coluna = $resultado->fetch_assoc()) {
                $variavel['LID'] = $coluna['auto_increment'];
            }
            

            if ($_COOKIE['Permissao'] == 1){
                echo '<tr>
                        <form method="post" action"index.php">
                        <td><input type="text" name="id" value="'.$variavel['LID'].'" disabled></td>
                        <td><input type="text" name="nome" placeholder="Item"  ></td>
                        <td><input type="number" name="preco" placeholder="Valor" ></td>
                        <td><input type="number" name="estoque" placeholder="Estoque"></td>
                        <td><input type="submit" name="btnadd" value="Adicionar"></td>
                         </form>
                    </tr>
                ';
            }
                echo '</table> <form method="post" action"index.php"><input type="submit" name="btnlogout" value="Logout"></form>';
        ?>
        <?php goto end ?>

        <?php end: ?>
    </body>
</html>