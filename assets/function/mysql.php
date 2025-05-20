<?php
date_default_timezone_set('America/Sao_Paulo');

// Conexão com o banco de dados
$dbConect = new mysqli($dbHost, $dbUserName, $dbUserPassword, $dbName);


/** Function for select all content of first row in table, have possibility use condition
 * 
 * @param string $table name of table 
 * @param string $where_condition condition for seache, case empty select all
 * @param array|null $left_join Array with LEFT JOIN details (e.g., ['table' => 'join_table', 'on' => 'join_condition']).
 * 
 * @return mixed
 **/
function mysql_select_in_array($table, $where_condition = null, $select = "*", $left_join = null)
{
    global $dbConect;


    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        // console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }


    $query = "SELECT $select FROM $table";

    // Adiciona LEFT JOIN se fornecido
    if ($left_join !== null && is_array($left_join)) {
        $join_table = $left_join['table'];
        $join_condition = $left_join['on'];
        $query .= " LEFT JOIN $join_table ON $join_condition";
    }

    // Adiciona WHERE se fornecido
    if ($where_condition !== null) {
        $query .= " WHERE $where_condition";
    }

    if ($result = $dbConect->query($query)) {
        $returnArray = mysqli_fetch_assoc($result);
        if ($returnArray) {
            // console_log("Tudo certo");
            return $returnArray;
        } else {
            // console_log("Erro: Nenhum dado encontrado na tabela.");
            return null;
        }
    } else {
        // console_log("Erro na consulta SQL: " . $dbConect->error);
        return null;
    }
}

/**
 * Function to select all content in a table, with optional WHERE condition and LEFT JOIN support.
 *
 * @param string $select column of the table, default is * for all.
 * @param string $table Name of the table.
 * @param string|null $where_condition Condition for filtering, if empty selects all.
 * @param array|null $left_join Array with LEFT JOIN details (e.g., ['table' => 'join_table', 'on' => 'join_condition']).
 *
 * @return array|null
 */
function mysql_select_all_in_array($table, $where_condition = null, $select = "*", $left_join = null, $oderBy = null)
{
    global $dbConect;

    // Verifica se há conexão com o banco de dados
    if ($dbConect->connect_error) {
        // console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Constrói a consulta SQL
    $query = "SELECT $select FROM $table";

    // Adiciona LEFT JOIN se fornecido
    if ($left_join !== null && is_array($left_join)) {
        $join_table = $left_join['table'];
        $join_condition = $left_join['on'];
        $query .= " LEFT JOIN $join_table ON $join_condition";
    }

    // Adiciona WHERE se fornecido
    if ($where_condition !== null) {
        $query .= " WHERE $where_condition";
    }

    if ($oderBy != null) {
        $query .= " ORDER BY $oderBy";
    }

    // Executa a consulta
    if ($result = $dbConect->query($query)) {
        $returnArray = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $returnArray[] = $row;
        }
        return $returnArray;
    } else {
        // console_log("Erro na consulta SQL: " . $dbConect->error);
        return null;
    }
}

/**
 * Executa uma consulta SQL genérica com suporte para todos os tipos de comandos
 * 
 * @param string $query Consulta SQL (pode conter placeholders ?)
 * @param array $params Parâmetros para prepared statements (opcional)
 * @return mixed
 *   - Para SELECT: array de resultados ou null em caso de erro
 *   - Para INSERT: ID do último registro inserido ou false em caso de erro
 *   - Para UPDATE/DELETE: Número de linhas afetadas ou false em caso de erro
 *   - Para CREATE/ALTER/DROP: true em caso de sucesso, false em caso de erro
 *   - null em caso de erro de conexão
 */
function mysql_query($query, $params = [])
{
    global $dbConect;

    // Verifica conexão
    if ($dbConect->connect_error) {
        error_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Remove espaços extras e identifica o tipo de comando
    $query = trim($query);
    $firstWord = strtoupper(strtok($query, " "));

    // Comandos DDL (CREATE, ALTER, DROP) não usam prepared statements
    if (in_array($firstWord, ['CREATE', 'ALTER', 'DROP', 'TRUNCATE'])) {
        if ($dbConect->query($query) === true) {
            return true;
        } else {
            error_log("Erro DDL: " . $dbConect->error);
            return false;
        }
    }

    // Prepara a consulta para outros comandos
    $stmt = $dbConect->prepare($query);
    if (!$stmt) {
        error_log("Erro ao preparar query: " . $dbConect->error);
        return null;
    }

    // Bind parameters se houver
    if (!empty($params)) {
        $types = '';
        $values = [];

        foreach ($params as $param) {
            if (is_int($param)) {
                $types .= 'i';
            } elseif (is_float($param)) {
                $types .= 'd';
            } else {
                $types .= 's';
            }
            $values[] = $param;
        }

        $stmt->bind_param($types, ...$values);
    }

    // Executa a consulta
    if (!$stmt->execute()) {
        error_log("Erro na execução: " . $stmt->error);
        $stmt->close();
        return null;
    }

    // Determina o tipo de retorno
    switch ($firstWord) {
        case 'SELECT':
            $result = $stmt->get_result();
            $returnArray = [];
            while ($row = $result->fetch_assoc()) {
                $returnArray[] = $row;
            }
            $stmt->close();
            return $returnArray;

        case 'INSERT':
            $insertId = $stmt->insert_id;
            $stmt->close();
            return $insertId;

        case 'UPDATE':
        case 'DELETE':
            $affectedRows = $stmt->affected_rows;
            $stmt->close();
            return $affectedRows;

        default:
            $stmt->close();
            return true;
    }
}

function mysql_generic($query)
{
    global $dbConect;

    // Verifica se há conexão com o banco de dados
    if ($dbConect->connect_error) {
        // console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Executa a consulta
    if ($dbConect->query($query)) {
        return true;
    } else {
        // console_log("Erro na consulta SQL: " . $dbConect->error);
        return false;
    }
}


/** Function for return one value of a row in table
 * 
 * @param string $table name of table 
 * @param string $column column for return
 * @param string $column_search column for search
 * @param string $search_value value for search
 * 
 * @return string
 **/
function mysql_select_one_value($table, $column, $column_search, $search_value)
{
    global $dbConect;

    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Consulta SQL com verificação de erros
    $query = "SELECT $column FROM $table WHERE $column_search = '$search_value'";  // Certifique-se de ajustar a coluna de pesquisa (id ou outra).
    if ($result = $dbConect->query($query)) {
        $row = mysqli_fetch_assoc($result);
        if ($row) {
            return $row[$column];
        } else {
            console_log("Erro: Nenhum dado encontrado para o valor '$search_value'.");
            return null;
        }
    } else {
        console_log("Erro na consulta SQL: " . $dbConect->error);
        return null;
    }
}

/** Function for select all content in table, have possibility use condition
 * 
 * @param string $table name of table 
 * @param string $where_condiction condiction for seache, case empty select all
 * 
 * @return mixed
 **/
function mysql_select_count($table, $where_condiction = null)
{
    global $dbConect;

    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    $query = "SELECT * FROM $table" . (($where_condiction != null) ? " WHERE $where_condiction" : "");
    if ($result = $dbConect->query($query)) {
        return $result->num_rows; // Retorna o número de linhas encontradas
    } else {
        return 0;
    }
}


/** Function for insert date in table
 * 
 * @param string $table name of table 
 * @param array $data array whit column and values
 * 
 * @return string return id in sucess and null if failed
 **/
function mysql_insert($table, $data)
{
    global $dbConect;

    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        // console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Construir os campos e valores para o insert
    $columns = implode(", ", array_keys($data));
    $placeholders = implode(", ", array_fill(0, count($data), '?'));

    // Preparar a query
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $dbConect->prepare($query);

    if (!$stmt) {
        // console_log("Erro ao preparar o INSERT: " . $dbConect->error);
        return null;
    }

    // Associar os tipos de dados (s para string, i para int, etc)
    $types = str_repeat('s', count($data)); // Aqui estamos assumindo que todos os campos são strings, ajuste conforme necessário
    $stmt->bind_param($types, ...array_values($data));

    // Executar e verificar sucesso
    if ($stmt->execute()) {
        return $dbConect->insert_id; // Retorna o ID do último registro inserido
    } else {
        // console_log("Erro ao executar INSERT: " . $stmt->error);
        return null;
    }
}



/** Function for updata dates in table
 * 
 * @param string $table name of table 
 * @param array $data array whit column and values
 * @param string $column_search column for search
 * @param string $search_value value for search
 * 
 * @return string return id
 **/
function mysql_update($table, $data, $column_search, $search_value)
{
    global $dbConect;

    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        return null;
    }

    // Construir a query de seleção para verificar os valores atuais
    $select_query = "SELECT * FROM $table WHERE $column_search = ?";
    $stmt_select = $dbConect->prepare($select_query);

    if (!$stmt_select) {
        return null;
    }

    $stmt_select->bind_param("s", $search_value);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    $current_data = $result->fetch_assoc();
    $stmt_select->close();

    // Se não encontrou registro, retorna erro
    if (!$current_data) {
        return null;
    }

    // Verifica se os dados novos são exatamente iguais aos atuais
    $is_same = true;
    foreach ($data as $key => $value) {
        if ($current_data[$key] != $value) {
            $is_same = false;
            break;
        }
    }

    // Se os dados são idênticos, retorna 1
    if ($is_same) {
        return 1;
    }

    // Construir os campos para o UPDATE
    $set_clause = implode(" = ?, ", array_keys($data)) . " = ?";

    // Preparar a query de UPDATE
    $query = "UPDATE $table SET $set_clause WHERE $column_search = ?";
    $stmt = $dbConect->prepare($query);

    if (!$stmt) {
        return null;
    }

    // Associar os tipos de dados e valores (assumindo que tudo é string)
    $types = str_repeat('s', count($data)) . 's';
    $values = array_merge(array_values($data), [$search_value]);
    $stmt->bind_param($types, ...$values);

    // Executar e verificar sucesso
    if ($stmt->execute()) {
        return $stmt->affected_rows > 0 ? $stmt->affected_rows : 1; // Se não houve mudança, retorna 1
    } else {
        return null;
    }
}



/** Function for delete a row of table
 * 
 * @param string $table name of table 
 * @param string $column_search column for search
 * @param string $search_value value for search
 * 
 * @return string return id
 **/
function mysql_delete($table, $column_search, $search_value)
{
    global $dbConect;

    // Verificar se a conexão foi bem-sucedida
    if ($dbConect->connect_error) {
        console_log("Erro de conexão: " . $dbConect->connect_error);
        return null;
    }

    // Preparar a query
    $query = "DELETE FROM $table WHERE $column_search = ?";
    $stmt = $dbConect->prepare($query);

    if (!$stmt) {
        console_log("Erro ao preparar o DELETE: " . $dbConect->error);
        return null;
    }

    // Associar os tipos de dados (s para string, i para int, etc)
    $stmt->bind_param('s', $search_value);

    // Executar e verificar sucesso
    if ($stmt->execute()) {
        return $stmt->affected_rows; // Retorna o número de linhas deletadas
    } else {
        console_log("Erro ao executar DELETE: " . $stmt->error);
        return null;
    }
}

// Função para exibir logs no console do navegador
function console_log($text)
{
?>
    <script>
        console.log(<?php echo json_encode($text); ?>);
    </script>
<?php
}
