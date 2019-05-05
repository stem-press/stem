<?php

namespace Stem\Models\Query;

use Stem\Core\Context;
use Stem\Models\Post;
use Stem\Models\User;

/**
 * Fluent interface for the horrible WP_Query
 *
 * @property-read Field $id Post ID
 * @property-read Field $slug Post slug
 * @property-read Field $slugs Post slugs
 * @property-read Field $title Post title
 * @property-read Field $type Post type
 * @property-read Field $parent Post parent
 * @property-read Field $author Post author
 * @property-read Field $authors Post authors
 * @property-read Field $authorName Post author name
 * @property-read Field $category Post category
 * @property-read Field $categories Post categories
 * @property-read Field $tag Post tag
 * @property-read Field $tags Post tags
 * @property-read Field $hasPassword Post has password
 * @property-read Field $password Post password
 * @property-read Field $status Post status
 * @property-read Field $commentCount Post comment count
 * @property-read Query $or Sets metaquery relation to OR
 * @property-read Query $and Sets metaquery relation to AND
 *
 * @package Stem\Models\Query
 */
final class Query {
    /** @var Context|null The context */
    private $context = null;

    /** @var bool Determines if this query is a subquery */
    private $isSubquery = false;

    /** @var array Arguments for \WP_Query */
    private $args = [];

    /** @var int[] Array of author IDs to query  */
    private $authors = [];

    /** @var null|string Author name */
    private $authorName = null;

    /** @var Query[] Metadata Sub-queries  */
    private $metaSubQueries = [];

    /** @var null|string The meta query relation */
    private $metaqueryRelation = null;

    /** @var array This query's meta queries */
    private $metaQueries = [];

    /** @var array This query's taxonomy queries */
    private $taxQueries = [];

    /** @var array Fields that should not be considered meta values */
    private static $nonMetaFields = [
        'id',
        'slug',
        'title',
	    'parent',
	    'author',
        'authorName',
        'category',
        'tag',
        'hasPassword',
        'password',
        'type',
        'status',
        'commentCount'
    ];

    /** @var array Valid operators for specific fields */
    private static $fieldOperators = [
        'id' => ['=', '!=', 'in', 'not in'],
        'slug' => ['=', 'in'],
        'title' => ['=', 'like', 'not like'],
	    'parent' => ['=', '!=', 'in', 'not in'],
        'author' => ['=', '!=', 'in', 'not in'],
        'authorName' => ['='],
        'category' => ['=', '!=', 'in', 'not in', 'all'],
        'taxonomy' => ['=', '!=', 'in', 'not in', 'all', 'exists', 'not exists'],
        'tag' => ['=', '!=', 'in', 'not in', 'all'],
        'hasPassword' => ['=', '!='],
        'password' => ['='],
        'type' => ['=', 'in'],
        'status' => ['=', 'in'],
        'commentCount' => ['=', '!=', '<', '<=', '>', '>='],
        'meta' =>  ['=', '!=', '<', '<=', '>', '>=', 'like', 'not like', 'in', 'not in', 'between', 'not between', 'exists', 'not exists', 'regexp', 'not regexp', 'rlike']
    ];

    /** @var array Plural aliases for fields */
    private static $pluralAliases = [
        'authors' => 'author',
        'slugs' => 'slug',
        'categories' => 'category',
        'tags' => 'tag'
    ];

    /**
     * Query constructor.
     *
     * @param Context $context
     * @param null|string|string[] $postType
     * @param bool $subquery
     * @param null|string $metaqueryRelation
     */
    public function __construct(Context $context, $postType, $subquery = false, $metaqueryRelation = null) {
        $this->context = $context;

        $this->isSubquery = $subquery;
        $this->metaqueryRelation = $metaqueryRelation;

        if (!empty($postType)) {
            $this->args['post_type'] = $postType;
        }
    }

    //region Query Builder

    /**
     * @param $name
     * @return Field|Query
     * @throws \Exception
     */
    public function __get($name) {
        if ($this->isSubquery && in_array($name, static::$nonMetaFields)) {
            throw new \Exception("Only meta fields allowed in sub-queries.");
        }

        if (isset(static::$pluralAliases[$name])) {
            $name = static::$pluralAliases[$name];
        }

        if ($name == 'id') {
            return new Field($this, 'id', static::$fieldOperators['id'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'slug') {
            return new Field($this, 'slug', static::$fieldOperators['id'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'title') {
	        return new Field($this, 'title', static::$fieldOperators['title'], function($field, $type, $operator, $value) {
		        $this->processWhere($field, $type, $operator, $value);
	        });
        }  else if ($name == 'parent') {
	        return new Field($this, 'parent', static::$fieldOperators['parent'], function($field, $type, $operator, $value) {
		        $this->processWhere($field, $type, $operator, $value);
	        });
        } else if ($name == 'author') {
            return new Field($this, 'author', static::$fieldOperators['author'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'authorName') {
            return new Field($this, 'authorName', static::$fieldOperators['authorName'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'category') {
            return new Field($this, 'category', static::$fieldOperators['category'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'tag') {
            return new Field($this, 'tag', static::$fieldOperators['tag'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'hasPassword') {
            return new Field($this, 'hasPassword', static::$fieldOperators['hasPassword'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'password') {
            return new Field($this, 'password', static::$fieldOperators['password'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'type') {
            return new Field($this, 'type', static::$fieldOperators['type'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'status') {
            return new Field($this, 'status', static::$fieldOperators['status'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'commentCount') {
            return new Field($this, 'commentCount', static::$fieldOperators['commentCount'], function($field, $type, $operator, $value) {
                $this->processWhere($field, $type, $operator, $value);
            });
        } else if ($name == 'or') {
            if ($this->metaqueryRelation != null) {
                throw new \Exception("The meta query relation has already been set to '{$this->metaqueryRelation}'.  Once set, it cannot be changed.");
            }

            $this->metaqueryRelation = 'OR';

            return $this;
        } else if ($name == 'and') {
            if ($this->metaqueryRelation != null) {
                throw new \Exception("The meta query relation has already been set to '{$this->metaqueryRelation}'.  Once set, it cannot be changed.");
            }

            $this->metaqueryRelation = 'AND';

            return $this;
        } else {
            return new Field($this, $name, static::$fieldOperators['meta'], function($field, $type, $operator, $value) {
                $this->field($field, $type, $operator, $value);
            });
        }
    }

    public function or($callable) {
        $subquery = new Query($this->context, null, true, 'OR');
        call_user_func($callable, $subquery);
        $this->metaSubQueries[] = $subquery;
        return $this;
    }

    public function and($callable) {
        $subquery = new Query($this->context,null, true, 'AND');
        call_user_func($callable, $subquery);
        $this->metaSubQueries[] = $subquery;
        return $this;
    }

    public function limit($limit) {
        $this->setArgument('posts_per_page', $limit);
        return $this;
    }

    public function offset($offset) {
        $this->setArgument('offset', $offset);
        return $this;
    }

    public function page($page) {
        $this->setArgument('page', $page);
        return $this;
    }

    /**
     * Perform taxonomy query
     *
     * @param string $taxonomy
     * @param string $valueType
     * @return Field
     * @throws \Exception
     */
    public function taxonomy($taxonomy, $valueType='term_id') {
        if (!in_array($valueType, ['slug', 'term_id', 'name', 'term_taxonomy_id'])) {
            throw new \Exception("Invalid taxonomy value type '$valueType'.  Valid value types: slug, term_id, name, term_taxonomy_id.");
        }

        return new Field($this, 'taxonomy', static::$fieldOperators['taxonomy'], function($field, $type, $operator, $value) use ($taxonomy, $valueType) {
            if ($operator == '=') {
                $operator = 'in';
            } else if ($operator == '!=') {
                $operator = 'not in';
            } else if ($operator == 'all') {
                $operator = 'and';
            }

            if (!is_array($value)) {
                $value = [$value];
            }

            $query = [
                'taxonomy' => $taxonomy,
                'field' => $valueType,
                'terms' => $value
            ];

            if ($operator != 'in') {
                $query['operator'] = strtoupper($operator);
            }

            $this->taxQueries[] = $query;
        });
    }

    /**
     * Set the text search terms
     *
     * @param string $searchTerms
     * @return $this
     */
    public function search($searchTerms) {
        if ($searchTerms === null) {
            unset($this->args['s']);
        } else {
            $this->args['s'] = $searchTerms;
        }

        return $this;
    }

    /**
     * Meta key query
     *
     * @param string $field
     * @param string $operator
     * @param mixed|null $value
     * @param string $type
     * @param string $queryName
     * @return $this
     */
    public function field($field, $fieldType, $operator, $value, $type = 'CHAR', $queryName = null) {
        $metaQuery = [
            'key' => $field,
            'value' => $value,
            'compare' => strtoupper($operator)
        ];

        if (strtoupper($operator) == 'EXISTS') {
        	unset($metaQuery['value']);
        }

        if (!empty($fieldType)) {
        	$type = $fieldType;
        }

        if ($type != 'CHAR') {
            $metaQuery['type'] = $type;
        }

        if (!empty($queryName)) {
            $this->metaQueries[$queryName] = $metaQuery;
        } else {
            $this->metaQueries[] = $metaQuery;
        }

        return $this;
    }

    /**
     * Add a where clause to the query.
     *
     * @param mixed ...$args
     * @throws \Exception
     * @return $this
     */
    public function where(...$args) {
        list($field, $operator, $value) = $this->parseWhereArgs($args);

        $this->processWhere($field, $operator, $value);

        return $this;
    }

    /**
     * Add a where clause to the query passing in a 2 element array containing the field
     * and value, or a 3 element array containing the field, operator and value.
     *
     * @param array $args
     * @throws \Exception
     * @return $this
     */
    public function whereWithArgs(array $args) {
        list($field, $operator, $value) = $this->parseWhereArgs($args);

        $this->processWhere($field, $operator, $value);

        return $this;
    }

    public function order($field, $direction = 'ASC', $append = true) {
        if (!$append) {
            unset($this->args['orderby']);
        }

        if (!isset($this->args['orderby'])) {
            $this->args['orderby'] = [];
        }

        if (in_array($field, static::$nonMetaFields) || ($field == 'post_date')) {
        	if ($field == 'id') {
        		$field = 'ID';
	        }

	        $this->args['orderby'] = array_merge($this->args['orderby'], [
		        "$field" => $direction
	        ]);
        } else {
        	$this->field($field, null, 'EXISTS', null, 'NUMERIC', "{$field}_clause");
        	$this->args['orderby'] = array_merge($this->args['orderby'], [
        		"{$field}_clause" => $direction
	        ]);
        }

        return $this;
    }

    //endregion

    //region Clause Processing

    /**
     * Insures the args are a 3 element array by adding the '=' operator if needed.
     *
     * @param array $args
     * @return array
     */
    private function parseWhereArgs(array $args) {
        if (count($args) == 2) {
            return [$args[0], '=', $args[1]];
        } else {
            return $args;
        }
    }

    /**
     * Processes a where clause
     *
     * @param string $field
     * @param string $operator
     * @param mixed|null $value
     * @throws \Exception
     */
    private function processWhere($field, $fieldType, $operator, $value) {
        if (isset(static::$pluralAliases[$field])) {
            $field = static::$pluralAliases[$field];
        }

        if (isset(static::$fieldOperators[$field])) {
            if (!in_array($operator, static::$fieldOperators[$field])) {
                throw new \Exception("Invalid operator '$operator'.  Valid operators: ".implode(", ", static::$fieldOperators[$field]));
            }
        }

        if ($field == 'id') {
            $this->processIds($operator, $value);
        } else if ($field == 'slug') {
	        $this->processSlugs($operator, $value);
        }  else if ($field == 'parent') {
	        $this->processParent($operator, $value);
        } else if ($field == 'title') {
        	if ($operator == 'like') {
		        $this->setArgument('post_title_like', $value);
	        } else if ($operator == 'not like') {
		        $this->setArgument('post_title_not_like', $value);
	        } else {
                $this->setArgument('title', $value);
	        }
        } else if ($field == 'author') {
            $operator = in_array($operator, ['=', 'in']) ? '=' : '!=';
            $this->processAuthor($operator, $value);
        } else if ($field == 'authorName') {
            $this->authorName = $value;
            $this->authors = [];
        } else if ($field == 'category') {
            $this->processCategory($operator, $value);
        } else if ($field == 'tag') {
            $this->processTags($operator, $value);
        } else if ($field == 'hasPassword') {
            $this->setArgument('has_password', $value);
        } else if ($field == 'password') {
            $this->setArgument('password', $value);
        } else if ($field == 'type') {
            $this->setArgument('post_type', $value);
        } else if ($field == 'status') {
            $this->setArgument('post_status', $value);
        } else if ($field == 'commentCount') {
            if ($value === null) {
                unset($this->args['comment_count']);
            } else {
                $this->args['comment_count'] = [
                    'value' => $value,
                    'compare' => $operator
                ];
            }
        } else {
            $this->field($field, $fieldType, $operator, $value);
        }
    }

    /**
     * Processes where clause for post slugs
     *
     * @param $operator
     * @param $value
     * @throws \Exception
     */
    private function processSlugs($operator, $value) {
        $postOp = 'post_name__in';

        if (empty($value)) {
            unset($this->args[$postOp]);
            return;
        }

        $slugs = [];
        if (is_string($value)) {
            $slugs = [$value];
        } else if (is_array($value)) {
            foreach($value as $post) {
                if (is_string($post)) {
                    $slugs[] = $post;
                } else if ($post instanceof \WP_Post) {
                    $slugs[] = $post->post_name;
                } else if ($post instanceof Post) {
                    $slugs[] = $post->slug();
                }
            }
        }

        if (empty($value)) {
            unset($this->args[$postOp]);
        } else {
            $this->args[$postOp] = $slugs;
        }
    }

    /**
     * Processes an author where clause
     *
     * @param string $operator
     * @param mixed|null $value
     * @throws \Exception
     */
    private function processAuthor($operator, $value) {
        if ($value == null) {
            $this->authors = [];
            return;
        }

        if ($operator == '=') {
            $add = '';
        } else if ($operator == '!=') {
            $add = '-';
        } else {
            throw new \Exception("Invalid operator for authors.  Only '=' and '!=' supported, '$operator' used.");
        }

        $newAuthors = [];
        if (is_array($value)) {
            foreach($value as $author) {
                if (is_numeric($author) || is_string($author)) {
                    $newAuthors[] = $add.$author;
                } else if ($author instanceof \WP_User) {
                    $newAuthors[] = $add.$author->ID;
                } else if ($author instanceof User) {
                    $newAuthors[] = $add.$author->id();
                }
            }
        } else if (is_numeric($value) || is_string($value)) {
            $newAuthors[] = $add.$value;
        } else if ($value instanceof \WP_User) {
            $newAuthors[] = $add.$value->ID;
        } else if ($value instanceof User) {
            $newAuthors[] = $add.$value->id();
        }

        $this->authors = array_merge($this->authors, $newAuthors);
        if (count($this->authors) > 0) {
            $this->authorName = null;
        }
    }

	/**
	 * Processes a parent where clause
	 *
	 * @param string $operator
	 * @param mixed|null $value
	 * @throws \Exception
	 */
	private function processParent($operator, $value) {
		if (in_array($operator, ['=', 'in'])) {
			$postOp = 'post_parent__in';
		} else {
			$postOp = 'post_parent__not_in';
		}

		if (empty($value)) {
			unset($this->args[$postOp]);
			return;
		}

		$ids = [];
		if (is_numeric($value) || is_string($value)) {
			$ids = [$value];
		} else if (is_array($value)) {
			foreach($value as $post) {
				if (is_numeric($post) || is_string($post)) {
					$ids[] = $post;
				} else if ($post instanceof \WP_Post) {
					$ids[] = $post->ID;
				} else if ($post instanceof Post) {
					$ids[] = $post->id;
				}
			}
		}

		if (empty($value)) {
			unset($this->args[$postOp]);
		} else {
			$this->args[$postOp] = $ids;
		}
	}

    /**
     * Processes where clause for post IDs
     *
     * @param $operator
     * @param $value
     * @throws \Exception
     */
    private function processIds($operator, $value) {
        if (in_array($operator, ['=', 'in'])) {
            $postOp = 'post__in';
        } else {
            $postOp = 'post__not_in';
        }

        if (empty($value)) {
            unset($this->args[$postOp]);
            return;
        }

        $ids = [];
        if (is_numeric($value) || is_string($value)) {
            $ids = [$value];
        } else if (is_array($value)) {
            foreach($value as $post) {
                if (is_numeric($post) || is_string($post)) {
                    $ids[] = $post;
                } else if ($post instanceof \WP_Post) {
                    $ids[] = $post->ID;
                } else if ($post instanceof Post) {
                    $ids[] = $post->id;
                }
            }
        }

        if (empty($value)) {
            unset($this->args[$postOp]);
        } else {
            $this->args[$postOp] = $ids;
        }
    }

    /**
     * Processes a category where clause
     * @param $operator
     * @param $value
     * @throws \Exception
     */
    private function processCategory($operator, $value) {
        $IDs = $this->collectTermTaxonomyIDs($value);
        $op = null;
        if (in_array($operator, ['=', 'in'])) {
            $op = 'category__in';
        } else if (in_array($operator, ['!=', 'not in'])) {
            $op = 'category__not_in';
        } else if ($operator == 'all') {
            $op = 'category__and';
        }

        if (empty($op)) {
            throw new \Exception("Invalid operator '$operator'.  Valid operators: ".implode(", ", static::$fieldOperators['category']));
        }

        if (empty($IDs)) {
            unset($this->args[$op]);
        } else {
            $this->args[$op] = $IDs;
        }
    }

    /**
     * Processes a tags where clause
     * @param $operator
     * @param $value
     * @throws \Exception
     */
    private function processTags($operator, $value) {
        $IDs = $this->collectTermTaxonomyIDs($value);
        $op = null;
        if (in_array($operator, ['=', 'in'])) {
            $op = 'tag__in';
        } else if (in_array($operator, ['!=', 'not in'])) {
            $op = 'tag__not_in';
        } else if ($operator == 'all') {
            $op = 'tag__and';
        }

        if (empty($op)) {
            throw new \Exception("Invalid operator '$operator'.  Valid operators: ".implode(", ", static::$fieldOperators['tag']));
        }

        if (empty($IDs)) {
            unset($this->args[$op]);
        } else {
            $this->args[$op] = $IDs;
        }
    }

    /**
     * Gathers term taxonomy IDs from a value
     * @param int|string|\WP_Term|\WP_Term[]|string[]|int[] $value
     * @return array
     */
    private function collectTermTaxonomyIDs($value) {
        if (empty($value)) {
            return [];
        }

        if (is_numeric($value) || is_string($value)) {
            return [$value];
        } else if (is_array($value)) {
            $terms = [];
            foreach($value as $term) {
                if (is_numeric($term) || is_string($term)) {
                    $terms[] = $term;
                } else if ($term instanceof \WP_Term) {
                    $terms[] = $term->term_taxonomy_id;
                }
            }

            return $terms;
        } else if ($value instanceof \WP_Term) {
            return [$value->term_taxonomy_id];
        }

        return [];
    }

    /**
     * Sets the value on the argument array, unsetting if the value is null.
     * @param $field
     * @param $value
     */
    private function setArgument($field, $value) {
        if ($value === null) {
            unset($this->args[$field]);
        } else {
            $this->args[$field] = $value;
        }
    }

    //endregion

    //region Execute Query

    /**
     * Returns the first post
     * @return Post|null
     */
    public function first() {
        $result = $this->limit(1)->order('post_date', 'ASC', true)->get();
        if (count($result) == 0) {
            return null;
        }

        return $result[0];
    }

    /**
     * Returns the last post
     * @return Post|null
     */
    public function last() {
        $result = $this->limit(1)->order('post_date', 'DESC', true)->get();
        if (empty($result)) {
            return null;
        }

        return $result[0];
    }

	/**
	 * Executes the query and returns the result
	 *
	 * @return PostCollection
	 */
	public function get() {
		add_filter('posts_where', [$this, 'filterPostsWhere'], 10, 2);
		$collection = new PostCollection($this->context, $this);
		remove_filter('posts_where', [$this, 'filterPostsWhere']);

		return $collection;
	}

	public function filterPostsWhere($where, $wp_query) {
		global $wpdb;

		if ($search_term = $wp_query->get( 'post_title_like' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
		} else if ($search_term = $wp_query->get( 'post_title_not_like' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title NOT LIKE \'%' . $wpdb->esc_like( $search_term ) . '%\'';
		}

		return $where;
	}

    /**
     * Builds the arguments that will be used with WP_Query
     * @return array
     */
    public function build() {
        $args = $this->args;

        if (!empty($authors)) {
            $args['author'] = implode(',', $authors);
        } else if (!empty($authorName)) {
            $args['author_name'] = $authorName;
        }

        $meta = $this->buildMetaQuery();
        if (!empty($meta)) {
            $args['meta_query'] = $meta;
        }

        if (!empty($this->taxQueries)) {
            $args['tax_query'] = $this->taxQueries;
        }

        return $args;
    }

    /**
     * Builds the meta queries
     * @return array
     */
    protected function buildMetaQuery() {
        $allMetas = $this->metaQueries;

        if ($this->metaqueryRelation !== null) {
            $allMetas['relation'] = $this->metaqueryRelation;
        }

        foreach($this->metaSubQueries as $subquery) {
            $allMetas[] = $subquery->buildMetaQuery();
        }

        return $allMetas;
    }

    //endregion
}