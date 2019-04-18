<?php

namespace Stem\Commands\Queue;

use Stem\Core\Command;
use Stem\Queue\Job;
use Stem\Queue\Queue;

class QueueWorkerCommand extends Command {
	/**
	 * Clears a queue
	 *
	 * <queue>
	 * : The name of the queue to process
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function clear($args, $assoc_args) {
		$queue = $args[0];

		$count = Queue::instance()->count($queue);

		self::Out("Clearing queue with $count items ... ");
		Queue::instance()->clear($queue);
		self::Out("Cleared.", true);
	}

	/**
	 * Processes the items in a given queue
	 *
	 * <queue>
	 * : The name of the queue to process
	 *
	 * [--max-iterations=<int>]
	 * : The max number of jobs to process
	 *
	 * @when after_wp_load
	 *
	 * @param $args
	 * @param $assoc_args
	 */
	public function process($args, $assoc_args) {
		$queue = $args[0];
		$maxIterations = (isset($assoc_args['max-iterations'])) ? $assoc_args['max-iterations'] : -1;

		if (Queue::instance()->count($queue) == 0) {
			self::Out('No jobs in queue.  Exiting.', true);
			exit(0);
		}

		$iteration = 1;
		while(true) {
			self::Out("[#{$iteration}] Fetching next job ... ", false);

			/** @var Job $job */
			$job = Queue::instance()->next($queue, 0);
			if (empty($job)) {
				self::Out('No job found, exiting.', true);
				break;
			}
			self::Out('Job found.', true);

			$result = $job->run();
			if ($result == Job::STATUS_STOP_ALL) {
				self::Out('Job want to clear queue ... ');
				Queue::instance()->clear($queue);
				self::Out('Cleared, exiting.');
			} else if ($result == Job::STATUS_ERROR) {
				self::Out('Job error ... ');
				Queue::instance()->remove($queue, $job);

				$job->iteration++;
				if ($job->iteration <= $job->maxIterations()) {
					Queue::instance()->add($queue, $job);
					self::Out('Re-adding to queue to try again.', true);
				} else {
					self::Out('Maximum iterations tried, removing job from queue.', true);
				}

			} else {
				Queue::instance()->remove($queue, $job);
			}

			$iteration++;
			if (($maxIterations > 0) && ($iteration >= $maxIterations)) {
				self::Out('Maximum jobs processed.');
				break;
			}
		}

	}

	public static function Register() {
		\WP_CLI::add_command('queue', __CLASS__);
	}
}