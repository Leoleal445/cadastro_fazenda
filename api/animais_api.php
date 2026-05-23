<?php
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$acao = $_REQUEST['acao'] ?? '';
$pdo  = getConnection();

match($acao) {
  'listar'  => listar($pdo),
  'salvar'  => salvar($pdo),
  'excluir' => excluir($pdo),
  default   => json_encode(['erro' => 'Ação inválida'])
};
function listar(PDO $pdo): void {
  $page     = (int)($_GET['page'] ?? 1);
  $perPage  = 10;
  $offset   = ($page - 1) * $perPage;
  $brinco   = $_GET['brinco'] ?? '';
  $sexo     = $_GET['sexo']   ?? '';

  $where = ['situacao = 1'];
  $params = [];

  if ($brinco !== '') {
    $where[]  = 'brinco LIKE :brinco';
    $params[':brinco'] = '%' . $brinco . '%';
  }
  if (in_array($sexo, ['Macho', 'Fêmea'])) {
    $where[]  = 'sexo = :sexo';
    $params[':sexo'] = $sexo;
  }

  $sql  = "SELECT * FROM animais_teste WHERE "
       . implode(' AND ', $where)
       . " ORDER BY id DESC LIMIT :limit OFFSET :offset";

  $stmt = $pdo->prepare($sql);
  foreach ($params as $k => $v) { $stmt->bindValue($k, $v); }
  $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
  $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
  $stmt->execute();
  $animais = $stmt->fetchAll();

  // Total de registros para paginação
  $sqlCount = "SELECT COUNT(*) FROM animais_teste WHERE "
             . implode(' AND ', $where);
  $stmtC = $pdo->prepare($sqlCount);
  foreach ($params as $k => $v) { $stmtC->bindValue($k, $v); }
  $stmtC->execute();
  $total = (int)$stmtC->fetchColumn();

  echo json_encode([
    'animais'      => $animais,
    'total'        => $total,
    'pagina_atual' => $page,
    'total_paginas'=> (int)ceil($total / $perPage),
  ]);
}
function salvar(PDO $pdo): void {
  $id        = (int)($_POST['id']        ?? 0);
  $fazenda   = (int) $_POST['fazenda_id'];
  $brinco    = trim($_POST['brinco']    ?? '');
  $sexo      = trim($_POST['sexo']      ?? '');
  $raca      = trim($_POST['raca']      ?? '');
  $peso      = (float)($_POST['peso']    ?? 0);

  // Validação mínima
  if (!$brinco || !in_array($sexo, ['Macho', 'Fêmea']) || $peso <= 0) {
    echo json_encode(['erro' => 'Dados inválidos']); return;
  }

  if ($id > 0) {
    // Atualiza
    $stmt = $pdo->prepare("UPDATE animais_teste
      SET fazenda_id=:f, brinco=:b, sexo=:s, raca=:r, peso=:p
      WHERE id=:id AND situacao=1");
    $stmt->execute([':f'=>$fazenda, ':b'=>$brinco, ':s'=>$sexo,
                    ':r'=>$raca,   ':p'=>$peso,   ':id'=>$id]);
    echo json_encode(['ok' => 'Animal atualizado', 'id' => $id]);
  } else {
    // Insere
    $stmt = $pdo->prepare("INSERT INTO animais_teste
      (fazenda_id, brinco, sexo, raca, peso)
      VALUES (:f, :b, :s, :r, :p)");
    $stmt->execute([':f'=>$fazenda, ':b'=>$brinco,
                    ':s'=>$sexo,    ':r'=>$raca, ':p'=>$peso]);
    echo json_encode(['ok' => 'Animal cadastrado', 'id' => $pdo->lastInsertId()]);
  }
}

function excluir(PDO $pdo): void {
  $id = (int)($_POST['id'] ?? 0);
  if ($id <= 0) {
    echo json_encode(['erro' => 'ID inválido']); return;
  }
  // Exclusão lógica: apenas muda situacao para 0
  $stmt = $pdo->prepare("UPDATE animais_teste SET situacao=0 WHERE id=:id");
  $stmt->execute([':id' => $id]);
  echo json_encode(['ok' => 'Animal excluído']);
}
