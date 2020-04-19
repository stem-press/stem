<?php

namespace Stem\Queue;

class Queue {
	/** @var null|\Redis  */
	private $redisClient = null;

	/** @var int Max waiting time */
	private $maxWait = 15;

	/** @var null|Queue The static instance  */
	protected static $instance = null;

	/**
	 * Returns the static instance
	 * @return Queue
	 */
	public static function instance() {
		if (static::$instance == null) {
			static::$instance = new Queue();
		}

		return static::$instance;
	}

	/**
	 * Configures the queue
	 * @param $config
	 */
	public function configure($config) {
		if (isset($config['timeout'])) {
			$this->maxWait = $config['timeout'];
		}

		$this->redisClient = new \Redis();
		$this->redisClient->connect($config['driver']['host'], $config['driver']['port']);
		$this->redisClient->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
	}

	/**
	 * Adds a job to the queue
	 *
	 * @param string $queue
	 * @param Job $job
	 */
	public function add($queue, $job) {
		$this->redisClient->lPush($queue, $job);
	}

	/**
	 * Returns the next Job in the queue
	 *
	 * @param $queue
	 * @param $timeout
	 *
	 * @return Job|null
	 */
	public function next($queue, $timeout = -1) {
		if ($timeout == -1) {
			$timeout = $this->maxWait;
		}

		if ($timeout == 0) {
			$queueItem = $this->redisClient->rpoplpush($queue, "$queue-processing");
		} else {
			$queueItem = $this->redisClient->brpoplpush($queue, "$queue-processing", $timeout);
		}

		if (!empty($queueItem)) {
			if (!is_string($queueItem)) {
				return $queueItem;
			}

			return unserialize($queueItem);
		}

		return null;
	}

	/**
	 * Removes a job from the queue
	 *
	 * @param string $queue
	 * @param Job $job
	 */
	public function remove($queue, $job) {
		$this->redisClient->lRem("$queue-processing", $job, 1);
	}

	/**
	 * Clears a queue
	 *
	 * @param string $queue
	 */
	public function clear($queue) {
		$this->redisClient->del($queue, "$queue-processing");
	}

	/**
	 * Returns the number of items in the queue
	 *
	 * @param string $queue
	 *
	 * @return int
	 */
	public function count($queue) {
		return $this->redisClient->lLen($queue);
	}
}