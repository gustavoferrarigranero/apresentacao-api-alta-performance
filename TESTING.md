# Roteiro de implementação — APIs de alta performance

Este é o projeto usado pelos alunos durante a aula. Ele contém a stack, o domínio ClinicFlow, banco, factories, seed e testes básicos, mas não contém a solução apresentada no projeto final.

## Preparação do ambiente

```bash
nvm use
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
php artisan serve
```

Confirme o ponto de partida:

```bash
curl -i 'http://127.0.0.1:8000/api/health'
php artisan route:list --path=api
```

Antes do exercício, somente a rota de saúde deve representar a API da aula.

## Verificação da stack

```bash
php artisan test --filter=StackReadinessTest
```

Esse teste garante que estão disponíveis:

- models, migrations, factories e seed do ClinicFlow;
- tabelas de filas e falhas;
- driver de fila em banco;
- Laravel HTTP Client;
- API Resources;
- banco SQLite para desenvolvimento e testes.

## Etapa 1 — Contrato com API Resource

Objetivo: criar uma representação explícita de agendamento sem expor todas as colunas do model.

Passos da aula:

1. Gere `AppointmentResource`.
2. Retorne `id`, `starts_at` e `status`.
3. Inclua apenas `id` e `name` do paciente.
4. Escreva o teste do contrato antes de criar a rota.

Critério de conclusão: mudanças em colunas internas não alteram o JSON público.

## Etapa 2 — Consulta performática

Objetivo: construir a listagem sem N+1 e sem payload sem limite.

Passos da aula:

1. Crie um controller invocável para a listagem.
2. Valide `clinic_id`, `status` e `per_page`.
3. Selecione somente as colunas usadas pelo Resource.
4. Carregue o paciente antecipadamente.
5. Use paginação por cursor.
6. Limite `per_page` a 100.
7. Conte as queries executadas no teste.

Critério de conclusão: cinco ou cinquenta agendamentos devem manter um número limitado de consultas.

## Etapa 3 — Versionamento por URL

Objetivo: disponibilizar o primeiro contrato em `/api/v1/appointments`.

Passos da aula:

1. Agrupe as rotas com o prefixo `v1`.
2. Mantenha regras de negócio fora do agrupamento de versão.
3. Adicione teste para o endpoint versionado.

Critério de conclusão: `GET /api/v1/appointments?clinic_id=1` retorna o contrato V1.

## Etapa 4 — Versionamento por Header

Objetivo: servir o contrato V1 pela URL estável `/api/appointments`.

Passos da aula:

1. Leia o header `Accept`.
2. Aceite `application/vnd.clinicflow.v1+json`.
3. Retorne 406 para uma versão desconhecida.
4. Cubra os dois casos com testes.

Critério de conclusão: o cliente escolhe a versão sem alterar a URL.

## Etapa 5 — Webhook de saída

Objetivo: publicar `appointment.created` sem bloquear a resposta da API.

Passos da aula:

1. Crie tabelas para endpoints e tentativas de entrega.
2. Crie o endpoint de cadastro do agendamento.
3. Monte o evento com identificador único e dados mínimos.
4. Despache o job depois do commit da transação.
5. Assine o corpo JSON com HMAC SHA-256.
6. Configure timeout curto.
7. Configure cinco tentativas e backoff progressivo.
8. Registre status, resposta, duração e número da tentativa.

Critério de conclusão: a API responde 201 antes de o sistema parceiro processar o evento.

## Etapa 6 — Recebimento de webhook

Objetivo: receber eventos externos com segurança e sem duplicar efeitos.

Passos da aula:

1. Leia o corpo bruto da requisição.
2. Calcule e compare a assinatura antes de interpretar o JSON.
3. Valide o contrato do evento.
4. Persista `event_id` com índice único.
5. Despache o processamento somente para um evento novo.
6. Retorne 204 também para repetições válidas.

Critério de conclusão: duas entregas com o mesmo `event_id` produzem um único registro e um único job.

## Comparação com o projeto final

Depois de cada etapa, compare apenas os arquivos relacionados ao tema trabalhado. Evite copiar o projeto final inteiro, pois a sequência de implementação e os testes fazem parte do exercício.

## Validação ao terminar a aula

```bash
composer validate --no-check-publish
vendor/bin/pint --test
php artisan migrate:fresh --seed
php artisan test
php artisan route:list --path=api
```

O resultado construído em aula deve alcançar os mesmos comportamentos e testes documentados no `TESTING.md` do projeto final.
