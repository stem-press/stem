<?php

namespace Stem\Models;
use Stem\Core\Context;

/**
 * Class Attachment.
 *
 * Represents a media attachment
 *
 * @property-read string|null $filename
 * @property-read string|null $localFilePath
 * @property-read string|null $url
 * @property-read string|null $link
 * @property string|null $alt
 * @property string|null $description
 * @property string|null $caption
 * @property-read string|null $type
 * @property-read string|null $subType
 * @property string|null $mime
 * @property-read string|null $icon
 * @property-read array|null $sizeUrls
 * @property-read array|null $cleanedSizeUrls
 * @property-read int $width
 * @property-read int $height
 * @property-read string|null $orientation
 *
 */
class Attachment extends Post {
    protected static $postType = 'attachment';

	/** @var array Properties */
	protected $attachmentProperties = [
		'description' => 'post_content',
		'caption' => 'post_excerpt'
	];

	/** @var null|string The attachment's filename */
	protected $_filename = null;

	/** @var null|string The attachment's local file path */
	protected $_localFilePath = null;

    /** @var null|string The attachment's url */
    protected $_url = null;

    /** @var null|string The link to the attachment's page */
    protected $_link = null;

    /** @var null|string The attachment's alt text */
    protected $_alt = null;

    /** @var null|string The attachment's description */
    protected $_description = null;

    /** @var null|string The attachment's caption */
    protected $_caption = null;

    /** @var null|string The attachment's mime type */
    protected $_mime = null;

    /** @var null|string The attachment's type */
    protected $_type = null;

    /** @var null|string The attachment's subtype */
    protected $_subtype = null;

    /** @var null|string The URL for the icon representing the attachment's mime type */
    protected $_icon = null;

    /** @var null|array Extra information about the attachment */
    protected $_attachmentInfo = null;

    /** @var null|array Attachment metadata */
    protected $_attachmentMeta = null;

	/** @var null|array URLs for all of the image sizes  */
	protected $_sizeUrls = null;

	/** @var null|array URLs for all of the image sizes  */
	protected $_cleanedSizeUrls = null;

	/**
	 * Attachment constructor.
	 *
	 * @param Context $context
	 * @param \WP_Post|null $post
	 */
    public function __construct(Context $context, \WP_Post $post = null) {
        parent::__construct($context, $post);

        $this->postProperties = array_merge($this->postProperties, $this->attachmentProperties);
        $this->parseType();
    }

    //region Properties

	public function __get($name) {
    	if ($name == 'width') {
    		return $this->attachmentMeta('width', (int)0);
	    } else if ($name == 'height') {
		    return $this->attachmentMeta('height', (int)0);
	    } else if ($name == 'orientation') {
		    $w = $this->attachmentMeta('width', (int)0);
		    $h = $this->attachmentMeta('height', (int)0);

		    if ($w > $h) {
		    	return 'landscape';
		    } else if ($w < $h) {
		    	return 'portrait';
		    } else if ($w == $h) {
		    	return 'square';
		    }
	    }

		return parent::__get($name);
	}

	/**
     * The filename of the attachment
     * @return null|string
     */
    protected function getFilename() {
        if ($this->_filename != null) {
            return $this->_filename;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_filename = wp_basename(get_attached_file($this->id));
        return $this->_filename;
    }

	/**
	 * The filename of the attachment
	 * @return null|string
	 */
	protected function getLocalFilePath() {
		if ($this->_localFilePath != null) {
			return $this->_localFilePath;
		}

		if (empty($this->id)) {
			return null;
		}

		$this->_localFilePath = get_attached_file($this->id);
		return $this->_localFilePath;
	}

    /**
     * The URL for the attachment's original image
     * @return null|string
     */
    protected function getUrl() {
        if ($this->_url != null) {
            return $this->_url;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_url = wp_get_attachment_url($this->id);
        return $this->_url;
    }


	/**
	 * The URLs for the attachment's sizes
	 * @return null|array
	 */
    protected function getSizeUrls() {
    	if ($this->_sizeUrls != null) {
    		return $this->_sizeUrls;
	    }

	    global $_wp_additional_image_sizes;

	    $sizes = [];
	    $get_intermediate_image_sizes = get_intermediate_image_sizes();

	    // Create the full array with sizes and crop info
	    foreach($get_intermediate_image_sizes as $_size) {
	    	$sizes[$_size] = null;
	    }

	    foreach($_wp_additional_image_sizes as $_size => $sizeData) {
	    	if (!isset($sizes[$_size])) {
	    		$sizes[$_size] = null;
		    }
	    }

	    foreach($sizes as $key => $useless) {
	    	$sizes[$key] = wp_get_attachment_image_src($this->id, $key)[0];
	    }

	    $this->_sizeUrls = $sizes;
	    return $sizes;
    }


	/**
	 * The URLs for the attachment's sizes
	 * @return null|array
	 */
	protected function getCleanedSizeUrls() {
		if ($this->_cleanedSizeUrls != null) {
			return $this->_cleanedSizeUrls;
		}

		$sizedUrls = $this->getSizeUrls();
		$this->_cleanedSizeUrls = [];
		foreach($sizedUrls as $size => $url) {
			$this->_cleanedSizeUrls[str_replace('-','_', $size)] = $url;
		}

		return $this->_cleanedSizeUrls;
	}

    /**
     * Link to the attachment's page
     * @return null|string
     */
    protected function getLink() {
        if ($this->_link != null) {
            return $this->_link;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_link = get_attachment_link($this->id);
        return $this->_link;
    }

    /**
     * Attachment's alt text
     * @return null|string
     */
	protected function getAlt() {
        if ($this->_alt != null) {
            return $this->_alt;
        }

        $this->_alt = $this->meta('_wp_attachment_image_alt', null);
        return $this->_alt;
    }

    /**
     * Sets the alt text for the attachment
     * @param string $alt
     */
	protected function setAlt($alt) {
        $this->_alt = $alt;
        $this->updateMeta('_wp_attachment_image_alt', $alt);
    }

    /**
     * The attachment's primary type
     * @return null|string
     */
	protected function getType() {
        return $this->_type;
    }

    /**
     * The attachment's sub type
     * @return null|string
     */
	protected function getSubType() {
        return $this->_subtype;
    }

    /**
     * The attachment's mime type
     * @return null|string
     */
	protected function getMime() {
        if ($this->_mime != null) {
            return $this->_mime;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_mime = $this->post->post_mime_type;
        return $this->_mime;
    }

    /**
     * Sets the attachment's mime type
     * @param string $mime
     */
	protected function setMime($mime) {
        $this->_icon = null;
        $this->_mime = $mime;
        $this->changes->addChange('post_mime_type', $mime);

        $this->parseType();
    }

    /**
     * URL for the icon representing the attachment's mime type
     * @return null|string
     */
	protected function getIcon() {
        if ($this->_icon != null) {
            return $this->_icon;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_icon = wp_mime_type_icon($this->_mime ?: $this->id);
        return $this->_icon;
    }

    /**
     * Parses the type/subtype from the mime type
     */
    private function parseType() {
        $this->_type = null;
        $this->_subtype = null;

        $mime = $this->mime;
        if (empty($mime)) {
            return;
        }

        if (false !== strpos($mime, '/' )) {
            list($this->_type, $this->_subtype) = explode('/', $mime);
        } else {
            list($this->_type, $this->_subtype) = [$mime, ''];
        }
    }

    //endregion

    //region Image Tags

    /**
     * Returns an img tag using the requested size template.
     *
     * @param string $size The size template to use, specify 'original' for original size.
     * @param bool $attr Any additional attributes to add to the tag
     * @param bool $stripDimensions Strip dimensions from the tag
     *
     * @return string|null
     */
    public function img($size = 'original', $attr = false, $stripDimensions = false) {
        if (empty($this->id)) {
            return null;
        }

        if (! $attr) {
            $img = wp_get_attachment_image($this->id, $size);
        } else {
            $img = wp_get_attachment_image($this->id, $size, false, $attr);
        }

        if ($stripDimensions) {
            $img = preg_replace('#width="[0-9]+"\s*#', '', $img);
            $img = preg_replace('#height="[0-9]+"\s*#', '', $img);
        }

        return $img;
    }

    /**
     * Generates an amp-img tag.
     * @param string $size
     * @param bool $responsive
     * @param array|null $attr Any additional attributes to add to the tag
     * @return string
     */
    public function ampImg($size = 'thumbnail', $responsive = true, $attr = null) {
        if (empty($this->id)) {
            return null;
        }

        if (empty($attr)) {
            $attr = [];
        }

        if ($responsive) {
            $attr['layout'] = 'responsive';
        }

        $img = wp_get_attachment_image($this->id, $size, false, $attr);

        $img = str_replace('<img', '<amp-img', $img);

        return $img;
    }

	/**
	 * Returns the url for an image using the requested size template.
	 *
	 * @param string $size The size template to use.
	 *
	 * @return string|null
	 */
	public function src($size = 'original') {
		if (empty($this->id)) {
			return null;
		}

		$result = wp_get_attachment_image_src($this->id, $size);
		if ($result && is_array($result) && (count($result) > 0)) {
			return $result[0];
		}

		return null;
	}

	/**
	 * Returns a <source> tag
	 *
	 * @param string $size The size template to use.
	 * @param array $mediaQuery The media query to match this source
	 *
	 * @return string|null
	 */
	public function source($size = 'original', $mediaQuery = []) {
		if (empty($this->id)) {
			return null;
		}

		$result = wp_get_attachment_image_src($this->id, $size);
		if ($result && is_array($result) && (count($result) > 0)) {
			$src = $result[0];

			$queryEle = [];
			foreach($mediaQuery as $query => $querySize) {
				$queryEle[] = "$query:  $querySize";
			}

			$query = implode(' and ', $queryEle);

			return "<source srcset='{$src}' media='({$query})'>";
		}

		return null;
	}

    //endregion

    //region Attachment Metadata

    /**
     * Returns the post's attachment metadata
     *
     * @param null|string $keyPath The key path into the attachment metadata
     * @param null|mixed $defaultValue The default value to return if the key doesn't exist
     * @return array|mixed|null
     */
    public function attachmentMeta($keyPath=null, $defaultValue=null) {
        $this->insureAttachmentMeta();

        if (empty($keyPath)) {
            return $this->_attachmentMeta;
        } else {
            return arrayPath($this->_attachmentMeta, $keyPath, $defaultValue);
        }
    }

    /**
     * Updates metadata value
     * @param $keyPath
     * @param $value
     */
    public function updateAttachmentMeta($keyPath, $value) {
        $this->insureAttachmentMeta();

        updateArrayPath($this->_attachmentMeta, $keyPath, $value);

        $this->changes->updateAttachmentMeta($this->_attachmentMeta);
    }

    /**
     * Deletes a metadata item
     * @param $keyPath
     */
    public function deleteAttachmentMeta($keyPath) {
        $this->insureAttachmentMeta();

        unsetArrayPath($this->_attachmentMeta, $keyPath);

        $this->changes->updateAttachmentMeta($this->_attachmentMeta);
    }

    /**
     * Insures metadata is loaded for this post
     */
    private function insureAttachmentMeta() {
        if ($this->_attachmentMeta == null) {
            if (empty($this->id)) {
                $this->_attachmentMeta = [];
            } else {
                $this->_attachmentMeta = wp_get_attachment_metadata($this->id);
            }
        }
    }

    //endregion

    //region Attachment Info

    /**
     * Loads the attachment's extra info
     * @param bool $forceReload
     * @return array|null
     */
    public function attachmentInfo($forceReload = false) {
        if (!$forceReload && ($this->_attachmentInfo != null)) {
            return $this->_attachmentInfo;
        }

        if (empty($this->id)) {
            return null;
        }

        $this->_attachmentInfo = wp_prepare_attachment_for_js($this->id);
        return $this->_attachmentInfo;
    }

    //endregion

	/**
	 * @return array
	 */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();

        unset($json['author']);
        unset($json['content']);
        unset($json['excerpt']);
        unset($json['categories']);
        unset($json['thumbnail']);

        $json['caption'] = $this->caption;
        $json['description'] = $this->description;

        if ($this->attachmentInfo()) {
            $json['sizes'] = $this->_attachmentInfo['sizes'];
            $json['width'] = $this->_attachmentInfo['width'];
            $json['height'] = $this->_attachmentInfo['height'];
            $json['orientation'] = $this->_attachmentInfo['orientation'];
        }

        return $json;
    }
}
