<?php

namespace Stem\Queue;

/**
 * Represents a job added to a queue to run
 *
 * @package Stem\Queue
 */
abstract class Job {
	/** @var int Job ran without error */
	const STATUS_OK = 0;

	/** @var int Job had an error */
	const STATUS_ERROR = -1;

	/** @var int The entire queue should be stopped and cleared */
	const STATUS_STOP_ALL = 666;

	/** @var int The current iteration */
	public $iteration = 1;

	/**
	 * The maximum number of times this job can be retried before failing completely
	 * @return int
	 */
	public function maxIterations() {
		return 1;
	}

	/**
	 * Runs the job, returning a status code
	 * @return int
	 */
	abstract public function run();
}