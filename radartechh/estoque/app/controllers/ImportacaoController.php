<?php
/**
 * ImportacaoController.php
 * Importação em lote de produtos via arquivo CSV.
 * Valida linha a linha e gera relatório de erros.
 * Colunas esperadas: nome;codigo_barras;categoria;marca;preco_custo;preco_venda;estoque_atual;estoque_minimo
 */

require_once dirname(__DIR__) . '/models/ProdutoModel.php';
require_once dirname(__DIR__) . '/models/CategoriaModel.php';

class ImportacaoController
{
    private ProdutoModel   $modelProduto;
    private CategoriaModel $modelCategoria;

    public function __construct()
    {
        exigirAdmin();
        $this->modelProduto   = new ProdutoModel();
        $this->modelCategoria = new CategoriaModel();
    }

    // GET /importacao
    public function index(?int $id = null): void
    {
        $tituloPagina = 'Importar CSV';
        $paginaAtiva  = 'importacao';
        require_once dirname(__DIR__) . '/views/importacao/index.php';
    }

    // POST /importacao/processar
    public function processar(?int $id = null): void
    {
        validarCsrf();

        // Verifica se o arquivo foi enviado
        if (empty($_FILES['arquivo']['tmp_name']) || $_FILES['arquivo']['error'] !== UPLOAD_ERR_OK) {
            flashErro('Nenhum arquivo enviado ou ocorreu um erro no upload.');
            redirecionarRota('/importacao');
        }

        $arquivo = $_FILES['arquivo'];

        // Valida extensão
        $ext = strtolower(pathinfo($arquivo['name'], PATHINFO_EXTENSION));
        if ($ext !== 'csv') {
            flashErro('Apenas arquivos CSV são aceitos.');
            redirecionarRota('/importacao');
        }

        // Valida tamanho
        if ($arquivo['size'] > UPLOAD_MAX_SIZE) {
            flashErro('O arquivo excede o tamanho máximo de 5 MB.');
            redirecionarRota('/importacao');
        }

        // Move para a pasta de uploads
        $nomeFinal  = 'import_' . date('YmdHis') . '_' . session_id() . '.csv';
        $caminhoFinal = UPLOAD_DIR . $nomeFinal;

        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoFinal)) {
            flashErro('Não foi possível salvar o arquivo. Verifique as permissões da pasta /uploads.');
            redirecionarRota('/importacao');
        }

        // Carrega o mapa de categorias (nome => id)
        $categorias     = $this->modelCategoria->listar();
        $mapaCategorias = [];
        foreach ($categorias as $cat) {
            $mapaCategorias[mb_strtolower($cat['nome'])] = $cat['id'];
        }

        // Processa o CSV
        $handle    = fopen($caminhoFinal, 'r');
        $separador = $this->detectarSeparador($caminhoFinal);
        $linha     = 0;
        $importados= 0;
        $erros     = [];

        // Ignora o cabeçalho
        fgetcsv($handle, 2000, $separador);

        while (($row = fgetcsv($handle, 2000, $separador)) !== false) {
            $linha++;

            // Ignora linhas vazias
            if (empty(array_filter($row))) continue;

            // Espera ao menos 6 colunas
            if (count($row) < 6) {
                $erros[] = "Linha $linha: número de colunas insuficiente (esperado 8, encontrado " . count($row) . ").";
                continue;
            }

            [$nome, $codigo, $categoriaNome, $marca, $custo, $venda, $estoqueAtual, $estoqueMin]
                = array_pad(array_map('trim', $row), 8, '');

            // Validações da linha
            $errosLinha = [];
            if (strlen($nome) < 2) $errosLinha[] = 'nome inválido';

            $custo = (float) str_replace(['.', ','], ['', '.'], $custo);
            $venda = (float) str_replace(['.', ','], ['', '.'], $venda);

            if ($custo  < 0) $errosLinha[] = 'preço de custo negativo';
            if ($venda  < 0) $errosLinha[] = 'preço de venda negativo';

            $estoqueAtual = (int) $estoqueAtual;
            $estoqueMin   = (int) $estoqueMin;

            if ($estoqueAtual < 0) $errosLinha[] = 'estoque atual negativo';
            if ($estoqueMin   < 0) $errosLinha[] = 'estoque mínimo negativo';

            if (!empty($errosLinha)) {
                $erros[] = "Linha $linha (" . e($nome) . "): " . implode(', ', $errosLinha) . '.';
                continue;
            }

            // Resolve a categoria pelo nome (cria se não existir)
            $categoriaId = null;
            if ($categoriaNome) {
                $chave = mb_strtolower($categoriaNome);
                if (isset($mapaCategorias[$chave])) {
                    $categoriaId = $mapaCategorias[$chave];
                } else {
                    // Cria a categoria automaticamente
                    $this->modelCategoria->criar($categoriaNome);
                    $novasCats   = $this->modelCategoria->listar();
                    foreach ($novasCats as $nc) {
                        $mapaCategorias[mb_strtolower($nc['nome'])] = $nc['id'];
                    }
                    $categoriaId = $mapaCategorias[$chave] ?? null;
                }
            }

            // Tenta criar o produto
            $resultado = $this->modelProduto->criarDoCsv([
                'nome'           => $nome,
                'codigo_barras'  => $codigo  ?: null,
                'categoria_id'   => $categoriaId,
                'marca'          => $marca   ?: null,
                'preco_custo'    => $custo,
                'preco_venda'    => $venda,
                'estoque_atual'  => $estoqueAtual,
                'estoque_minimo' => $estoqueMin,
            ]);

            if ($resultado === true) {
                $importados++;
            } else {
                $erros[] = "Linha $linha (" . e($nome) . "): $resultado";
            }
        }

        fclose($handle);

        // Remove o arquivo temporário
        @unlink($caminhoFinal);

        // Salva resultado na sessão para exibir
        $_SESSION['import_resultado'] = [
            'importados' => $importados,
            'erros'      => $erros,
            'total'      => $linha,
        ];

        redirecionarRota('/importacao');
    }

    /** Detecta o separador do CSV (ponto-e-vírgula ou vírgula). */
    private function detectarSeparador(string $arquivo): string
    {
        $linhaEx = fgets(fopen($arquivo, 'r'));
        return substr_count($linhaEx, ';') >= substr_count($linhaEx, ',') ? ';' : ',';
    }
}
