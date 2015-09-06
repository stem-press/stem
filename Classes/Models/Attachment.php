<?php
namespace ILab\Stem\Models;

use ILab\Stem\Core\Context;

class Attachment extends Post {
    private $parentPost=null;
    private $attachmentInfo=null;

    public function img($size='thumbnail',$attr=false, $stripDimensions=false) {
        if (!$attr)
            $img=wp_get_attachment_image($this->post->ID,$size);
        else
            $img=wp_get_attachment_image($this->post->ID,$size,false,$attr);

        if ($stripDimensions){
            $img=preg_replace('#width="[0-9]+"\s*#','',$img);
            $img=preg_replace('#height="[0-9]+"\s*#','',$img);
        }

        return $img;
    }

    public function src($size='thumbnail') {
        $result=wp_get_attachment_image_src($this->post->ID,$size);
        return $result[0];
    }

    public function attachmentUrl() {
        return wp_get_attachment_url($this->id);
    }

    public function parentPost() {
        if ($this->parentPost)
            return $this->parentPost;

        $parent_id=get_post_field('post_parent',$this->id);
        if ($parent_id && !empty($parent_id))
            $this->parentPost=$this->context->modelForPost(\WP_Post::get_instance($parent_id));

        return $this->parentPost;
    }

    protected function loadAttachmentInfo() {
        if (!$this->attachmentInfo)
            $this->attachmentInfo=wp_prepare_attachment_for_js($this->post);
    }

    public function caption(){
        $this->loadAttachmentInfo();

        return $this->attachmentInfo['caption'];
    }

    public function description(){
        $this->loadAttachmentInfo();

        return $this->attachmentInfo['description'];
    }
}