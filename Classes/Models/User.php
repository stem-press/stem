<?php

namespace Stem\Models;

use Stem\Core\Context;

/**
 * Class User.
 *
 * Represents a WordPress User
 */
class User implements \JsonSerializable
{
    /**
     * The current context.
     *
     * @var Context
     */
    public $context;

    /**
     * Underlying user.
     *
     * @var \WP_User
     */
    protected $user;

    /** @var number|null User's id */
    protected $id;

    /**
     * First name.
     * @var null|string
     */
    private $firstName = null;

    /**
     * Last name.
     * @var null|string
     */
    private $lastName = null;

    /**
     * Bio or description.
     * @var null|string
     */
    private $bio = null;

    /**
     * Website URL.
     * @var null|string
     */
    private $website = null;

    /**
     * User's display name.
     * @var null|string
     */
    private $displayName = null;

    /**
     * User's email address.
     * @var null|string
     */
    private $email = null;

    /**
     * User's permalink.
     * @var null|string
     */
    private $permalink = null;

    public function __construct(Context $context, \WP_User $user)
    {
        $this->id = $user->ID;

        $this->context = $context;
        $this->user = $user;

        $this->firstName = $user->first_name;
        $this->lastName = $user->last_name;
        $this->bio = $user->description;
        $this->website = $user->user_url;
        $this->displayName = $user->user_nicename;
        $this->email = $user->user_email;

        $this->permalink = get_author_posts_url($user->ID, $this->displayName);
    }

    public function id() {
        return $this->id;
    }

    /**
     * The underlying \WP_User object.
     * @return \WP_User
     */
    public function user()
    {
        return $this->user;
    }

    /**
     * User's permalink.
     * @return null|string
     */
    public function permalink()
    {
        return $this->permalink;
    }

    /**
     * The user's gravatar image tag.
     * @param int $size
     * @param string $default
     * @param string $alt
     * @param null $args
     *
     * @return false|string
     */
    public function avatar($size = 96, $default = '', $alt = '', $args = null)
    {
        return get_avatar($this->user->ID, $size, $default, $alt, $args);
    }

    /**
     * User's display name.
     * @return null|string
     */
    public function displayName()
    {
        return $this->displayName;
    }

    /**
     * First name.
     * @return null|string
     */
    public function firstName()
    {
        return $this->firstName;
    }

    /**
     * Last name.
     * @return null|string
     */
    public function lastName()
    {
        return $this->lastName;
    }

    /**
     * User's email address.
     * @return null|string
     */
    public function email()
    {
        return $this->email;
    }

    /**
     * Website URL.
     * @return null|string
     */
    public function website()
    {
        return $this->website;
    }

    /**
     * Bio or description.
     * @return null|string
     */
    public function bio()
    {
        return $this->bio;
    }

    public function jsonSerialize()
    {
        return [
            'firstName' => $this->firstName(),
            'lastName' => $this->lastName(),
            'bio' => $this->bio(),
            'website' => $this->website(),
            'displayName' => $this->displayName(),
            'email' => $this->email(),
            'permalink' => $this->permalink(),
        ];
    }
}
