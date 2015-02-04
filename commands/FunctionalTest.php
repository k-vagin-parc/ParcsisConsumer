<?php
/**
 *
 * @author k.vagin
 */

namespace Parcsis\ConsumersMQ\Tests;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class FunctionalTest extends Command {

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
		$messageData = [
			'message' => ['test', 'value'],
		];

		$message = new TestMessage($messageData);
		$consumer = new TestConsumer;

		\Parcsis\ConsumersMQ\Publisher::publish($message, TestMessage::EXCHANGE);

		$consumer->consume();

		//print_r($msg);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [];
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
