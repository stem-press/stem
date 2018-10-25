<?php
/**
 * Created by PhpStorm.
 * User: jong
 * Date: 8/18/15
 * Time: 3:48 PM.
 */

namespace Stem\Utilities;

final class Text
{
    public static function closeTags($html)
    {
        //put all opened tags into an array
        preg_match_all('#<([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);
        $openedtags = $result[1];
        //put all closed tags into an array
        preg_match_all('#</([a-z]+)>#iU', $html, $result);
        $closedtags = $result[1];
        $len_opened = count($openedtags);
        // all tags are closed
        if (count($closedtags) == $len_opened) {
            return $html;
        }
        $openedtags = array_reverse($openedtags);
        // close tags
        for ($i = 0; $i < $len_opened; $i++) {
            if (! in_array($openedtags[$i], $closedtags)) {
                $html .= '</'.$openedtags[$i].'>';
            } else {
                unset($closedtags[array_search($openedtags[$i], $closedtags)]);
            }
        }
        $html = str_replace(['</br>', '</hr>', '</wbr>'], '', $html);
        $html = str_replace(['<br>', '<hr>', '<wbr>'], ['<br />', '<hr />', '<wbr />'], $html);

        return $html;
    }

    public static function trim($text, $num_words = 55, $more = null, $allowed_tags = 'p a span b i br')
    {
        if (null === $more) {
            $more = __('&hellip;');
        }
        $original_text = $text;
        $allowed_tag_string = '';
        foreach (explode(' ', $allowed_tags) as $tag) {
            $allowed_tag_string .= '<'.$tag.'>';
        }

        if ($allowed_tags != null) {
            $text = strip_tags($text, $allowed_tag_string);
        }

        /* translators: If your word count is based on single characters (East Asian characters),
        enter 'characters'. Otherwise, enter 'words'. Do not translate into your own language. */
        if ('characters' == _x('words', 'word count: words or characters?') && preg_match('/^utf\-?8$/i', get_option('blog_charset'))) {
            $text = trim(preg_replace("/[\n\r\t ]+/", ' ', $text), ' ');
            preg_match_all('/./u', $text, $words_array);
            $words_array = array_slice($words_array[0], 0, $num_words + 1);
            $sep = '';
        } else {
            $words_array = preg_split("/[\n\r\t ]+/", $text, $num_words + 1, PREG_SPLIT_NO_EMPTY);
            $sep = ' ';
        }
        if (count($words_array) > $num_words) {
            array_pop($words_array);
            $text = implode($sep, $words_array);
            $text = $text.$more;
        } else {
            $text = implode($sep, $words_array);
        }
        $text = self::closeTags($text);

        return apply_filters('wp_trim_words', $text, $num_words, $more, $original_text);
    }
}
