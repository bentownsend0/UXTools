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

        add_filter('manage_pages_columns', array($this, 'AddAdminColumns'));
        add_action('manage_pages_custom_column', array($this, 'SetAdminColumns'), 10, 2);
    }


    /**
     * @param $columns
     * @return array
     *
     * Adds admin columns if the user has admin privileges
     */
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

    /**
     * @param $column
     * @param $post_id
     *
     * Sets up columns that have been added to the admin if the user had admin privileges
     */
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

    /**
     * @param $post_id
     * @return mixed
     *
     * Gets the word count for the current item listed
     */
    function GetWordCount($post_id)
    {
        $post = get_post($post_id);
        $charList = '';
        $wordCount = str_word_count(strip_tags($post->post_content), 0, $charList);

        return $wordCount;
    }

    /**
     * @param $post_id
     * @return string|void
     *
     * Gets a breakdown of the HTML items within the content for the current item listed
     */
    function GetContentBreakdown($post_id)
    {
        $post = get_post($post_id);
        $postContent = $post->post_content ? $post->post_content : '';

        if (!$postContent) {
            return;
        }

        $HTML = @DOMDocument::loadHTML($postContent);

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

        $allElements = $HTML->getElementsByTagName('*');
        $breakdown .= '<br /> Total Elements: ';
        $breakdown .= $allElements->length;

        return $breakdown;
    }

    /**
     * @param $post_id
     * @return false|string
     *
     * Gets the post format for the current item listed
     */
    function GetPostFormat($post_id)
    {
        $format = get_post_format($post_id) ? get_post_format($post_id) : 'Standard';
        return $format;
    }

    /**
     * @return mixed
     *
     * Gets the number of pages that makes up the article
     */
    function GetPageCount()
    {
        global $numpages;
        $pageCount = $numpages;
        return $pageCount;
    }
}
