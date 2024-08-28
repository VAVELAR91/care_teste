# care_teste

Peojeto feito com PHP, HTML, Js, CSS e Mysql

## 🛠️ Banco de dados

Utilizando Mysql

- Banco NotasFiscais

- Tabela notas
  ```
  CREATE TABLE `notas` (
    `id` int(11) PRIMARY KEY,
    `numero` int(11) NOT NULL,
    `destinatario` varchar(20) NOT NULL,
    `valor` double NOT NULL,
    `xml` text NOT NULL
    )
  ```

## ⚙️ Executando

O sistema tem uma unica tela, onde o usuario seleciona o arquivo xml e importa

Após importar, ele tem a opção de gravar a nota

Depois de gravada, na mesma tela tem opções para pesquisa das notas que estão gravadas
