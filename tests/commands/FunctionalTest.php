<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;

use Illuminate\Console\Command;
use Parcsis\ConsumersMQ\Publisher;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class FunctionalTest extends Command {

	const
		POINTS			= 1000,
		EXCHANGE 		= 'consumers-mq-test-exchange',
		INSERT_QUEUE	= 'parcsis.consumers-mq.testing.calculate.insert',
		PROCESSED_Q		= 'parcsis.consumers-mq.testing.calculate.processed',

		TYPE_EVEN		= 'parcsis.consumers-mq.testing.calculate.even',
		TYPE_ODD		= 'parcsis.consumers-mq.testing.calculate.odd';

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'consumer-mq:test';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Проверяет работу консьюмера';


	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$option = $this->argument('operation');

		if ($option === 'insert') {
			$this->insertMessages();
			$this->observer();
		}
		else {
			$this->startConsumer($option);
		}
	}

	private function insertMessages()
	{
		$this->info('Send insert messages');

		// clear
		\DB::table('calculated')->truncate();

		$message = new OnInsertInMessage;

		for ($i = 1; $i <= self::POINTS; $i++) {
			$message->setNumber(rand(1, self::POINTS));
			(new Publisher)->publish($message, self::EXCHANGE);
		}

		$this->info('Ok.');
	}

	private function startConsumer($consumerName)
	{
		$this->info("Consumer {$consumerName} starting...");

		if ($consumerName === "insert_in") {
			$consumer = new InsertIn;
		}
		elseif ($consumerName === "calculate_even") {
			$consumer = new CalculateEven;
		}
		elseif ($consumerName === "calculate_odd") {
			$consumer = new CalculateOdd;
		}
		elseif ($consumerName === "calculate_other") {
			$consumer = new CalculateOther;
		}
		else {
			throw new \Exception('Incorrect consumer name, correct one of them: "insert_in", "calculate_even", "calculate_odd", "calculate_other"');
		}

		$consumer->consume();

		$this->info("Consumer {$consumerName} complete");
	}

	/**
	 * следит за ходом выполнения теста, анализируя табл
	 */
	private function observer()
	{
		$isError = false;
		$previous = -1;
		$attempt = 0;
		while (true) {
			$calculatedError = \DB::table('calculated')->where('out', 2)->count();

			if ($calculatedError > 0) {
				$isError = true;
			}

			$calculatedCount = \DB::table('calculated')->where('out', 1)->count();

			$this->info("Processed: {$calculatedCount} count");

			sleep(15);

			$allCount = \DB::table('calculated')->where('out', '>', 0)->count();
			if ($allCount === self::POINTS || $attempt > 2) {
				break;
			}

			if ($allCount === $previous) {
				//  если за 2 итерации количество обработанных полей не изменилось - прерывание
				$attempt++;
			}

			$previous = $allCount;
		}

		// отправляет код остановки всем консьюмерам:
		$message = new AfterEvenInsertMessage();
		$message->setId(-1);
		(new Publisher)->publish($message, self::EXCHANGE);

		$message = new AfterOddInsertMessage();
		$message->setId(0);
		(new Publisher)->publish($message, self::EXCHANGE);

		if (!$isError) {
			$this->info("Success!");
		}
		else {
			$errors = \DB::table('calculated')->where('out', 2)->get();
			foreach ($errors as $error) {
				$this->error("id: {$error->id}, error: {$error->error}, consumer: {$error->owner}");
			}
		}
	}

	/**
	 * Get the console command arguments.
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['operation', InputArgument::REQUIRED, '"insert" - send message in INSERT_QUEUE, or name of consumer: "insert_in", "calculate_even", "calculate_odd", "calculate_other"', null]
		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [];
	}

}
