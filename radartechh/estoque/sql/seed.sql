-- =============================================================
-- SEED: Dados de exemplo para a papelaria
-- Execute APÓS o schema.sql
-- =============================================================

USE `estoque_papelaria`;

-- -------------------------------------------------------------
-- Usuários iniciais
-- Senha do admin: Admin@1234  (trocar no primeiro acesso!)
-- Senha do vendedor: Vendedor@1 (trocar no primeiro acesso!)
-- Hashes gerados com password_hash($senha, PASSWORD_BCRYPT)
-- -------------------------------------------------------------
INSERT INTO `usuarios` (`nome`, `login`, `senha_hash`, `perfil`) VALUES
('Administrador',  'admin',    '$2y$10$DcfMbnHnnGBoWWjfO//R3.72zGJO0T4QPEq2ntofBe4ltwKcJvxTy', 'admin'),
('Maria Vendedora','vendedor', '$2y$10$lQ4D2/FhF7rRUJJVq3sQBO/zVbIQAVJP2YgLXNdSXMYPDMZhmjlIS', 'vendedor');

-- -------------------------------------------------------------
-- Categorias
-- -------------------------------------------------------------
INSERT INTO `categorias` (`nome`) VALUES
('Canetas e Marcadores'),
('Cadernos e Blocos'),
('Papéis e Folhas'),
('Colas e Adesivos'),
('Réguas e Instrumentos'),
('Borrachas e Corretivos'),
('Mochilas e Pastas'),
('Tintas e Pincéis'),
('Materiais para Escritório'),
('Didáticos e Livros');

-- -------------------------------------------------------------
-- Fornecedores
-- -------------------------------------------------------------
INSERT INTO `fornecedores` (`nome`, `telefone`, `email`) VALUES
('Distribuidora Escolar Ltda',  '(11) 3000-1000', 'vendas@distribescolar.com.br'),
('Papéis & Cia Distribuidora',  '(11) 3000-2000', 'contato@papeisecia.com.br'),
('BIC Papelaria Distribuidor',  '(11) 3000-3000', 'distribui@bic.com.br'),
('Master Atacado Papelaria',    '(21) 3000-4000', 'atacado@masterpap.com.br');

-- -------------------------------------------------------------
-- Produtos de exemplo
-- -------------------------------------------------------------
INSERT INTO `produtos` (`nome`, `codigo_barras`, `categoria_id`, `marca`, `preco_custo`, `preco_venda`, `estoque_atual`, `estoque_minimo`) VALUES
('Caneta Esferográfica Azul 1.0mm',    '7891010000001',  1, 'BIC',        0.45, 1.50,  80,  20),
('Caneta Esferográfica Vermelha 1.0mm','7891010000002',  1, 'BIC',        0.45, 1.50,  60,  20),
('Caneta Esferográfica Preta 1.0mm',   '7891010000003',  1, 'BIC',        0.45, 1.50,  70,  20),
('Caneta Hidrográfica 12 Cores',       '7891010000004',  1, 'Faber-Castell', 5.20, 14.90, 25, 10),
('Marcador Permanente Preto',          '7891010000005',  1, 'Pilot',      2.10,  5.90,  3,  10),
('Caderno Espiral 200 Folhas',         '7891010000006',  2, 'Foroni',     8.50, 22.90, 40,  15),
('Caderno Universitário 100 Folhas',   '7891010000007',  2, 'São Domingos',4.30,11.90, 55,  20),
('Bloco de Rascunho A4',               '7891010000008',  2, 'Spiral',     1.80,  4.50, 30,  10),
('Papel Sulfite A4 500 Folhas',        '7891010000009',  3, 'Report',    12.00, 28.90, 15,   8),
('Papel Color Plus A4 (pacote 50fls)', '7891010000010',  3, 'Color Plus', 7.50, 18.90, 10,   5),
('Cola Bastão 40g',                    '7891010000011',  4, 'UHU',        2.80,  6.90, 45,  15),
('Cola Branca 90g',                    '7891010000012',  4, 'Cascorez',   1.90,  4.90,  2,  10),
('Fita Adesiva 18mm x 30m',           '7891010000013',  4, '3M',         1.40,  3.90, 20,  10),
('Régua 30cm',                         '7891010000014',  5, 'Acrimet',    1.20,  3.50, 18,   8),
('Compasso Escolar',                   '7891010000015',  5, 'Maped',      4.50, 11.90,  7,   5),
('Borracha Branca',                    '7891010000016',  6, 'Faber-Castell', 0.35, 1.20, 90, 30),
('Corretivo Líquido 18ml',             '7891010000017',  6, 'BIC',        1.80,  4.50, 12,  10),
('Apontador com Depósito',             '7891010000018',  6, 'Faber-Castell', 0.65, 1.90, 35, 15),
('Lápis Preto HB (caixa 12un)',        '7891010000019',  1, 'Faber-Castell', 3.20, 8.90, 22, 10),
('Giz de Cera 12 Cores',               '7891010000020',  8, 'Acrilex',    3.50,  9.90, 16,   8);

-- -------------------------------------------------------------
-- Movimentações iniciais de exemplo (simulam histórico)
-- -------------------------------------------------------------
INSERT INTO `movimentacoes` (`produto_id`, `tipo`, `quantidade`, `motivo`, `custo_unitario`, `fornecedor_id`, `usuario_id`, `observacao`) VALUES
(1,  'entrada', 100, 'Compra',       0.45, 1, 1, 'Pedido inicial de inauguração'),
(2,  'entrada',  80, 'Compra',       0.45, 1, 1, 'Pedido inicial de inauguração'),
(3,  'entrada',  80, 'Compra',       0.45, 1, 1, 'Pedido inicial de inauguração'),
(6,  'entrada',  50, 'Compra',       8.50, 2, 1, 'Pedido inicial'),
(7,  'entrada',  70, 'Compra',       4.30, 2, 1, 'Pedido inicial'),
(9,  'entrada',  20, 'Compra',      12.00, 2, 1, 'Pedido inicial'),
(16, 'entrada', 100, 'Compra',       0.35, 3, 1, 'Pedido inicial'),
(1,  'saida',    20, 'Venda',        NULL, NULL, 2, NULL),
(2,  'saida',    20, 'Venda',        NULL, NULL, 2, NULL),
(3,  'saida',    10, 'Venda',        NULL, NULL, 2, NULL),
(6,  'saida',    10, 'Venda',        NULL, NULL, 2, NULL),
(9,  'saida',     5, 'Venda',        NULL, NULL, 2, NULL),
(16, 'saida',    10, 'Venda',        NULL, NULL, 2, NULL),
(12, 'saida',     3, 'Perda',        NULL, NULL, 1, 'Produto com embalagem danificada'),
(5,  'saida',     2, 'Uso interno',  NULL, NULL, 1, 'Uso no escritório');
