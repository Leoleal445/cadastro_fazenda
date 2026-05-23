<!DOCTYPE html>
<html lang="pt-BR"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastro de Animais</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">
</head>
<body>
<div class="container py-4">
  <h2 class="mb-4">🐄 Cadastro de Animais</h2>

  <!-- Filtros -->
  <div class="row g-2 mb-3">
    <div class="col-md-4">
      <input type="text" id="filtBrinco" class="form-control"
             placeholder="Filtrar por brinco...">
    </div>
    <div class="col-md-3">
      <select id="filtSexo" class="form-select">
        <option value="">Todos os sexos</option>
        <option>Macho</option>
        <option>Fêmea</option>
      </select>
    </div>
    <div class="col-auto">
      <button class="btn btn-success" data-bs-toggle="modal"
              data-bs-target="#modalAnimal"
              onclick="abrirModal()">+ Novo Animal</button>
    </div>
  </div>
    <!-- Tabela -->
  <div class="table-responsive">
    <table class="table table-bordered table-hover">
      <thead class="table-dark">
        <tr><th>ID</th><th>Fazenda</th><th>Brinco</th>
            <th>Sexo</th><th>Raça</th><th>Peso (kg)</th><th>Ações</th></tr>
      </thead>
      <tbody id="tabelaAnimais"></tbody>
    </table>
  </div>
  <div id="paginacao" class="d-flex justify-content-center gap-2 mt-2"></div>

  <!-- Modal cadastro/edição -->
  <div class="modal fade" id="modalAnimal">
    <div class="modal-dialog"><div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitulo">Novo Animal</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="campoId">
        <div class="mb-2"><label>Fazenda ID</label>
          <input type="number" id="campoFazenda" class="form-control"></div>
        <div class="mb-2"><label>Brinco</label>
          <input type="text" id="campoBrinco" class="form-control"></div>
        <div class="mb-2"><label>Sexo</label>
          <select id="campoSexo" class="form-select">
            <option>Macho</option><option>Fêmea</option>
          </select></div>
        <div class="mb-2"><label>Raça</label>
          <input type="text" id="campoRaca" class="form-control"></div>
        <div class="mb-2"><label>Peso (kg)</label>
          <input type="number" step="0.01" id="campoPeso" class="form-control"></div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary" onclick="salvar()">Salvar</button>
        <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div></div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
let paginaAtual = 1;
let modal;
document.addEventListener('DOMContentLoaded', () => {
  modal = new bootstrap.Modal(document.getElementById('modalAnimal'));
  carregarAnimais();
  document.getElementById('filtBrinco').addEventListener('input', () => { paginaAtual=1; carregarAnimais(); });
  document.getElementById('filtSexo').addEventListener('change', () => { paginaAtual=1; carregarAnimais(); });
});

async function carregarAnimais() {
  const brinco = document.getElementById('filtBrinco').value;
  const sexo   = document.getElementById('filtSexo').value;
  const url    = `animais_api.php?acao=listar&page=${paginaAtual}&brinco=${brinco}&sexo=${sexo}`;
  const res    = await fetch(url);
  const dados  = await res.json();

  const tbody = document.getElementById('tabelaAnimais');
  tbody.innerHTML = dados.animais.map(a => `
    <tr>
      <td>${a.id}</td><td>${a.fazenda_id}</td><td>${a.brinco}</td>
      <td><span class="badge bg-${a.sexo==='Macho'?'primary':'danger'}">${a.sexo}</span></td>
      <td>${a.raca}</td><td>${a.peso}</td>
      <td>
        <button class="btn btn-sm btn-warning" onclick="editar(${JSON.stringify(a)})">Editar</button>
        <button class="btn btn-sm btn-danger"  onclick="excluir(${a.id})">Excluir</button>
      </td>
    </tr>`).join('');

  const pg = document.getElementById('paginacao');
  pg.innerHTML = '';
  for (let i=1; i <= dados.total_paginas; i++) {
    pg.innerHTML += `<button class="btn btn-sm ${i===paginaAtual?'btn-dark':'btn-outline-secondary'}"
      onclick="irPara(${i})">${i}</button>`;
  }
}

function irPara(p) { paginaAtual = p; carregarAnimais(); }

function abrirModal() {
  document.getElementById('modalTitulo').textContent = 'Novo Animal';
  ['campoId','campoFazenda','campoBrinco','campoRaca','campoPeso'].forEach(id => {
    document.getElementById(id).value = '';
  });
}

function editar(a) {
  document.getElementById('modalTitulo').textContent = 'Editar Animal';
  document.getElementById('campoId').value      = a.id;
  document.getElementById('campoFazenda').value  = a.fazenda_id;
  document.getElementById('campoBrinco').value   = a.brinco;
  document.getElementById('campoSexo').value     = a.sexo;
  document.getElementById('campoRaca').value     = a.raca;
  document.getElementById('campoPeso').value     = a.peso;
  modal.show();
}

async function salvar() {
  const body = new FormData();
  body.append('acao',       'salvar');
  body.append('id',         document.getElementById('campoId').value);
  body.append('fazenda_id', document.getElementById('campoFazenda').value);
  body.append('brinco',     document.getElementById('campoBrinco').value);
  body.append('sexo',       document.getElementById('campoSexo').value);
  body.append('raca',       document.getElementById('campoRaca').value);
  body.append('peso',       document.getElementById('campoPeso').value);
  const res  = await fetch('animais_api.php', { method: 'POST', body });
  const json = await res.json();
  if (json.ok) { modal.hide(); carregarAnimais(); }
  else alert(json.erro);
}

async function excluir(id) {
  if (!confirm('Confirma a exclusão?')) return;
  const body = new FormData();
  body.append('acao', 'excluir'); body.append('id', id);
  await fetch('animais_api.php', { method: 'POST', body });
  carregarAnimais();
}
</script></div></body></html>