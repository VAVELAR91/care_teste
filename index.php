<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />
    <title>Importar TXT</title>
    <link rel="stylesheet" type="text/css" href="index.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
    function validaArquivo() {
        if (document.getElementById("arquivo").files.length == 0) {
            alert('Arquivo não selecionado!');
            return false;
        }
        return true;
    }

    function gravarNotas(event) {
		event.preventDefault();

        let numero = document.getElementById("numero").value;
        let valor = document.getElementById("valor").value;
        let dest = document.getElementById("dest").value;
        let xml = document.getElementById("xml_novo").value;

        let url = "gravarNota.php";
		$.post(url, {numero: numero, valor: valor, dest: dest, xml: xml}, function (result) {
			console.log(result);
            switch (result) {
                case '0':
                    alert('Nota fiscal já gravada anteriormente');
                    break;
                case '1':
                    alert('Nota fiscal gravada com sucesso');
                    break;
                case '-1':
                    alert('Erro ao gravar nota fiscal');
                    break;
            }
        });
    }
    </script>
</head>

<body>
    <?php
        $conexao = mysqli_connect("localhost", "root", ""); //localhost  onde esta o banco de dados.
        $banco = mysqli_select_db($conexao , "notasFiscais");
    ?>
    <div class="formulario">
        <!--Titulo do Formulário-->
        <h1>Importar dados do arquivo XML</h1>
        <!--Formulário com PHP para fazer upload de arquivo com PHP-->
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data"
            onSubmit="return validaArquivo();">
            <label>Arquivo</label>
            <!--Campo para fazer o upload do arquivo com PHP-->
            <input type="file" name="arquivo" id="arquivo" accept=".xml"><br><br>
            <button type="submit">Importar</button>
        </form>
    </div>

    <div class="formulario">
        <!--Titulo do Formulário-->
        <h1>Procurar notas importadas</h1>
        <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <select name="filtro_tipo">
                <option value="0" selected>CNPJ/CPF</option>
                <option value="1">Numero da nota</option>
                <option value="2">Valor da nota</option>
            </select>
            <input name="filtro_conteudo" type="text" required></input>
            <button type="submit">Pesquisar</button>
        </form>
    </div>

    <div class="notas">
        <?php
         if (isset($_FILES['arquivo'])) {
            $xml = simplexml_load_file($_FILES['arquivo']['tmp_name']);
         }

         if (isset($_POST['filtro_tipo'])) {
            switch ($_POST['filtro_tipo']) {
                case 0:
                    $query = "select * from notas where destinatario = '".$_POST['filtro_conteudo']."'";
                    break;
                case 1:
                    $query = "select * from notas where numero = ".$_POST['filtro_conteudo'];
                    break;
                case 2:
                    $query = "select * from notas where valor = ".$_POST['filtro_conteudo'];
                    break;
            }

            $results= mysqli_query($conexao, $query);

            if($results->num_rows === 0){
            ?>
        <div class="mensagem">
            <h2>Sem resultados para sua pesquisa!</h2>
        </div>
        <?php
                exit;
            }
            while ($sql = mysqli_fetch_array($results)) {
            ?>
        <form class="resultado" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
            <div><?= $sql["numero"]  ?></div>
            <div><?= $sql["destinatario"]  ?></div>
            <div><?= $sql["valor"]  ?></div>
            <input type="hidden" name="xml" value='<?= $sql["xml"] ?>'>
            <div><button type="submit">Visualizar</button></div>
        </form>
        <?php
            }
         }

         if (isset($_POST['xml'])) {
            $xml = simplexml_load_string($_POST['xml']);
         }

         if (isset($_FILES['arquivo']) || isset($_POST['xml'])){ 
            if (!$xml) {
                echo "Erro ao abrir arquivo!";
                exit;
            }
            if (is_null($xml->NFe->infNFe)) {
                echo 'xml invalido';
                exit;
            }
            $cnpj = $xml->NFe->infNFe->emit->CNPJ;
            /*if ($cnpj <> '09066241000884') {
                echo 'CNPJ nao pertence ao emitente 09.066.241/0008-84';
                exit;
            }*/
            if (is_null($xml->protNFe->infProt)) {
                echo 'xml invalido';
                exit;
            } else {
        ?>
        <form onsubmit="gravarNotas(event)">
            <div class="notas_conteudo">
                <div class="THead_NFe ">
                    Dados da nota
                </div>

                <div class="rowTP01">
                    <div class="wID15">
                        <label>
                            Modelo
                        </label>
                        <p><?= $xml->NFe->infNFe->ide->mod ?></p>
                    </div>
                    <div class="wID15 pID_R10">
                        <label>
                            Série
                        </label>
                        <p><?= $xml->NFe->infNFe->ide->serie ?></p>
                    </div>
                    <div class="wID20 ">
                        <label>
                            Número
                        </label>
                        <p><?= $xml->NFe->infNFe->ide->nNF ?></p>
                    </div>
                    <div class="wID50">
                        <label>
                            Data/Hora da emissão
                        </label>
                        <p><?= date_format(date_create($xml->NFe->infNFe->ide->dhEmi),"d/m/Y H:i:s") ?></p>
                    </div>
                </div>

                <div class="THead_NFe ">
                    Emitente
                </div>

                <div class="rowTP01">
                    <div class="wID15">
                        <label>
                            CNPJ
                        </label>
                        <p><?= $xml->NFe->infNFe->emit->CNPJ ?></p>
                    </div>
                    <div class="wID15 pID_R10">
                        <label>
                            IE
                        </label>
                        <p><?= $xml->NFe->infNFe->emit->IE ?></p>
                    </div>
                    <div class="wID50">
                        <label>
                            Nome/Razão Social
                        </label>
                        <p><?= $xml->NFe->infNFe->emit->xNome ?></p>
                    </div>
                </div>

                <div class="THead_NFe ">
                    Destinatário
                </div>

                <div class="rowTP01">
                    <?php
                        if(is_null($xml->NFe->infNFe->dest->CPF)){
                        ?>
                    <div class="wID15">
                        <label>
                            CNPJ
                        </label>
                        <p><?= $xml->NFe->infNFe->dest->CNPJ ?></p>
                    </div>
                    <div class="wID15 pID_R10">
                        <label>
                            IE
                        </label>
                        <p><?= $xml->NFe->infNFe->dest->IE ?></p>
                    </div>
                    <?php
                        }else{
                        ?>
                    <div class="wID15">
                        <label>
                            CPF
                        </label>
                        <p><?= $xml->NFe->infNFe->dest->CPF ?></p>
                    </div>
                    <?php
                        }
                        ?>

                    <div class="wID50">
                        <label>
                            Nome/Razão Social
                        </label>
                        <p><?= $xml->NFe->infNFe->dest->xNome ?></p>
                    </div>
                </div>

                <div class="THead_NFe ">
                    Valores totais da nota
                </div>

                <div class="rowTP01">
                    <div class="wID15">
                        <label>
                            Valor de Base de Calculo
                        </label>
                        <p><?= $xml->NFe->infNFe->total->ICMSTot->vBC ?></p>
                    </div>
                    <div class="wID15">
                        <label>
                            Valor do ICMS
                        </label>
                        <p><?= $xml->NFe->infNFe->total->ICMSTot->vICMS  ?></p>
                    </div>
                    <div class="wID15 ">
                        <label>
                            Valor dos Produtos
                        </label>
                        <p><?= $xml->NFe->infNFe->total->ICMSTot->vProd  ?></p>
                    </div>
                    <div class="wID15">
                        <label>
                            Valor do frete
                        </label>
                        <p><?= $xml->NFe->infNFe->total->ICMSTot->vFrete ?></p>
                    </div>
                    <div class="wID15">
                        <label>
                            Valor total da nota
                        </label>
                        <p><?= $xml->NFe->infNFe->total->ICMSTot->vNF  ?></p>
                    </div>
                    <input type="hidden" name="numero" id="numero" value='<?= $xml->NFe->infNFe->ide->nNF ?>'>
                    <input type="hidden" name="valor" id="valor" value='<?= $xml->NFe->infNFe->total->ICMSTot->vNF ?>'>
                    <?php
                        if(is_null($xml->NFe->infNFe->dest->CPF)){
                        ?>
                    <input type="hidden" name="dest" id="dest" value='<?= $xml->NFe->infNFe->dest->CNPJ ?>'>
                    <?php
                        }else{
                        ?>
                    <input type="hidden" name="dest" id="dest" value='<?= $xml->NFe->infNFe->dest->CPF ?>'>
                    <?php
                        }
                        ?>
                </div>
                <?php
                    if (isset($_FILES['arquivo'])) {
                    ?>
                <input type="hidden" name="xml_novo" id="xml_novo" value='<?= file_get_contents($_FILES['arquivo']['tmp_name']) ?>'>
                <div class="gravar"><button type="submit">Gravar</button></div>
                <?php
                    }
                    ?>
        </form>
    </div>

    <?php
            }
        }
        ?>
    </div>
</body>

</html>