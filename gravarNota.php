<?php
$conexao = mysqli_connect("localhost", "root", ""); //localhost  onde esta o banco de dados.
$banco = mysqli_select_db($conexao , "notasFiscais");

$numero = $_POST["numero"];
$dest = $_POST["dest"];
$valor = $_POST["valor"];
$xml = $_POST["xml"];

$query = "select * from notas where numero=$numero and destinatario='$dest' ";
$results= mysqli_query($conexao, $query);

if($results->num_rows > 0){
    echo 0;
    return;
}

$query = "insert into notas (numero,destinatario,valor,xml) values ($numero , '$dest' , $valor , '$xml')";
$results =  mysqli_query($conexao,$query);

if(!$results){
    echo -1;
}else{
    echo 1;
}