# rest.monitor

## 📌 Назначение

Модуль для 1С-Битрикс, автоматически отслеживающий все REST API вызовы (вебхуки, OAuth, приложения) и отправляющий данные в OpenSearch для мониторинга и анализа.

Фиксируется:
- метод запроса (например, `crm.lead.add`)
- ID пользователя
- IP адрес
- длительность выполнения
- статус (успешно/ошибка)

---

## ⚙️ Возможности

- Автоматический перехват всех REST-вызовов (`/rest/`) через события Bitrix
- Сохранение логов в БД (`b_rest_api_log`)
- Асинхронная отправка логов в OpenSearch через Bitrix-агент
- Простая установка через мастер
- Отладочный лог: `/rest_monitor_debug.log`

---

## 🚀 Установка

1. Клонируйте модуль в директорию Bitrix:

```bash
cd /local/modules
git clone https://github.com/Katapios/rest.monitor.git
```

2. Установите модуль в админке: **Marketplace → Модули**  
   При установке укажите URL вашего OpenSearch (например: `http://opensearch.local:9200`)

3. Модуль автоматически:
   - Зарегистрирует обработчики `OnPageStart` и `OnEndBufferContent`
   - Создаст таблицу логов
   - Запустит Bitrix-агент для отправки логов

---

## 🔍 Как это работает

1. **REST вызов поступает** (например, `crm.lead.add`)
2. **Модуль фиксирует** начало и конец запроса, вычисляет время, определяет метод, пользователя, IP, результат
3. **Лог сохраняется** в таблицу `b_rest_api_log` с флагом `SENT = N`
4. **Агент каждую минуту** отправляет новые логи в OpenSearch по адресу `http://ваш-сервер/rest_api_logs/_doc`
5. После успешной отправки `SENT` меняется на `Y`

---

## 🧪 Пример REST запроса

```bash
curl -X POST "https://example.bitrix24.ru/rest/1/XXXX/crm.lead.add.json" \
     -H "Content-Type: application/json" \
     -d '{"fields":{"TITLE":"Тест лид","NAME":"Иван"}}'
```

---

## ⚠️ Важно

- Для внешних вызовов используйте **входящий вебхук**
- Убедитесь, что сервер OpenSearch доступен из Bitrix
- Cron для агентов ускорит отправку логов (иначе зависит от хитов)

---

## 🗑 Удаление

При удалении:
- Агент отключается
- Обработчики снимаются
- Таблица логов удаляется
- (опционально) могут быть удалены CRM-сущности

---


## 🤝  Как сделать Pull Request на GitHub и предложить свои доработки
Если вы хотите внести улучшения в мой код, следуйте этой инструкции:

### Шаги

1. Сделайте **Fork** репозитория.
2. Клонируйте свой форк:
   ```bash
   git clone https://github.com/Katapios/rest.monitor.git
   cd имя-репозитория
   ```
3. Создайте новую ветку:
   ```bash
   git checkout -b feature/название-изменения
   ```
4. Внесите изменения и сделайте коммит:
   ```bash
   git add .
   git commit -m "Описание изменений"
   git push origin feature/название-изменения
   ```
5. Откройте Pull Request на GitHub:
   - Перейдите в свой форк.
   - Нажмите **"Compare & pull request"**.
   - Заполните описание и отправьте PR.

📌 **Важно**: Пожалуйста, старайтесь писать читаемые коммиты и предоставляйте контекст в описании PR.



# rest.monitor (EN)

## 📌 Purpose

Bitrix module for tracking all REST API calls and exporting logs to OpenSearch for monitoring and analytics.

It logs:
- method name (e.g. `crm.lead.add`)
- user ID
- IP address
- execution duration
- result (success/error)

---

## ⚙️ Features

- Automatic tracking of all `/rest/` requests using Bitrix events
- Logs stored in `b_rest_api_log` table
- Async delivery to OpenSearch via Bitrix agent
- Easy setup wizard
- Debug log: `/rest_monitor_debug.log`

---

## 🚀 Installation

1. Clone the module into Bitrix modules directory:

```bash
cd /local/modules
git clone https://github.com/Katapios/rest.monitor.git
```

2. In Bitrix admin panel, go to **Marketplace → Modules**  
   During install, provide OpenSearch base URL (e.g. `http://opensearch.local:9200`)

3. The module will:
   - Register `OnPageStart` and `OnEndBufferContent` handlers
   - Create log table
   - Launch log sender agent

---

## 🔍 How it works

1. **REST call is received** (e.g. `crm.lead.add`)
2. **Module tracks** start/end, calculates duration, user, method, IP, result
3. **Logs are saved** into table with `SENT = N`
4. **Agent runs every minute**, pushes logs to OpenSearch (`/rest_api_logs/_doc`)
5. If successful, `SENT` is updated to `Y`

---

## 🧪 Example REST call

```bash
curl -X POST "https://example.bitrix24.ru/rest/1/XXXX/crm.lead.add.json" \
     -H "Content-Type: application/json" \
     -d '{"fields":{"TITLE":"Test Lead","NAME":"Ivan"}}'
```

---

## ⚠️ Notes

- Use **incoming webhook** for external REST calls
- Make sure OpenSearch is reachable from Bitrix
- Cron improves agent delivery (else depends on site traffic)

---

## 🗑 Uninstall

- Agent removed
- Event handlers unregistered
- Log table dropped
- CRM entities may be removed if selected



## 🤝 How to Make a Pull Request on GitHub and Contribute Improvements

If you'd like to improve this code, please follow the steps below:

### Steps

1. **Fork** the repository.
2. Clone your fork:
   ```bash
   git clone https://github.com/Katapios/rest.monitor.git
   cd your-repo-name
   ```
3. Create a new branch:
   ```bash
   git checkout -b feature/your-feature-name
   ```
4. Make your changes and commit them:
   ```bash
   git add .
   git commit -m "Describe your changes"
   git push origin feature/your-feature-name
   ```
5. Open a Pull Request on GitHub:
   - Go to your forked repository.
   - Click **"Compare & pull request"**.
   - Add a meaningful description and submit your PR.

📌 **Important**: Please write clear commit messages and include helpful context in your PR description.
