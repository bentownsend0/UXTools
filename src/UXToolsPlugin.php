<?php

namespace UXTools;

use DOMDocument;

/**
 * Class UXToolsPlugin
 * @package UXTools
 */
class UXToolsPlugin
{
    const PLUGIN_ID = 'ux-tools';

    /**
     * Define all your actions and WP hooks
     */
    public function run()
    {
        add_filter('manage_posts_columns', array($this, 'AddAdminColumns'));
        add_action('manage_posts_custom_column', array($this, 'SetAdminColumns'), 10, 2);
    }

    function AddAdminColumns($columns)
    {
        if(current_user_can('administrator')) {
            $columns = array_merge($columns, array(
                'postFormat' => 'Format',
                'pageCount' => 'No of Pages',
                'wordCount' => 'Word Count',
                'contentBreakdown' => 'Content Breakdown'
            ));
        }

        return $columns;
    }

    function SetAdminColumns($column, $post_id)
    {
        if(current_user_can('administrator')) {
            switch ($column) {
                case 'postFormat' :
                    echo $this->GetPostFormat($post_id);
                    break;
                case 'pageCount' :
                    echo $this->GetPageCount();
                    break;
                case 'wordCount' :
                    echo $this->GetWordCount($post_id);
                    break;
                case 'contentBreakdown' :
                    echo $this->GetContentBreakdown($post_id);
                    break;
            }
        }
    }

    function GetWordCount($post_id)
    {
        $post = get_post($post_id);
        $charList = '';
        $wordCount = str_word_count(strip_tags($post->post_content), 0, $char_list);

        return $wordCount;
    }

    function GetContentBreakdown($post_id)
    {
        $post = get_post($post_id);
        $HTML = @DOMDocument::loadHTML($post->post_content);

        if (!$HTML) {
            return;
        }

        $paragraphs = $HTML->getElementsByTagName('p');
        $breakdown = 'Paragraphs: ';
        $breakdown .= $paragraphs->length;

        $orderedLists = $HTML->getElementsByTagName('ol');
        $orderedListsLength = $orderedLists->length;
        if ($orderedListsLength) {
            $breakdown .= '<br /> Ordered Lists: ';
            $breakdown .= $orderedListsLength;
        }

        $unorderedLists = $HTML->getElementsByTagName('ul');
        $unorderedListsLength = $unorderedLists->length;
        if ($unorderedListsLength) {
            $breakdown .= '<br /> Unordered Lists: ';
            $breakdown .= $unorderedListsLength;
        }

        // todo: think of a better solution for counting different image types
        $images = substr_count($post->post_content, 'img') + substr_count($post->post_content, '[image');
        if ($images > 0) {
            $breakdown .= '<br /> Images: ';
            $breakdown .= $images;
        }

        $shortcodes = substr_count($post->post_content, '[/') + substr_count($post->post_content, '/]') - $images;
        if ($images > 0) {
            $breakdown .= '<br /> Images: ';
            $breakdown .= $images;
        }

        $allElements = $HTML->getElementsByTagName('*');
        $breakdown .= '<br /> Total Elements: ';
        $breakdown .= $allElements->length;

        return $breakdown;
    }

    function GetPostFormat($post_id)
    {
        $format = get_post_format($post_id) ? get_post_format($post_id) : 'Standard';
        return $format;
    }

    function GetPageCount()
    {
        global $numpages;
        $pageCount = $numpages;
        return $pageCount;
    }
}
