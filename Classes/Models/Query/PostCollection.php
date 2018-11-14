<?php

namespace Stem\Models\Query;

use Stem\Core\Context;

/**
 * Represents the results from a query or \WP_Query
 *
 * @package Stem\Models
 */
class PostCollection implements \ArrayAccess, \Iterator, \Countable {
    /** @var Context|null The context */
    protected $context = null;

    /** @var Post[] Posts */
    protected $posts = [];

    /** @var Query|null The query that generated the collection  */
    protected $query = null;

    /** @var null|\WP_Query The WP_Query */
    protected $wpQuery = null;

    /** @var array The arguments used for the WP_Query */
    protected $args = [];

    /**
     * PostCollection constructor.
     *
     * @param Context $context
     * @param Query|null $query
     * @param \WP_Query|null $wpQuery
     */
    public function __construct(Context $context, Query $query = null, \WP_Query $wpQuery = null) {
        $this->context = $context;

        if (!empty($query)) {
            $this->query = $query;

            $this->args = $query->build();
            $this->wpQuery = new \WP_Query($this->args);
        } else if (!empty($wpQuery)) {
            $this->wpQuery = $wpQuery;
            $this->args = $wpQuery->query_vars;
        }

        foreach($this->wpQuery->posts as $post) {
            $this->posts[] = $this->context->modelForPost($post);
        }
    }

    //region Properties

    /**
     * Returns all of the posts this collection contains
     * @return Post[]
     */
    public function posts() {
        return $this->posts;
    }

    /**
     * Count elements of an object
     * @link http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count() {
        return count($this->posts);
    }

    /**
     * Total posts that could be returned by the underlying query
     * @return int
     */
    public function total() {
        return (int)$this->wpQuery->found_posts;
    }

    /**
     * Total number of pages of posts
     * @return int
     */
    public function pages() {
        return (int)$this->wpQuery->max_num_pages;
    }

    /**
     * The current page
     * @return int
     */
    public function currentPage() {
        if (isset($this->args['offset']) && ($this->pages() > 0)) {
            return (int)floor(floatval($this->args['offset']) / $this->pages());
        }

        if (isset($this->args['paged'])) {
            return $this->args['paged'];
        }

        return 0;
    }

    /**
     * The current offset
     * @return int
     */
    public function offset() {
        if (isset($this->args['offset'])) {
            return $this->args['offset'];
        }

        if (isset($this->args['paged'])) {
            return $this->args['paged'] * $this->pages();
        }

        return 0;
    }

    /**
     * The arguments used to build the query, for debugging
     * @return array
     */
    public function arguments() {
        return $this->args;
    }

    /**
     * The SQL used to generate the results, for debugging and chuckles.
     * @return string
     */
    public function sql() {
        return $this->wpQuery->request;
    }

    //endregion

    //region Iterator

    /**
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     * @since 5.0.0
     */
    public function current() {
        return current($this->posts);
    }

    /**
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function next() {
        next($this->posts);
    }

    /**
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     * @since 5.0.0
     */
    public function key() {
        return key($this->posts);
    }

    /**
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     * @since 5.0.0
     */
    public function valid() {
        $key = key($this->posts);

        return (($key !== null) && ($key !== false));
    }

    /**
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     * @since 5.0.0
     */
    public function rewind() {
        reset($this->posts);
    }

    //endregion

    //region Array Access

    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset) {
        return isset($this->posts[$offset]);
    }

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     * @since 5.0.0
     */
    public function offsetGet($offset) {
        return (isset($this->posts[$offset])) ? $this->posts[$offset] : null;
    }

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws \Exception
     */
    public function offsetSet($offset, $value) {
        throw new \Exception("This array is read-only");
    }

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     * @since 5.0.0
     * @throws \Exception
     */
    public function offsetUnset($offset) {
        throw new \Exception("This array is read-only");
    }

    //endregion

    //region Debug

    public function __debugInfo() {
        return [
            'posts' => $this->posts,
            'args' => $this->args,
            'sql' => $this->sql(),
            'wpQuery' => $this->wpQuery,
            'count' => $this->count(),
            'total' => $this->total(),
            'pages' => $this->pages(),
            'currentPage' => $this->currentPage()
        ];
    }

    //endregion

}