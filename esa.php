<?php

require __DIR__ . "/vendor/autoload.php";

use Carbon\Carbon;

/**
 * Class esa
 * https://docs.esa.io/posts/102
 */
class esa
{
    private $access_token;
    private $current_team;
    private $api;
    private $query;

    /**
     * esa constructor.
     * @param string $access_token
     * @param string $current_team
     */
    public function __construct(string $access_token, string $current_team)
    {
        $this->access_token = $access_token;
        $this->current_team = $current_team;

        $this->api = \Polidog\Esa\Api::factory($this->access_token, $this->current_team);
    }

    /**
     * template_post_id を指定して新しく投稿する
     *
     * @param int $template_post_id
     * @return array
     */
    function post(int $template_post_id): array
    {
        $query = [
            'template_post_id' => $template_post_id,
            'user' => 'esa_bot',
        ];

        $post = $this->api->createPost($query);

        return $post;
    }

    /**
     * 指定されたesaの記事で、特定の箇所を置換する
     *
     * {date}　→　実行日
     * {complete_task}　→　完了した課題
     * {task}　→　優先度 高 の課題
     *
     * @param array $post
     * @param array $markdown_links
     */
    function make_post_query(array $post, array $markdown_links)
    {
        $dt = new Carbon('now', 'Asia/Tokyo');

        $body_md = preg_replace('{{date}}', $dt->toDateString(), $post['body_md']);
        $body_md = preg_replace('{{complete_issues}}', implode($markdown_links['complete_issues'], "\n"), $body_md);
        $body_md = preg_replace('{{issues}}', implode($markdown_links['issues'], "\n"), $body_md);
        $this->query['wip'] = 'false';
        $this->query['body_md'] = $body_md;
    }

    /**
     * make_post_query()で置換した内容で指定した記事を置き換える
     *
     * @param int $post_number 更新対象の記事
     */
    function update(int $post_number)
    {
        $this->api->updatePost($post_number, $this->query);
    }
}
