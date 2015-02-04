
### Changelog:
* Класс \RabbitMessage удален
* Создание очереди требует указание ее имени, queueBind больше имя очереди не принимает
* Константы "reason of restart" (в Parcsis\ConsumersMQ\Dispatcher\MessageDispatcherBase) и "control pattern" (в пуле констант) перенесены в параметры конфигурации модуля `constants.control.restart`
* Прибиндивание к очереди в консьюмере теперь асинхронное и происходит после init() (в методе connectToRabbit()),
вместо AMQP::queueDeclare надо вызвать $this->queueDeclare, вместо AMQP::queueBind - $this->queueBind (в queueBind убран параметр имя очереди). Как фича теперь 1 консьюмер может присосаться только к
1 точке обмена (это полезно или вредно??)
* Из класса MessageConsumerBase выкинута реализация метода callback - они все равно кругом переопределяется, если нужна обработка транзакций - можно расширить и скопипастить в наследника код из DS
* Максимальное количество запросов обрабатываемых процессом перенесено в MessageDispatcherBase
* auto_delete у очереди по умолчанию false теперь, т.к. во всех консьюмерах оно так устанавливается, теперь передавать это не надо

### php doc (ide helper):
* https://github.com/pdezwart/php-amqp/tree/master/stubs