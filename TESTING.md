# Testes e roteiro da aula — APIs de alta performance

Todos os comandos deste documento devem ser executados na raiz do `projeto-final`.

## Preparação

```bash
nvm use
composer install
npm install
npm run build
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan test
```

Inicie a aplicação:

```bash
php artisan serve
```

Confirme a saúde da API:

```bash
curl -i 'http://127.0.0.1:8000/api/health'
```

## API Resource, payload e paginação

Consulte a versão explícita por URL:

```bash
curl -i \
  'http://127.0.0.1:8000/api/v1/appointments?clinic_id=1&per_page=5&status=scheduled'
```

Verifique:

- somente os campos públicos aparecem no JSON;
- cada paciente contém apenas `id` e `name`;
- a resposta contém `data`, `links` e `meta`;
- `meta.strategy` contém `cursor-pagination`;
- `per_page=500` retorna 422.

Execute os testes deste bloco:

```bash
php artisan test --filter=AppointmentApiPerformanceTest
```

## Versionamento por URL

```bash
curl -i 'http://127.0.0.1:8000/api/v1/appointments?clinic_id=1'
```

O endpoint deve retornar 200 e usar o contrato V1.

## Versionamento por Header

```bash
curl -i \
  -H 'Accept: application/vnd.clinicflow.v1+json' \
  'http://127.0.0.1:8000/api/appointments?clinic_id=1'

curl -i \
  -H 'Accept: application/vnd.clinicflow.v2+json' \
  'http://127.0.0.1:8000/api/appointments?clinic_id=1'
```

A versão V1 deve retornar 200. A versão não suportada deve retornar 406.

## Configuração dos webhooks

Configure no `.env`:

```dotenv
PARTNER_WEBHOOK_SECRET=lesson-secret
```

Limpe o cache de configuração:

```bash
php artisan config:clear
```

Registre um endpoint para a clínica criada pelo seed:

```bash
php artisan tinker --execute="App\Models\WebhookEndpoint::create(['clinic_id' => 1, 'url' => 'http://127.0.0.1:8000/api/webhooks/partner', 'secret' => 'lesson-secret']);"
```

Em outro terminal, inicie a fila:

```bash
php artisan queue:work --tries=5
```

## Criação do agendamento e webhook de saída

```bash
curl -i -X POST 'http://127.0.0.1:8000/api/v1/appointments' \
  -H 'Content-Type: application/json' \
  -H 'Accept: application/json' \
  -d '{"clinic_id":1,"patient_id":1,"starts_at":"2027-01-15T14:30:00Z"}'
```

Verifique:

- a API retorna 201;
- o evento contém `event_id`, `event`, `occurred_at` e `data`;
- o envio ocorre pelo worker;
- o header `X-Signature` contém a assinatura HMAC;
- o cliente usa timeout curto;
- o job possui cinco tentativas e backoff progressivo.

## Auditoria das entregas

```bash
php artisan tinker --execute="dump(App\Models\WebhookDelivery::latest()->first()?->toArray());"
```

O registro deve apresentar:

- identificador e nome do evento;
- número da tentativa;
- status da resposta;
- corpo recebido;
- duração em milissegundos;
- indicação de sucesso ou falha.

## Recebimento e idempotência

```bash
php artisan tinker --execute="dump(App\Models\WebhookDelivery::latest()->first()?->toArray());"
php artisan test --filter=AppointmentWebhookTest
```

Os testes enviam o mesmo `event_id` mais de uma vez e comprovam que:

- assinatura inválida retorna 401;
- entrega válida retorna 204;
- repetição válida também retorna 204;
- somente um evento é persistido;
- somente um job de processamento é criado.

## Validação completa

```bash
composer validate --no-check-publish
vendor/bin/pint --test
php artisan migrate:fresh --seed
php artisan test
php artisan route:list --path=api
```

O projeto está pronto quando toda a suíte passa e as cinco rotas aparecem:

- `GET /api/health`;
- `GET /api/v1/appointments`;
- `POST /api/v1/appointments`;
- `GET /api/appointments`;
- `POST /api/webhooks/partner`.
