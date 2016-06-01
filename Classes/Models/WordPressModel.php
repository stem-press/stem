<?php

namespace ILab\Stem\Models;

/**
 * Class WordPressModel
 *
 * Base class for WordPress models.
 *
 * @package ILab\Stem\Models
 */
class WordPressModel implements \JsonSerializable {
    public function jsonSerialize() {
        return [];
    }
}