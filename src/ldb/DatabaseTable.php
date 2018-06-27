<?php
namespace manguto\ldb;

class DatabaseTable
{

    public $name;

    public $identifierName;

    public $columns = array();

    public $lastIndex = 0;

    public $entries = array();

    // ----------------------------------------------------------------------------------------------------------------------------------
    public function __construct(string $name)
    {
        $this->name = strtolower($name);
        
        $this->identifierName = $this->name . 'id';
    }

    private function entry_get(int $identifierValue, bool $throwException = true): Model
    {
        if (isset($this->entries[$identifierValue])) {
            return $this->entries[$identifierValue];
        } else {
            if ($throwException) {
                throw new \Exception("Registro do(a) '" . $this->name . "' não encontrado(a) ($identifierValue).");
            } else {
                return false;
            }
        }
    }

    public function entry_insert_update(Model &$model)
    {
        if ($model->getIdentifier() == 0) {
            $model->setIdentifier(++ $this->lastIndex);
        } else {
            if (! isset($this->entries[$model->getIdentifier()])) {
                throw new \Exception("Registro '" . $model->getModelName() . "' não encontrado (" . $model->getIdentifier() . ").");
            }
        }
        $this->columns_update($model);
        $this->entries[$model->getIdentifier()] = $model;
    }

    public function entry_delete(Model &$model)
    {
        unset($this->entries[$model->getIdentifier()]);
        
        $model->setIdentifier(0);
    }

    /**
     * Atualiza a informacao das colunas da tabela, bem como de todos os registros da mesma com base no MODELO passado
     * - $model => table[columns]
     *
     * Verifica se todos os registros da tabela possuem a coluna em questao e caso contrário, insere-a com o valor padrao da mesma
     * - $model => table[entries]
     *
     * Atualiza o modelo passado com as colunas da tabela a qual este está vinculado
     * - table[columns] => $model
     * 
     * @param Model $model
     * @return \manguto\ldb\Model
     */
    public function columns_update(Model $model)
    {
        // atualiza a informacao das colunas desta tabela, bem como de todos os registros da mesma com base no MODELO passado
        $modelData = $model->getData();
        foreach ($modelData as $columnName => $columnValue) {
            if (! isset($this->columns[$columnName])) {
                $this->column_update($columnName);
                
                // verifica se todos os registros da tabela possuem a coluna em questao e caso contrário, insere-a com o valor padrao da mesma
                foreach ($this->entries as &$entry) {
                    $entryData = $entry->getData();
                    if (! isset($entryData[$columnName])) {
                        $entry->{'set' . $columnName}($this->columns[$columnName]->default);
                    }
                }
            }
        }        
        // atualiza o modelo passado com as colunas da tabela a qual este está vinculado
        $modelData = $model->getData();
        foreach ($this->columns as $column) {
            if (! isset($modelData[$column->name])) {
                $model->{'set' . $column->name}($column->default);
            }
        }        
        return $model;
    }

    /**
     * atualiza/adiciona coluna nos registros da tabela em questao
     * com base nas informacoes passadas (updates)
     *
     * @param string $columnName
     * @param array $updates
     */
    public function column_update(string $columnName, array $updates = [])
    {
        if (! isset($this->columns[$columnName])) {
            $this->columns[$columnName] = new DatabaseColumn($columnName);
        }
        foreach ($updates as $columnNameTemp => $columnValueTemp) {
            $this->columns[$columnName]->$columnNameTemp = $columnValueTemp;
        }
    }

    // ########################################################################################### MANAGER
    // ########################################################################################### MANAGER
    // ########################################################################################### MANAGER
    public function Manager(string $style = '')
    {
        $return = array();
        
        // tablename
        $t = $this->name;
        
        // html javascript
        $return[] = "<script>

            function {$t}SetTarget(value){
                $('#$t input[name=\"target\"]').val(value);
            }
            function {$t}SetOption(value){
                $('#$t input[name=\"option\"]').val(value);
            }
            function {$t}SetParameter(value){
                $('#$t input[name=\"parameter\"]').val(value);
            }
            function {$t}Submit(){    
                $('#$t').submit();
            }
            
            //--------------------------------------------- table entry control
            function {$t}_entry_add(){    
                {$t}SetTarget('entry');
                {$t}SetOption('add');
                {$t}SetParameter(0);
                {$t}Submit();
            }
            function {$t}_entry_edit(id){    
                {$t}SetTarget('entry');
                {$t}SetOption('edit');
                {$t}SetParameter(id);
                {$t}Submit();
            }
            function {$t}_entry_delete(id){
                if(confirm('Deseja realmente excluir este(a) \"$t\"? [identificador='+id+']')){
                    {$t}SetTarget('entry');
                    {$t}SetOption('delete');
                    {$t}SetParameter(id);
                    {$t}Submit();
                }
            }
            //--------------------------------------------- table info control
            function {$t}_info_add(){
                var infoname = prompt('Digite o nome da nova coluna:');

                if (infoname != null) {
                    {$t}SetTarget('info');
                    {$t}SetOption('add');
                    {$t}SetParameter(infoname);
                    {$t}Submit();
                }
            }
            function {$t}_info_edit(infoname){
                {$t}SetTarget('info');
                {$t}SetOption('edit');
                {$t}SetParameter(infoname);
                {$t}Submit();            
            }
            function {$t}_info_save(infoname){
                {$t}SetTarget('info');
                {$t}SetOption('save');
                {$t}SetParameter(infoname);

                $('tr#{$t}_edit input').each(function(){
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    $('form#$t').append('<input type=\"hidden\" name=\"'+name+'\" value=\"'+value+'\">');
                });

                {$t}Submit();
            }
            function {$t}_info_delete(infoname){
                if(confirm('Deseja realmente excluir este o parametro \"'+infoname+'\" da tabela \"$t\"?')){
                    {$t}SetTarget('info');
                    {$t}SetOption('delete');
                    {$t}SetParameter(infoname);
                    {$t}Submit();
                }
            }
            //--------------------------------------------- table control
            function {$t}_delete(){
                if(confirm('CUIDADO!!!! Tem certeza que deseja excluir a tabela \"$t\"?')){
                    {$t}SetTarget('table');
                    {$t}SetOption('delete');
                    {$t}SetParameter('');
                    {$t}Submit();
                }
            }
            
            $(document).ready(function(){
            
            });
            
            </script>";
        
        { // ======================================================== html form options control
          
            // form action
            $action = Database::form_action();
            
            $return[] = "<form id='$t' method='post' action='$action'>";
            $return[] = "<input type='hidden' name='target' value=''>";
            $return[] = "<input type='hidden' name='option' value=''>";
            $return[] = "<input type='hidden' name='parameter' value=''>";
            $return[] = "</form>";
        }
        
        $return[] = "<table style='$style overflow:hidden; font-size:11px;' class='table table-sm w-100'>";
        
        $return[] = "<tr><td>";
        $return[] = "<div class='h4 float-left w-50'>" . $this->name . "</div>";
        $return[] = "<div class='h4 float-right w-25'>";
        $return[] = "<button onclick='{$t}_entry_add()' class='btn btn-success btn-sm float-right m-1'title='Adicionar Registro'>";
        $return[] = "<img src='/res/general/images/icons/plus-2x.png'/>";
        $return[] = "</button>";
        $return[] = "</div>";
        $return[] = "</td></tr>";
        
        { // ############################################################################################################################# content
            $content = array();
            $content[0] = "<table style='$style' class='table table-striped database_scenario'>";
            $columns = array();
            if (sizeof($this->entries) > 0) {
                
                foreach ($this->entries as $entry) {
                    
                    if (false)
                        $entry = new Model();
                    if (sizeof($content) == 1) {
                        $content[] = '<thead class="thead-dark">';
                        $content[] = '<tr>';
                        foreach ($entry->getData() as $k => $v) {
                            $columns[] = $k;
                            $content[] = "<th scope='col' class='text-monospace font-weight-normal'>$k</th>";
                        }
                        $content[] = "<th scope='col' class='text-monospace font-weight-normal' title='Opções'>Opções</th>";
                        $content[] = '</tr>';
                        $content[] = '</thead>';
                        $content[] = '<tbody>';
                    }
                    
                    $content[] = '<tr>';
                    foreach ($columns as $columnName) {
                        $content[] = "<td title='$columnName' scope='row' class='text-monospace'>" . $entry->{'get' . $columnName}() . "</td>";
                    }
                    { // opcoes
                      // deb($action,0);
                        $content[] = "<td scope='row' class='text-monospace' style='font-size:10px;'>";
                        $content[] = "<button onclick='{$t}_entry_delete(" . $entry->getIdentifier() . ")' class='btn btn-danger btn-sm float-right m-1' title='Excluir Registro' style='font-size:10px;'>";
                        $content[] = "<img src='/res/general/images/icons/trash-2x.png'/>";
                        $content[] = "</button>";
                        $content[] = "<button onclick='{$t}_entry_edit(" . $entry->getIdentifier() . ")' class='btn btn-warning btn-sm float-right m-1' title='Editar Registro' style='font-size:10px;'>";
                        $content[] = "<img src='/res/general/images/icons/pencil-2x.png'/>";
                        $content[] = "</button>";
                        $content[] = "</td>";
                    }
                    
                    $content[] = '</tr>';
                }
            } else {
                $content[] = '<tr><td>Nenhum registro cadastrado.</td></tr>';
            }
            $content[] = '</tbody>';
            $content[] = '</table>';
        }
        
        { // ########################################################################################################################### info
            $info = array();
            
            if (isset($_POST['target']) && $_POST['target'] == 'info' && $_POST['option'] == 'edit' && $_POST['parameter'] != '') {
                $editionMode = true;
            } else {
                $editionMode = false;
            }
            
            $unicTerm = microtime();
            $unicTerm = str_replace('.', '', $unicTerm);
            $unicTerm = str_replace(' ', '', $unicTerm);
            $table_info_id = $this->name . '_info_' . $unicTerm;
            
            $columnQuant = sizeof($this->columns);
            
            $info[] = "<a href='javascript:$(\"#$table_info_id\").toggle();' class='btn btn-light btn-sm float-right m-1' style='font-size:10px;'>Informações</a>";
            
            { // exibicao quando da edicao
                if ($editionMode) {
                    $display = 'block';
                } else {
                    $display = 'none';
                }
            }
            
            $info[] = "<table id='$table_info_id' style='display:$display; $style' class='table database_scenario table-bordered table-striped mt-4 w-50'>";
            $info[] = "<thead>";
            $info[] = "<tr>";
            $info[] = "<td scope='row' colspan='4'>";
            $info[] = "<button onclick='{$t}_info_add()' class='btn btn-success btn-sm float-right m-1' title='Adicionar Coluna'>";
            $info[] = "<img src='/res/general/images/icons/plus.png'/>";
            $info[] = "</button>";
            $info[] = "</td>";
            $info[] = "</tr>";
            
            $sizeInfoHead = sizeof($info);
            if ($columnQuant == 0) {
                $info[] = "</thead>";
                $info[] = '<tr><td class="text-monospace">Nenhuma coluna cadastrada.</td></tr>';
                $columnQuant = 1;
            } else {
                
                { // --- verificacao de edicao de informacao
                    if ($editionMode) {
                        $columnAttrValueEdit = trim($_POST['parameter']);
                    } else {
                        $columnAttrValueEdit = false;
                    }
                }
                
                foreach ($this->columns as $column) {
                    if (sizeof($info) == $sizeInfoHead) {
                        $info[] = "<tr>";
                        foreach ($column as $columnAttrName => $columnAttrValue) {
                            $info[] = "<th scope='col' class='text-monospace'>$columnAttrName</th>";
                        }
                        $info[] = "<th scope='col' class='text-monospace'>Opc</th>";
                        $info[] = "</tr>";
                        $info[] = "</thead>";
                        $info[] = "<tbody>";
                    }
                    
                    // if(false)$column = new DatabaseColumn($name);
                    if ($column->name == $columnAttrValueEdit) {
                        $edit = true;
                        $info[] = "<tr id='{$t}_edit'>";
                    } else {
                        $edit = false;
                        $info[] = "<tr>";
                    }
                    
                    foreach ($column as $columnAttrName => $columnAttrValue) {
                        
                        if ($edit) {
                            $info[] = "<td scope='row' class='text-monospace' title='$columnAttrName'>";
                            $info[] = "<input type='text' name='$columnAttrName' value='$columnAttrValue'>";
                            $info[] = "</td>";
                        } else {
                            $info[] = "<td scope='row' class='text-monospace' title='$columnAttrName'>$columnAttrValue</td>";
                        }
                    }
                    
                    $info[] = "<td scope='row' class='text-monospace text-right'>";
                    
                    if ($edit) {
                        $info[] = "<button onclick='{$t}_info_save(\"" . $column->name . "\")' class='btn btn-success btn-sm m-1' title='Salvar Coluna'><img src='/res/general/images/icons/circle-check.png'/></button>";
                    } else {
                        $info[] = "<button onclick='{$t}_info_edit(\"" . $column->name . "\")' class='btn btn-warning btn-sm m-1' title='Editar Coluna'><img src='/res/general/images/icons/pencil.png'/></button>";
                    }
                    $info[] = "<button onclick='{$t}_info_delete(\"" . $column->name . "\")' class='btn btn-danger btn-sm m-1' title='Excluir Coluna'><img src='/res/general/images/icons/trash.png'/></button>";
                    $info[] = "</td>";
                    
                    if ($edit) {
                        $info[] = "</form>";
                    }
                    
                    $info[] = "</tr>";
                }
            }
            
            $info[] = "<tr><td scope='row' colspan='4' class='text-right'>Índice: " . $this->lastIndex . "</td></tr>";
            $info[] = "<tr><td scope='row' colspan='4' class='text-right'><button onclick='{$t}_delete()' title='EXCLUIR TABELA' class='btn btn-danger btn-sm' style='font-size:9px;'><img src='/res/general/images/icons/warning-2x.png'/> EXCLUIR TABELA</td></tr>";
            $info[] = "</tbody>";
            $info[] = "</table>";
        }
        // content
        $return[] = "<tr><td>" . implode(chr(10), $content) . "</td></tr>";
        // info
        $return[] = "<tr><td>" . implode(chr(10), $info) . "</td></tr>";
        
        $return[] = "</table>";
        
        return implode(chr(10), $return);
    }
}

?>  