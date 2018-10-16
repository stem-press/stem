<?php

namespace ILab\Stem\UI;

use ILab\Stem\Core\Context;
use ILab\Stem\Core\UI;

/**
 * Block class for user defined blocks
 *
 * @package ILab\Stem\UI
 */
class Block {
    /** @var Context|null  */
    protected $context = null;

    /** @var UI|null  */
    protected $ui = null;

    /** @var null|string  */
    protected $template = null;

    /** @var string  */
    protected $name = null;

    /** @var string  */
    protected $title= null;

    /** @var string */
    protected $description = null;

    /** @var string  */
    protected $category= null;

    /** @var string */
    protected $icon= null;

    /** @var array */
    protected $keywords= [];

    public function __construct(Context $context, UI $ui, $data = null) {
        $this->context = $context;
        $this->ui = $ui;

        if (get_class($this) == 'ILab\\Stem\\UI\\Block') {
            $requiredKeys = ['title', 'description', 'category', 'icon', 'template'];
            if (!keysExist($data, $requiredKeys)) {
                throw new \Exception('Block definition is missing one or more of these required keys: '.implode(', ', $requiredKeys));
            }
        }

        $this->configureBlock();

        $this->title = arrayPath($data, 'title', $this->title);
        $this->name = arrayPath($data, 'name', $this->name);
        if (empty($this->name) && !empty($this->title)) {
            $this->name = sanitize_title($this->title);
        }

        $this->category = arrayPath($data, 'category', $this->category);
        $this->description = arrayPath($data, 'description', $this->description);
        $this->icon = arrayPath($data, 'icon', $this->icon);
        $this->template = arrayPath($data, 'template', $this->template);
        $this->keywords = arrayPath($data,'keywords', (!empty($this->category)) ? [$this->category] : $this->keywords);

        if (empty($this->template)) {
            $this->template = 'blocks/'.strtolower(class_basename($this));
        }
    }

    /**
     * Allow subclasses to configure the block before any user supplied data is applied.
     */
    protected function configureBlock() {
    }

    /**
     * Description of the block
     * @return string
     * @throws \Exception
     */
    public function description() {
        if (empty($this->description)) {
            throw new \Exception('Block description cannot be empty.');
        }

        return $this->description;
    }

    /**
     * The icon for the block
     * @return string
     * @throws \Exception
     */
    public function icon() {
        if (empty($this->icon)) {
            throw new \Exception('Block icon cannot be empty.');
        }

        return $this->icon;
    }

    /**
     * Keywords for the block
     * @return array
     */
    public function keywords() {
        return $this->keywords;
    }

    /**
     * Title for the block
     * @return string
     * @throws \Exception
     */
    public function title() {
        if (empty($this->title)) {
            throw new \Exception('Block name cannot be empty.');
        }

        return $this->title;
    }

    /**
     * Name/slug for the block
     * @return string
     * @throws \Exception
     */
    public function name() {
        if (empty($this->name)) {
            if (empty($this->title)) {
                return sanitize_title($this->title());
            }

            throw new \Exception('Block name cannot be empty.');
        }

        return sanitize_title($this->title());
    }

    /**
     * Name of the category that the block belongs to
     * @return string
     * @throws \Exception
     */
    public function category() {
        if (empty($this->title)) {
            throw new \Exception('Block name cannot be empty.');
        }

        return $this->category;
    }

    /**
     * Slug for the category
     * @return string
     * @throws \Exception
     */
    public function categorySlug() {
        return sanitize_title($this->category());
    }

    /**
     * Renders the block
     * @param array $data
     * @return string
     */
    public function render($data) {
        return $this->ui->render($this->template, $data);
    }
}