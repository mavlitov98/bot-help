# Тестовое задание для BotHelp

```
Есть веб-api, непрерывно принимающее события (ограничимся 10000 событий) для группы аккаунтов (1000 аккаунтов) и складывающее их в очередь.
Каждое событие связано с определенным аккаунтом и важно, чтобы события аккаунта обрабатывались в том же порядке, в котором поступили в очередь. Обработка события занимает 1 секунду (эмулировать с помощью sleep).  
Сделать обработку очереди событий максимально быстрой на данной конкретной машине.
```

# Порядок запуска:
1. Запустить `make first-init`
2. Отправить `POST` запрос на `http://localhost:8081/event` (будет создано 10к событий для 1к аккаунтов, `http://localhost:15672/#/queues` - тут можно посмотреть)
3. Запустить потребление `make consume-events` (следить за потреблением можно так же в `http://localhost:15672/#/queues`)
