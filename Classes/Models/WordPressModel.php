<?php

namespace ILab\Stem\Models;

/**
 * Class WordPressModel.
 *
 * Base class for WordPress models.
 */
class WordPressModel implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return [];
    }
}
