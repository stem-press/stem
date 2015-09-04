<?php
namespace ILab\Stem\Models;


class Attachment extends Post {

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
}